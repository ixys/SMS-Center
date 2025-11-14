<?php

namespace App\Services;

use App\Jobs\SendSmsViaGoip;
use App\Models\Contact;
use App\Models\SimCard;
use App\Models\SmsConversation;
use App\Models\SmsMessage;
use Illuminate\Support\Carbon;

class SmsOutboundService
{
    /**
     * Commentaire FR :
     * Crée (ou retrouve) la conversation, crée un message sortant "queued"
     * puis dispatch le job d'envoi via GoIP.
     *
     * @param  string  $phoneNumber   Numéro de destination
     * @param  string  $body          Contenu du SMS
     * @param  SimCard|null  $simCard SIM / canal GoIP à utiliser (optionnel)
     * @param  Contact|null  $contact Contact lié (optionnel)
     * @param  SmsConversation|null $conversation Conversation existante (optionnel)
     */
    public function sendOutboundMessage(
        string $phoneNumber,
        string $body,
        ?SimCard $simCard = null,
        ?Contact $contact = null,
        ?SmsConversation $conversation = null,
    ): SmsMessage {
        // 1) Si pas de conversation fournie, on en retrouve une ou on la crée
        if (! $conversation) {
            $conversation = SmsConversation::firstOrCreate(
                [
                    'phone_number' => $phoneNumber,
                    'sim_card_id'  => optional($simCard)->id,
                ],
                [
                    'contact_id'           => optional($contact)->id,
                    'last_message_preview' => $body,
                    'last_direction'       => 'outbound',
                    'last_message_at'      => Carbon::now(),
                ]
            );
        } else {
            // Si une conversation existe déjà, on la met à jour
            $conversation->update([
                'contact_id'           => $contact?->id ?? $conversation->contact_id,
                'last_message_preview' => $body,
                'last_direction'       => 'outbound',
                'last_message_at'      => Carbon::now(),
            ]);
        }

        // 2) On crée le SmsMessage sortant en statut "queued"
        $message = SmsMessage::create([
            'sms_conversation_id' => $conversation->id,
            'contact_id'          => optional($contact)->id,
            'sim_card_id'         => optional($simCard)->id,
            'phone_number'        => $phoneNumber,
            'direction'           => 'outbound',
            'status'              => 'queued',
            'body'                => $body,
            'scheduled_at'        => null,
        ]);

        // 3) On dispatch le job qui va alimenter la file goip_outgoing_queue
        SendSmsViaGoip::dispatch($message->id);

        return $message;
    }
}
