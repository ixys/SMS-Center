<?php

namespace App\Messaging\Contracts;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\PlatformAccount;

interface PlatformDriver
{
    /**
     * Traite un message entrant depuis la plateforme externe.
     * $payload doit être déjà décodé (array).
     */
    public function handleIncoming(PlatformAccount $account, array $payload): Message;

    /**
     * Envoie un message vers la plateforme.
     */
    public function sendMessage(PlatformAccount $account, Conversation $conversation, string $content, array $options = []): Message;
}
