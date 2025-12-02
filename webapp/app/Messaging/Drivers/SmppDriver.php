<?php

namespace App\Messaging\Drivers;

use App\Enums\ConversationStatus;
use App\Enums\MessageStatus;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\PlatformAccount;
use App\Services\Sms\SmsGateway; // interface vers SmppSmsGateway
use Illuminate\Support\Facades\DB;
use Throwable;

class SmppDriver
{
    public function __construct(
        protected SmsGateway $gateway,
    ) {
    }

    /**
     * Connexion / bind SMPP (transceiver).
     */
    public function connect(): void
    {
        logger()->info('SMPP: connexion au gateway…');
        $this->gateway->connect();
        logger()->info('SMPP: connecté (transceiver prêt).');
    }

    /**
     * Enfile un message outbound dans la table messages.
     * L’envoi réel sera effectué par le worker SMPP.
     */
    public function sendMessage(PlatformAccount $account, Conversation $conversation, string $body, ?Campaign $campaign = null): Message
    {
        $message = Message::create([
            'conversation_uuid' => $conversation->uuid,
            'campaign_uuid'     => $campaign?->uuid,
            'direction'       => 'outbound',
            'status'          => MessageStatus::Pending,
            'content'            => $body,
        ]);

        logger()->info('SMPP: message outbound enfilé', [
            'message_uuid'        => $message->uuid,
            'conversation_uuid'   => $conversation->uuid,
            'platform_account_id' => $account->id,
        ]);

        return $message;
    }

    /**
     * Traite un batch de messages outbound en attente et retourne le nombre
     * de messages effectivement tentés (pending → sending → sent/failed).
     */
    public function processOutboundBatch(int $limit = 10): int
    {
        $processed = 0;

        Message::query()
               ->with(['conversation.platformAccount.platform'])
               ->where('direction', 'outbound')
               ->where('status', 'pending')
               ->whereHas('conversation.platformAccount.platform', fn ($q) => $q->where('code', 'sms'))
               ->orderBy('uuid')
               ->limit($limit)
               ->each(function (Message $message) use (&$processed) {
                   if ($this->sendSingleOutbound($message)) {
                       $processed++;
                   }
               });

        return $processed;
    }

    /**
     * Lit un SMS entrant via SMPP et l’enregistre dans le modèle Messaging.
     * Retourne le nombre de SMS stockés (0 ou 1 dans cette implémentation).
     */
    public function pollInbound(): int
    {
        try {
            $sms = $this->gateway->readSms();

            if (! $sms) {
                return 0;
            }

            $from = $this->normalizePhone($sms->from ?? null);
            $to   = $this->normalizePhone($sms->to ?? null);
            $body = $sms->body ?? null;

            logger()->info('SMPP: SMS inbound brut', [
                'raw_from' => $sms->from ?? null,
                'raw_to'   => $sms->to ?? null,
                'body'     => $body,
            ]);

            if (! $from || $body === null) {
                logger()->warning('SMPP: SMS inbound invalide (from/body manquant)', [
                    'from' => $sms->from ?? null,
                    'to'   => $sms->to ?? null,
                ]);
                return 0;
            }

            $this->storeInbound($from, $to, $body);

            return 1;
        } catch (Throwable $e) {
            if (str_contains($e->getMessage(), 'Resource temporarily unavailable')) {
                // Cas normal sur socket non bloquante
                return 0;
            }

            logger()->error('SMPP: erreur lecture inbound', [
                'exception' => $e,
            ]);

            return 0;
        }
    }

    /**
     * Envoi effectif d’un message outbound via SMPP.
     * Retourne true si on a réellement tenté d’envoyer ce message,
     * false si on l’a ignoré (plus pending, données manquantes, etc.).
     */
    protected function sendSingleOutbound(Message $message): bool
    {
        $attempted = false;

        DB::transaction(function () use (&$attempted, $message) {
            $message->refresh();

            logger()->info('SMPP sendSingleOutbound: init', [
                'message_uuid' => $message->uuid,
            ]);

            if ($message->status !== MessageStatus::Pending) {
                logger()->error('SMPP sendSingleOutbound: Status différent de pending', [
                    'message_uuid' => $message->uuid,
                    'status' => $message->status,
                ]);

                return;
            }

            $attempted = true;

            $message->update([
                'status'     => MessageStatus::Sending,
                'updated_at' => now(),
            ]);

            $conversation = $message->conversation;
            $account      = $conversation?->platformAccount;

            if (! $conversation || ! $account) {
                $message->update([
                    'status'        => MessageStatus::Failed,
                    'failed_at'     => now(),
                    'error_message' => 'Conversation ou compte plateforme manquant',
                ]);

                logger()->error('SMPP outbound: message sans conversation ou compte', [
                    'message_uuid' => $message->uuid,
                ]);

                return;
            }

            $rawTo = $conversation->remote_user_id
                     ?? $conversation->remote_username
                        ?? $message->to
                           ?? null;

            $to = $this->normalizePhone($rawTo);

            if (! $to) {
                $message->update([
                    'status'        => MessageStatus::Failed,
                    'failed_at'     => now(),
                    'error_message' => 'Numéro destinataire invalide',
                ]);

                logger()->warning('SMPP outbound: numéro destinataire invalide', [
                    'message_uuid' => $message->uuid,
                    'raw_to'     => $rawTo,
                ]);

                return;
            } else
                logger()->info('SMPP outbound: numéro destinataire', [
                    'message_uuid' => $message->uuid,
                    'raw_to'     => $rawTo,
                ]);

            $fromValue = $account->smpp_sender_id
                ?: $account->smpp_phone_number;

            if (! $fromValue) {
                $message->update([
                    'status'        => MessageStatus::Failed,
                    'failed_at'     => now(),
                    'error_message' => 'Aucun expéditeur SMPP configuré',
                ]);

                logger()->error('SMPP outbound: aucun expéditeur configuré', [
                    'message_uuid'          => $message->uuid,
                    'platform_account_id' => $account->id,
                ]);

                return;
            }

            try {
                logger()->info('SMPP outbound: envoi SMS', [
                    'message_uuid'          => $message->uuid,
                    'from'                => $fromValue,
                    'to'                  => $to,
                    'platform_account_id' => $account->id,
                    'sim_slot'            => $account->smpp_sim_slot,
                ]);

                $this->gateway->sendSms(
                    from: $fromValue,
                    to: $to,
                    body: $message->content,
                    options: [
                        'sim_slot' => $account->smpp_sim_slot,
                    ]
                );

                $now = now();

                $message->update([
                    'status'     => MessageStatus::Sent,
                    'sent_at'    => $now,
                    'updated_at' => $now,
                ]);

                $conversation->update([
                    'last_message_at'  => $now,
                    'last_outbound_at' => $now,
                ]);

                logger()->info('SMPP outbound: SMS envoyé', [
                    'message_uuid' => $message->uuid,
                ]);

            } catch (Throwable $e) {
                $message->update([
                    'status'        => MessageStatus::Failed,
                    'failed_at'     => now(),
                    'error_message' => $e->getMessage(),
                ]);

                logger()->error('SMPP outbound: erreur d’envoi', [
                    'message_uuid' => $message->uuid,
                    'exception'  => $e,
                ]);
            }
        });

        return $attempted;
    }

    /**
     * Enregistre un SMS entrant.
     */
    protected function storeInbound(string $from, string $to, string $body): void
    {
        logger()->info('SMPP: storeInbound', [
            'from' => $from,
            'to'   => $to,
            'body' => $body,
        ]);

        $account = null;

        if ($to) {
            $account = PlatformAccount::where('smpp_phone_number', $to)->firstOrFail();

            logger()->info('SMPP: storeInbound account', [
                'id' => $account->id,
                'name' => $account->name,
            ]);
        }

        /** @var Contact $contact */
        $contact = Contact::firstOrCreate(
            ['phone_number' => $from],
            ['first_name' => null, 'last_name' => null]
        );

        $now = now();

        /** @var Conversation $conversation */
        $conversation = Conversation::firstOrCreate(
            [
                'platform_account_id' => $account->id,
                'contact_uuid'          => $contact->uuid,
            ],
            [
                'external_conversation_id' => $from,
                'remote_user_id'           => $from,
                'remote_username'          => $from,
                'title'                    => $contact->first_name
                    ? 'SMS - ' . $contact->first_name . ' ' . $contact->last_name
                    : 'SMS - ' . $from,
                'status'                   => ConversationStatus::Open,
                'last_message_at'          => $now,
                'last_inbound_at'          => $now,
            ]
        );

        $message = Message::create([
            'conversation_uuid' => $conversation->uuid,
            'campaign_uuid'     => null,
            'direction'       => 'inbound',
            'status'          => MessageStatus::Received,
            'content'            => $body,
            'received_at'     => $now,
        ]);

        $conversation->update([
            'last_message_at' => $message->created_at,
            'last_inbound_at' => $message->created_at,
        ]);

        logger()->info('SMPP inbound: SMS stocké', [
            'from'            => $from,
            'to'              => $to,
            'message_uuid'      => $message->uuid,
            'conversation_uuid' => $conversation->uuid,
        ]);
    }

    /**
     * Normalisation simple FR → E.164.
     */
    protected function normalizePhone(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        $normalized = preg_replace('/[^\d+]/', '', $phone);

        if (! $normalized) {
            return null;
        }

        if (str_starts_with($normalized, '0') && ! str_starts_with($normalized, '+')) {
            $normalized = '+33' . substr($normalized, 1);
        }

        return $normalized;
    }
}
