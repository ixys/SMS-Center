<?php

// app/Messaging/Drivers/SmsDriver.php

namespace App\Messaging\Drivers;

use App\Messaging\Contracts\PlatformDriver;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\PlatformAccount;
use App\Services\Sms\SmsGateway;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class SmsDriver implements PlatformDriver
{
    public function __construct(
        protected SmsGateway $gateway
    ) {
        // Le SmsGateway est injecté par le conteneur de services Laravel.
    }

    /**
     * Traite un message entrant provenant de ton infra SMS (SMPP ou autre).
     *
     * Payload attendu (à adapter à ta stack) :
     * [
     *   'from' => '+33612345678',     // numéro émetteur (contact)
     *   'to' => '+33687654321',       // numéro destinataire (ton numéro)
     *   'content' => 'Bonjour',       // texte du SMS
     *   'message_id' => 'abc123',     // ID message côté SMS provider
     *   'sent_at' => '2025-12-01T20:15:00Z', // optionnel
     * ]
     */
    public function handleIncoming(PlatformAccount $account, array $payload): Message
    {
        $from = $this->normalizePhone(Arr::get($payload, 'from'));
        $to = $this->normalizePhone(
            Arr::get($payload, 'to') ??
            Arr::get($payload, 'local_number') ??
            ($account->settings['default_number'] ?? null)
        );

        if (! $from || ! $to) {
            // À adapter : tu peux lever une exception custom, ou logger et retourner une erreur HTTP.
            throw new \InvalidArgumentException('Payload SMS invalide : "from" ou "to" manquant.');
        }

        // Clé de conversation : généralement le numéro distant (contact)
        $remoteNumber = $from;

        $conversation = Conversation::firstOrCreate(
            [
                'platform_account_id' => $account->id,
                'external_conversation_id' => $remoteNumber, // on utilise le numéro comme ID de conversation
            ],
            [
                'remote_user_id' => $remoteNumber,
                'remote_username' => $remoteNumber,
                'title' => 'SMS - ' . $remoteNumber,
                'status' => 'open',
            ]
        );

        $sentAt = Arr::get($payload, 'sent_at')
            ? Carbon::parse(Arr::get($payload, 'sent_at'))
            : now();

        $message = $conversation->messages()->create([
            'platform_account_id' => $account->id,
            'external_message_id' => Arr::get($payload, 'message_id'),
            'direction' => 'inbound',
            'from' => $from,
            'to' => $to,
            'content' => Arr::get($payload, 'content'),
            'attachments' => [],
            'status' => 'delivered',
            'sent_at' => $sentAt,
        ]);

        $conversation->update([
            'last_message_at' => $sentAt,
            'last_inbound_at' => $sentAt,
        ]);

        return $message;
    }

    /**
     * Envoie un message vers un contact par SMS.
     *
     * @param  PlatformAccount  $account       Compte SMS utilisé (numéro émetteur, etc.)
     * @param  Conversation     $conversation  Conversation liée (contient le numéro distant)
     * @param  string           $content       Message texte
     * @param  array            $options       Options additionnelles (tags, campagne, etc.)
     */
    public function sendMessage(PlatformAccount $account, Conversation $conversation, string $content, array $options = []): Message
    {
        // Numéro distant : on prend remote_user_id (ou fallback sur external_conversation_id)
        $to = $this->normalizePhone(
            $conversation->remote_user_id ?: $conversation->external_conversation_id
        );

        if (! $to) {
            throw new \InvalidArgumentException('Impossible de déterminer le numéro destinataire pour cette conversation SMS.');
        }

        // Numéro émetteur : config du compte (settings) ou credentials
        $from = $this->normalizePhone(
            $account->settings['default_number']
            ?? $account->credentials['from_number']
               ?? null
        );

        if (! $from) {
            throw new \InvalidArgumentException('Aucun numéro émetteur configuré pour ce compte SMS.');
        }

        // Appel au gateway SMS
        $result = $this->gateway->send($from, $to, $content, $options);

        $sentAt = $result['sent_at'] ?? now();

        $message = $conversation->messages()->create([
            'platform_account_id' => $account->id,
            'external_message_id' => $result['external_message_id'] ?? null,
            'direction' => 'outbound',
            'from' => $from,
            'to' => $to,
            'content' => $content,
            'attachments' => $options['attachments'] ?? [],
            'status' => $result['status'] ?? 'sent',
            'sent_at' => $sentAt,
        ]);

        $conversation->update([
            'last_message_at' => $sentAt,
            'last_outbound_at' => $sentAt,
        ]);

        return $message;
    }

    /**
     * Normalise un numéro de téléphone (très basique).
     *
     * Ici tu peux :
     *  - retirer les espaces / tirets / parenthèses
     *  - forcer le format international (E.164) si tu connais le pays par défaut
     */
    protected function normalizePhone(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        // Supprime tout sauf + et les chiffres
        $normalized = preg_replace('/[^\d+]/', '', $phone);

        // Exemple : si ton infra travaille uniquement en E.164 et que tu es majoritairement en France,
        // tu peux ajouter ici la logique pour transformer "0612345678" en "+33612345678".
        if (str_starts_with($normalized, '0') && ! str_starts_with($normalized, '+')) {
            // Très basique : on remplace "0" initial par "+33"
            $normalized = '+33' . substr($normalized, 1);
        }

        return $normalized;
    }
}
