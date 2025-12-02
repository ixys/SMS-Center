<?php

namespace App\Services\Sms;

/**
 * Interface générique pour un gateway SMS.
 * Implémentation SMPP : SmppSmsGateway.
 * Plus tard : HTTP API, etc.
 */
interface SmsGateway
{
    /**
     * Établit la connexion au provider (SMPP, HTTP, etc.).
     */
    public function connect(): void;

    /**
     * Envoie un SMS.
     *
     * @param string $from    Expéditeur (alphanum ou numéro)
     * @param string $to      Destinataire (E.164 de préférence)
     * @param string $body    Contenu du SMS
     * @param array  $options Options spécifiques (ex: sim_slot pour GoIP)
     */
    public function sendSms(string $from, string $to, string $body, array $options = []): void;

    /**
     * Lit un SMS entrant, si disponible.
     *
     * Retourne un objet générique :
     *  - from : string|null
     *  - to   : string|null
     *  - body : string|null
     *  - raw  : mixed (objet natif de la lib SMPP)
     *
     * ou null s’il n’y a rien à lire.
     */
    public function readSms(): ?object;
}
