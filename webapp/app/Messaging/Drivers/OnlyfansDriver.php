<?php

namespace App\Messaging\Drivers;

use App\Messaging\Contracts\PlatformDriver;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\PlatformAccount;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class OnlyfansDriver implements PlatformDriver
{
    public function handleIncoming(PlatformAccount $account, array $payload): Message
    {
        // Exemple de payload normalisé :
        // [
        //   'conversation_id' => '123',
        //   'remote_user_id' => '456',
        //   'remote_username' => 'fanboy',
        //   'message_id' => '789',
        //   'direction' => 'inbound',
        //   'from' => 'fanboy',
        //   'to' => 'creator',
        //   'content' => 'Hey daddy',
        //   'attachments' => [...],
        //   'sent_at' => '2025-11-18T16:12:00Z',
        // ]

        $conversation = Conversation::firstOrCreate(
            [
                'platform_account_id' => $account->id,
                'external_conversation_id' => Arr::get($payload, 'conversation_id'),
            ],
            [
                'remote_user_id' => Arr::get($payload, 'remote_user_id'),
                'remote_username' => Arr::get($payload, 'remote_username'),
                'title' => 'OF - ' . Arr::get($payload, 'remote_username'),
                'status' => 'open',
            ]
        );

        $message = $conversation->messages()->create([
            'platform_account_id' => $account->id,
            'external_message_id' => Arr::get($payload, 'message_id'),
            'direction' => Arr::get($payload, 'direction', 'inbound'),
            'from' => Arr::get($payload, 'from'),
            'to' => Arr::get($payload, 'to'),
            'content' => Arr::get($payload, 'content'),
            'attachments' => Arr::get($payload, 'attachments', []),
            'status' => 'delivered',
            'sent_at' => Carbon::parse(Arr::get($payload, 'sent_at')),
        ]);

        $conversation->update([
            'last_message_at' => $message->sent_at,
            'last_inbound_at' => $message->direction === 'inbound' ? $message->sent_at : $conversation->last_inbound_at,
            'last_outbound_at' => $message->direction === 'outbound' ? $message->sent_at : $conversation->last_outbound_at,
        ]);

        return $message;
    }

    public function sendMessage(PlatformAccount $account, Conversation $conversation, string $content, array $options = []): Message
    {
        // Ici tu appelles l’API OnlyFans (ou le provider tiers)
        // pour envoyer le message, et tu récupères l’ID, timestamp, etc.

        // Pour l’exemple, on simule :
        $externalMessageId = 'simulated_' . uniqid();

        $message = $conversation->messages()->create([
            'platform_account_id' => $account->id,
            'external_message_id' => $externalMessageId,
            'direction' => 'outbound',
            'from' => $account->external_username,
            'to' => $conversation->remote_username,
            'content' => $content,
            'attachments' => $options['attachments'] ?? [],
            'status' => 'delivered',
            'sent_at' => now(),
        ]);

        $conversation->update([
            'last_message_at' => $message->sent_at,
            'last_outbound_at' => $message->sent_at,
        ]);

        return $message;
    }
}
