<?php

namespace App\Services\Sms;

interface SmsGateway
{
    /**
     * Envoie un SMS.
     *
     * @param  string  $from   Numéro émetteur (E.164 si possible)
     * @param  string  $to     Numéro destinataire (E.164 si possible)
     * @param  string  $content Contenu du message (texte brut)
     * @param  array   $options Options spécifiques (ID campagne, tags, etc.)
     *
     * @return array {
     *   'external_message_id' => string|null,
     *   'status' => string, // ex: 'queued', 'sent', 'delivered'
     *   'sent_at' => \DateTimeInterface|null,
     * }
     */
    public function send(string $from, string $to, string $content, array $options = []): array;
}
