<?php

namespace App\Console\Commands;

use App\Jobs\SendCampaignJob;
use App\Models\Campaign;
use Illuminate\Console\Command;

class DispatchScheduledCampaigns extends Command
{
    protected $signature = 'campaigns:dispatch-scheduled';

    protected $description = 'Dispatch les campagnes Messaging dont la date de planification est atteinte.';

    public function handle(): int
    {
        $now = now();

        $campaigns = Campaign::query()
                             ->where('status', 'scheduled')
                             ->whereNotNull('scheduled_at')
                             ->where('scheduled_at', '<=', $now)
                             ->get();

        if ($campaigns->isEmpty()) {
            $this->info('Aucune campagne scheduled à envoyer.');
            return self::SUCCESS;
        }

        foreach ($campaigns as $campaign) {
            // On passe en running pour éviter de la re-dispatch à la minute suivante
            $campaign->update([
                'status' => 'running',
            ]);

            SendCampaignJob::dispatch($campaign->id);

            $this->info("Campagne #{$campaign->id} ({$campaign->name}) dispatchée en queue.");
        }

        return self::SUCCESS;
    }
}
