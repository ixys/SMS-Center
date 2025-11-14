<?php

namespace App\Console\Commands;

use App\Models\SimCard;
use App\Models\SmsConversation;
use App\Models\SmsMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Commentaire FR :
 * Daemon UDP pour communiquer directement avec un GoIP4.
 * - Ecoute les SMS entrants (RECEIVE: ...)
 * - Envoie les SMS présents dans goip_outgoing_queue
 *
 * A lancer par exemple avec :
 * php artisan goip:worker
 */
class GoipWorkerCommand extends Command
{
    protected $signature = 'goip:worker';

    protected $description = 'GoIP UDP worker for sending and receiving SMS';

    // Adresse & port du GoIP
    protected string $goipHost;
    protected int $goipPort;

    public function __construct()
    {
        parent::__construct();

        $this->goipHost = config('services.goip.host', '192.168.2.100');
        $this->goipPort = (int) config('services.goip.port', 44444);
    }

    public function handle(): int
    {
        $this->info('Starting GoIP worker...');

        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

        if (! $socket) {
            $this->error('Unable to create UDP socket');
            return self::FAILURE;
        }

        // Bind sur toutes les interfaces, port configuré
        if (! socket_bind($socket, '0.0.0.0', $this->goipPort)) {
            $this->error('Unable to bind UDP socket on port ' . $this->goipPort);
            return self::FAILURE;
        }

        socket_set_nonblock($socket);

        while (true) {
            // 1) Traiter les paquets entrants
            $this->receiveIncomingPackets($socket);

            // 2) Traiter les SMS à envoyer
            $this->processOutgoingQueue($socket);

            // Petite pause pour éviter de bouffer 100% CPU
            usleep(100_000); // 100ms
        }

        // (théoriquement jamais atteint)
        socket_close($socket);
        return self::SUCCESS;
    }

    /**
     * Commentaire FR :
     * Lecture non-bloquante des paquets UDP provenant du GoIP.
     */
    protected function receiveIncomingPackets($socket): void
    {
        $from = '';
        $port = 0;

        $buf = '';
        $bytes = @socket_recvfrom($socket, $buf, 4096, 0, $from, $port);

        if ($bytes === false || $bytes === 0) {
            return;
        }

        // Exemple de trame typique (à adapter selon ton firmware) :
        // "RECEIVE:12345;id:goip1;password:xxx;srcnum:+336xxxx;msg:Hello;time:..."
        $this->info("Received from $from:$port => $buf");

        if (str_starts_with($buf, 'RECEIVE:')) {
            $this->handleIncomingSmsPacket($socket, $buf, $from, $port);
        } else {
            // Tu peux logger d'autres types de paquets ici (ACK, statut, etc.)
        }
    }

    /**
     * Commentaire FR :
     * Parse un paquet RECEIVE: et crée le SmsMessage inbound correspondant.
     */
    protected function handleIncomingSmsPacket($socket, string $packet, string $from, int $port): void
    {
        // On parse vite fait en key=value
        // (pour un truc sérieux tu ferais un parser dédié protocole GoIP)
        $parts = explode(';', $packet);

        $data = [];
        foreach ($parts as $part) {
            if (str_contains($part, ':')) {
                [$k, $v] = explode(':', $part, 2);
                $data[trim($k)] = trim($v);
            }
        }

        $receiveId = $data['RECEIVE'] ?? null;
        $goipId    = $data['id'] ?? null;
        $fromNum   = $data['srcnum'] ?? null;
        $msg       = $data['msg'] ?? null;

        if (! $receiveId || ! $goipId || ! $fromNum || $msg === null) {
            $this->error('Invalid RECEIVE packet: ' . $packet);
            return;
        }

        // ACK au GoIP pour dire "OK, bien reçu"
        $ack = "RECEIVE {$receiveId} OK";
        @socket_sendto($socket, $ack, strlen($ack), 0, $from, $port);

        // On mappe le goipId -> SimCard.sender_id
        $sim = SimCard::where('sender_id', $goipId)->first();

        // On cherche/ crée la conversation
        $conversation = SmsConversation::firstOrCreate(
            [
                'phone_number' => $fromNum,
                'sim_card_id'  => optional($sim)->id,
            ],
            [
                'last_message_preview' => $msg,
                'last_direction'       => 'inbound',
                'last_message_at'      => Carbon::now(),
            ],
        );

        // On crée le message inbound
        $message = SmsMessage::create([
            'sms_conversation_id' => $conversation->id,
            'sim_card_id'         => optional($sim)->id,
            'phone_number'        => $fromNum,
            'direction'           => 'inbound',
            'status'              => 'received',
            'body'                => $msg,
            'received_at'         => Carbon::now(),
        ]);

        // On met à jour la conversation
        $conversation->update([
            'last_message_preview' => $msg,
            'last_direction'       => 'inbound',
            'last_message_at'      => $message->created_at,
            'unread_inbound_count' => $conversation->unread_inbound_count + 1,
        ]);
    }

    /**
     * Commentaire FR :
     * Envoi des SMS en attente dans goip_outgoing_queue.
     */
    protected function processOutgoingQueue($socket): void
    {
        // On récupère quelques messages pending
        $rows = DB::table('goip_outgoing_queue')
            ->where('status', 'pending')
            ->limit(10)
            ->get();

        foreach ($rows as $row) {
            $this->sendSingleSms($socket, $row);
        }
    }

    protected function sendSingleSms($socket, $row): void
    {
        // On passe le statut en "sending"
        DB::table('goip_outgoing_queue')
            ->where('id', $row->id)
            ->update([
                'status'     => 'sending',
                'updated_at' => now(),
            ]);

        $message = SmsMessage::find($row->sms_message_id);
        $sim     = SimCard::find($row->sim_card_id);

        if (! $message || ! $sim) {
            DB::table('goip_outgoing_queue')
                ->where('id', $row->id)
                ->update([
                    'status'        => 'failed',
                    'error_message' => 'Missing message or SIM',
                    'updated_at'    => now(),
                ]);

            return;
        }

        // Construction d'une trame d'envoi basique (à adapter selon protocole GoIP)
        // Exemple très simplifié :
        // "SEND:1234;id:goip1;password:pass;dstnum:+336xxx;msg:Hello"
        $packetId = $row->id; // identifiant qu'on choisit
        $password = config('services.goip.password', 'admin');

        $packet = sprintf(
            'SEND:%s;id:%s;password:%s;dstnum:%s;msg:%s',
            $packetId,
            $sim->sender_id,
            $password,
            $row->phone_number,
            $row->body,
        );

        $this->info("Sending to GoIP: {$packet}");

        $ok = @socket_sendto($socket, $packet, strlen($packet), 0, $this->goipHost, $this->goipPort);

        if (! $ok) {
            DB::table('goip_outgoing_queue')
                ->where('id', $row->id)
                ->update([
                    'status'        => 'failed',
                    'error_message' => 'UDP send failed',
                    'updated_at'    => now(),
                ]);

            $message->update([
                'status'        => 'failed',
                'failed_at'     => now(),
                'error_message' => 'UDP send failed',
            ]);

            return;
        }

        // Ici tu peux, si tu veux, attendre un ACK du GoIP (SEND <id> OK)
        // Pour rester simple, on considère que c'est "sent" dès l'envoi UDP

        DB::table('goip_outgoing_queue')
            ->where('id', $row->id)
            ->update([
                'status'     => 'sent',
                'updated_at' => now(),
            ]);

        $message->update([
            'status'  => 'sent',
            'sent_at' => now(),
        ]);
    }
}
