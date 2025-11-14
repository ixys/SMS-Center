<?php

namespace App\Console\Commands;

use App\Models\SimCard;
use App\Models\SmsMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use Smpp\ClientBuilder;
use Smpp\Pdu\Address;
use Smpp\Smpp;

class SmppWorkerCommand extends Command
{
    protected $signature = 'smpp:worker';

    protected $description = 'SMPP worker for sending SMS via GoIP4';

    protected SmppClient $smpp;
    protected int $lastKeepAlive = 0;

    public function handle(): int
    {
        $this->info('Starting SMPP worker…');

        $this->connect();

        while (true) {
            $this->processOutgoingQueue();

            // Pour l’instant on ne gère pas les SMS entrants via SMPP
            // $this->readIncomingMessages();

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

        // === Copie de l’exemple php8-smpp, adapté ===
        $this->transport = new Socket([$host], $port);
        $this->transport->debug = false;
        $this->transport->setRecvTimeout(10000);

        $this->smpp = new SmppClient($this->transport);
        $this->smpp->debug = false;

        $this->transport->open();

        // ESME côté client → bindTransmitter (envoi uniquement)
        $this->smpp->bindTransmitter($systemId, $password);

        $this->info('SMPP connected & bound as transmitter');

        $this->lastKeepAlive = time();
    }

    protected function reconnect(): void
    {
        $this->error('Reconnecting SMPP…');

        try {
            $this->smpp?->close();
        } catch (\Throwable $e) {
            // ignore
        }

        sleep(2);

        $this->connect();
    }

    /**
     * Commentaire FR :
     * Récupère des messages en attente dans goip_outgoing_queue et les envoie.
     */
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
            // === Adresses SMPP, comme dans l’exemple ===
            $from = new Address(
                $sim->sender_id ?: ($sim->phone_number ?? 'GOIP'),
                Smpp::TON_ALPHANUMERIC,
                Smpp::NPI_UNKNOWN
            );

            $to = new Address(
                $row->phone_number,
                Smpp::TON_INTERNATIONAL,
                Smpp::NPI_E164
            );

            $text = $row->body;

            $this->info("SMPP submit_sm to {$row->phone_number} : {$text}");

            $this->smpp->sendSMS(
                $from,
                $to,
                $text
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
            $this->error('Error sending SMS via SMPP (php8-smpp): ' . $e->getMessage());

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

            $this->reconnect();
        }
    }

    protected function readIncomingMessages(): void
    {
        // TODO: lecture des deliver_sm quand on attaquera la partie inbound
        return;
    }
}
