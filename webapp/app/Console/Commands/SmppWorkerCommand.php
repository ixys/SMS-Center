<?php

namespace App\Console\Commands;

use App\Models\SimCard;
use App\Models\SmsConversation;
use App\Models\SmsMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Smpp\Client;
use Smpp\ClientBuilder;
use Smpp\Pdu\Address;

class SmppWorkerCommand extends Command
{
    protected $signature = 'smpp:worker';
    protected $description = 'SMPP worker for sending SMS via GoIP4';

    protected Client $smppClient;

    public function handle(): int
    {
        $this->info('Starting SMPP worker…');

        $this->connect();

        while (true) {
            // 1) Traiter les SMS à envoyer
            $this->processOutgoingQueue();

            // 2) Lire les SMS entrants (deliver_sm)
            $this->readIncomingMessages();

            usleep(100_000); // 100 ms
        }

        return self::SUCCESS;
    }

    protected function connect(): void
    {
        $host     = config('smpp.host');
        $port     = (int) config('smpp.port');
        $systemId = config('smpp.system_id');
        $password = config('smpp.password');

        $this->info("Connecting to SMPP {$host}:{$port}…");

        // On suit l’exemple 01-default-client.md de php8-smpp
        // DSN au format tcp://ip:port
        $dsn = sprintf('%s:%d', $host, $port);

        $this->smppClient = ClientBuilder::createForSockets([$dsn])
            ->setCredentials($systemId, $password)
            ->buildClient();

        // Mode transceiver : envoi + réception (pour l’instant on ne fait que l’envoi)
        $this->smppClient->bindTransceiver();

        $this->info('SMPP connected & bound as transceiver');
    }

    protected function processOutgoingQueue(): void
    {
        $rows = DB::table('goip_outgoing_queue')
            ->where('status', 'pending')
            ->orderBy('id')
            ->limit(10)
            ->get();

        foreach ($rows as $row) {
            $this->sendSingleSms($row);
        }
    }

    protected function readIncomingMessages(): void
    {
        try {
            if (! method_exists($this->smppClient, 'readSMS')) {
                return;
            }

            // On lit au plus un SMS par tick
            $sms = $this->smppClient->readSMS();

            if (! $sms) {
                return;
            }

            // Logs utiles mais pas trop verbeux
            $this->info('📥 Inbound class: ' . get_class($sms));
            $this->info("📥 Inbound print_r (résumé):\n" . print_r([
                    'message' => $sms->message ?? null,
                ], true));

            // Extraction des infos
            $from = $this->extractAddressValue($sms->source ?? null);
            $to   = $this->extractAddressValue($sms->destination ?? null);
            $body = $sms->message ?? null;

            $this->info("📥 Inbound parsed from={$from} to={$to} body=\"{$body}\"");

            if (! $from || $body === null) {
                $this->error("SMS inbound invalide (from/body manquants)");
                return;
            }

            $this->storeIncomingMessage($from, $to, $body, $sms);

        } catch (\Throwable $e) {
            // Cas normal : aucun message dispo sur socket non bloquante
            if (str_contains($e->getMessage(), 'Resource temporarily unavailable')) {
                return;
            }

            $this->error("Erreur lecture SMS entrants (SMPP deliver_sm) : " . $e->getMessage());
            \Log::error("Erreur inbound SMPP", ['exception' => $e]);
        }
    }

    protected function sendSingleSms($row): void
    {
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

        try {
            // TON / NPI en dur (valeurs SMPP standard)
            //  - from : alphanumérique
            //  - to   : international E.164
            $from = new Address(
                $sim->sender_id ?: ($sim->phone_number ?? 'GOIP'),
                5, // TON_ALPHANUMERIC
                0  // NPI_UNKNOWN
            );

            $to = new Address(
                $row->phone_number,
                1, // TON_INTERNATIONAL
                1  // NPI_E164
            );

            $this->info("submit_sm to {$row->phone_number} : {$row->body}");

            $this->smppClient->sendSMS(
                $from,
                $to,
                $row->body
            );

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
        } catch (\Throwable $e) {
            $this->error("SMPP error: " . $e->getMessage());

            DB::table('goip_outgoing_queue')
                ->where('id', $row->id)
                ->update([
                    'status'        => 'failed',
                    'error_message' => $e->getMessage(),
                    'updated_at'    => now(),
                ]);

            $message->update([
                'status'        => 'failed',
                'failed_at'     => now(),
                'error_message' => $e->getMessage(),
            ]);

            // On pourrait tenter un reconnect ici si besoin
        }
    }

    /**
     * Enregistre un SMS entrant dans la base (conversation + message).
     */
    protected function storeIncomingMessage(string $from, ?string $to, string $body, object $rawSms): void
    {
        // On essaie de retrouver la SIM à partir du numéro destinataire
        $sim = null;
        if ($to) {
            $sim = SimCard::where('phone_number', $to)->first();
        }

        $receivedAt = now();

        // Conversation = couple (numéro distant + SIM)
        $conversation = SmsConversation::firstOrCreate(
            [
                'phone_number' => $from,
                'sim_card_id'  => optional($sim)->id,
            ],
            [
                'last_message_preview' => $body,
                'last_direction'       => 'inbound',
                'last_message_at'      => $receivedAt,
            ]
        );

        $message = SmsMessage::create([
            'sms_conversation_id' => $conversation->id,
            'sim_card_id'         => optional($sim)->id,
            'contact_id'          => null,
            'phone_number'        => $from,
            'direction'           => 'inbound',
            'status'              => 'received',
            'body'                => $body,
            // Si ta colonne existe :
            'received_at'         => $receivedAt,
        ]);

        $conversation->update([
            'last_message_preview' => $body,
            'last_direction'       => 'inbound',
            'last_message_at'      => $message->created_at,
            'unread_inbound_count' => ($conversation->unread_inbound_count ?? 0) + 1,
        ]);

        $this->info("✅ Inbound SMS stocké from={$from}, conversation #{$conversation->id}");
    }

    /**
     * Extrait la valeur d'une adresse SMPP (Smpp\Pdu\Address) même si la propriété est privée.
     */
    protected function extractAddressValue(?object $address): ?string
    {
        if (! $address instanceof Address) {
            return null;
        }

        // Si un getter existe, on le privilégie
        if (method_exists($address, 'getValue')) {
            return $address->getValue();
        }

        // Fallback bourrin : reflection sur la propriété privée "value"
        try {
            $ref = new \ReflectionClass($address);
            if ($ref->hasProperty('value')) {
                $prop = $ref->getProperty('value');
                $prop->setAccessible(true);

                $value = $prop->getValue($address);

                return is_string($value) ? $value : null;
            }
        } catch (\Throwable $e) {
            // on ignore, on renverra null
        }

        return null;
    }
}
