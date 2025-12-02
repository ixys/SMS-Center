<?php

namespace App\Services\Campaigns;

use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Contact;
use App\Models\Conversation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class CampaignSender
{
    /**
     * Prépare les recipients d’une campagne à partir :
     * - des groupes
     * - des contacts individuels (optionnel, si tu ajoutes plus tard)
     */
    public function buildRecipients(Campaign $campaign, ?array $extraContactIds = []): void
    {
        DB::transaction(function () use ($campaign, $extraContactIds) {
            // 1) Récupérer les contacts des groupes
            $fromGroups = Contact::query()
                                 ->whereHas('groups', function ($q) use ($campaign) {
                                     $q->whereIn('contact_groups.id', $campaign->contactGroups()->pluck('id'));
                                 })
                                 ->pluck('id')
                                 ->all();

            // 2) Contacts fournis explicitement (si tu ajoutes un champ "contacts" côté UI)
            $fromExtra = $extraContactIds ?? [];

            // 3) Merge & unique
            $contactIds = collect($fromGroups)
                ->merge($fromExtra)
                ->unique()
                ->values();

            // 4) Créer les recipients "pending"
            $count = 0;

            foreach ($contactIds as $contactId) {
                CampaignRecipient::firstOrCreate(
                    [
                        'campaign_id' => $campaign->id,
                        'contact_id' => $contactId,
                    ],
                    [
                        'status' => 'pending',
                    ]
                );
                $count++;
            }

            $campaign->update([
                'total_recipients' => $count,
            ]);
        });
    }

    /**
     * Envoie la campagne (synchrone, pour V1).
     * Plus tard tu pourras sortir ça en Job queue.
     */
    public function send(Campaign $campaign): void
    {
        $account = $campaign->platformAccount;

        if (! $account) {
            throw new \RuntimeException("Campaign #{$campaign->id} n’a pas de PlatformAccount associé.");
        }

        $driver = $account->driver();

        $campaign->update(['status' => 'running']);

        $sent = 0;
        $failed = 0;

        /** @var CampaignRecipient $recipient */
        foreach ($campaign->recipients()->where('status', 'pending')->cursor() as $recipient) {
            $contact = $recipient->contact;

            if (! $contact) {
                $recipient->update([
                    'status' => 'failed',
                    'last_error' => 'Contact introuvable',
                ]);
                $failed++;
                continue;
            }

            try {
                // Déterminer l’identifiant distant selon la plateforme
                $remoteId = $this->resolveRemoteId($account, $contact);

                // Trouver ou créer la conversation
                $conversation = Conversation::firstOrCreate(
                    [
                        'platform_account_id' => $account->id,
                        'contact_id'          => $contact->id,
                    ],
                    [
                        'external_conversation_id' => $remoteId,
                        'remote_user_id'           => $remoteId,
                        'remote_username'          => $contact->phone_number ?? $remoteId,
                        'status'                   => 'open',
                    ]
                );

                // Envoi via le driver
                $message = $driver->sendMessage(
                    $account,
                    $conversation,
                    $campaign->content,
                );

                // Lier le message à la campagne si le driver retourne le Message
                if ($message) {
                    $message->update([
                        'campaign_id' => $campaign->id,
                    ]);

                    $recipient->update([
                        'message_id' => $message->id,
                        'status'     => 'sent',
                        'sent_at'    => now(),
                    ]);
                } else {
                    $recipient->update([
                        'status'     => 'sent',
                        'sent_at'    => now(),
                    ]);
                }

                $sent++;
            } catch (Throwable $e) {
                report($e);

                $recipient->update([
                    'status'     => 'failed',
                    'last_error' => $e->getMessage(),
                ]);

                $failed++;
            }
        }

        $campaign->update([
            'status'        => 'completed',
            'sent_count'    => $campaign->sent_count + $sent,
            'failed_count'  => $campaign->failed_count + $failed,
        ]);
    }

    /**
     * Détermine l’identifiant distant à utiliser pour un contact.
     * Pour l’instant : SMS = phone_number E.164.
     */
    protected function resolveRemoteId($account, Contact $contact): string
    {
        $remote = $contact->phone_number;

        if ($account->platform?->code === 'sms') {
            $remote = preg_replace('/[^\d+]/', '', (string) $remote);

            if (str_starts_with($remote, '0') && ! str_starts_with($remote, '+')) {
                $remote = '+33' . substr($remote, 1);
            }
        }

        return $remote;
    }
}
