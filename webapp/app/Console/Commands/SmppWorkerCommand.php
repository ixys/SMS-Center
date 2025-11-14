<?php

namespace App\Console\Commands;

use App\Models\SimCard;
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
            $this->processOutgoingQueue();
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
}
