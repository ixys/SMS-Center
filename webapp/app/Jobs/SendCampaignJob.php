<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Services\Campaigns\CampaignSender;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $campaignId,
    ) {
    }

    public function handle(CampaignSender $sender): void
    {
        /** @var Campaign|null $campaign */
        $campaign = Campaign::with(['contactGroups', 'recipients', 'platformAccount.platform'])
                            ->find($this->campaignId);

        if (! $campaign) {
            return; // campagne supprimée
        }

        // Si la campagne a été annulée / déjà terminée entre-temps, on ne fait rien
        if (! in_array($campaign->status, ['scheduled', 'running'])) {
            return;
        }

        // On (re)construit les destinataires (idempotent : firstOrCreate dans CampaignSender)
        $sender->buildRecipients($campaign);

        // Et on l’envoie (mettra le statut à running/completed)
        $sender->send($campaign->fresh());
    }
}
