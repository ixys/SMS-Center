<?php

namespace App\Console\Commands;

use App\Messaging\Drivers\SmppDriver;
use Illuminate\Console\Command;

class SmppWorkerCommand extends Command
{
    protected $signature = 'smpp:worker';

    protected $description = 'Worker SMPP : boucle de traitement des SMS (outbound + inbound).';

    public function __construct(
        protected SmppDriver $driver,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Démarrage du worker SMPP (SmppDriver)…');

        // Connexion SMPP
        $this->driver->connect();

        $this->info('SMPP connecté. Boucle worker en cours…');

        while (true) {
            // 1) Traiter les envois
            $outboundCount = $this->driver->processOutboundBatch(limit: 20);
            if ($outboundCount > 0) {
                $this->info("➡️  {$outboundCount} SMS outbound traités (pending → sent/failed).");
            }

            // 2) Traiter la réception
            $inboundCount = $this->driver->pollInbound();
            if ($inboundCount > 0) {
                $this->info("⬅️  {$inboundCount} SMS inbound reçus et stockés.");
            }

            // 3) Petite pause
            usleep(100_000); // 100 ms
        }

        return self::SUCCESS;
    }
}
