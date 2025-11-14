<?php

namespace App\Models;

use App\Models\Base\SmsConversation as BaseSmsConversation;

class SmsConversation extends BaseSmsConversation
{
	protected $fillable = [
		'contact_id',
		'phone_number',
		'sim_card_id',
		'last_message_preview',
		'last_direction',
		'last_message_at',
		'unread_inbound_count',
		'is_archived',
		'is_muted',
		'metadata'
	];

    protected function storeIncomingMessage(string $from, ?string $to, string $body, $rawSms): void
    {
        // On essaye d’identifier la SIM à partir du numéro destinataire (MSISDN de la SIM)
        $sim = null;
        if ($to) {
            $sim = SimCard::where('phone_number', $to)->first();
        }

        // On calcule une date de réception
        $receivedAt = now();
        if (isset($rawSms->time) && $rawSms->time instanceof \DateTimeInterface) {
            $receivedAt = $rawSms->time;
        }

        // Conversation = pair (numéro distant + SIM)
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
            'contact_id'          => null, // tu pourras mettre en place un matching contact plus tard
            'phone_number'        => $from,
            'direction'           => 'inbound',
            'status'              => 'received',
            'body'                => $body,
            'received_at'         => $receivedAt,
        ]);

        // Mise à jour de la conversation (dernier message + compteur non lus)
        $conversation->update([
            'last_message_preview' => $body,
            'last_direction'       => 'inbound',
            'last_message_at'      => $message->created_at,
            'unread_inbound_count' => ($conversation->unread_inbound_count ?? 0) + 1,
        ]);

        $this->info("Inbound SMS from {$from} stored (conversation #{$conversation->id})");
    }
}
