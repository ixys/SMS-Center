<?php

namespace App\Services\Sms;

use Smpp\Client;
use Smpp\ClientBuilder;
use Smpp\Exceptions\SmppInvalidArgumentException;
use Smpp\Pdu\Address;
use Throwable;

/**
 * Implémentation SMPP du SmsGateway, basée sur php8-smpp,
 * avec gestion de reconnexion automatique si la socket tombe.
 */
class SmppSmsGateway implements SmsGateway
{
    protected ?Client $client = null;

    /**
     * Nombre maximal de tentatives (connexion + opération) par appel.
     */
    protected int $maxAttempts = 2; // 1 tentative normale + 1 tentative après reconnexion

    public function __construct()
    {
        // Pas de connexion en eager, on laisse lazy-connect
    }

    /**
     * Connexion au SMSC SMPP en mode transceiver.
     */
    public function connect(): void
    {
        // Si déjà connecté, on ne refait pas un bind
        if ($this->client instanceof Client) {
            return;
        }

        $host     = config('smpp.host');
        $port     = (int) config('smpp.port', 2775);
        $systemId = config('smpp.system_id');
        $password = config('smpp.password');

        if (! $host || ! $systemId || ! $password) {
            throw new \RuntimeException('Configuration SMPP incomplète (smpp.host / smpp.system_id / smpp.password).');
        }

        $dsn = sprintf('%s:%d', $host, $port);

        logger()->info('SmppSmsGateway: connexion au SMSC', [
            'dsn'      => $dsn,
            'systemId' => $systemId,
        ]);

        $this->client = ClientBuilder::createForSockets([$dsn])
                                     ->setCredentials($systemId, $password)
                                     ->buildClient();

        // Mode transceiver (envoi + réception)
        $this->client->bindTransceiver();

        logger()->info('SmppSmsGateway: connecté et bind transceiver.');
    }

    /**
     * Envoi d’un SMS via SMPP avec logique de reconnexion si la socket tombe.
     */
    public function sendSms(string $from, string $to, string $body, array $options = []): void
    {
        $attempt = 0;

        RETRY:
        $attempt++;

        try {
            $this->ensureConnected();

            if (! $this->client instanceof Client) {
                throw new \RuntimeException('SmppSmsGateway: client SMPP non initialisé.');
            }

            $fromAddress = $this->makeAddressFrom($from);
            $toAddress   = $this->makeAddressTo($to);

            logger()->debug('SmppSmsGateway: submit_sm', [
                'from'    => $from,
                'to'      => $to,
                'body'    => $body,
                'options' => $options,
                'attempt' => $attempt,
            ]);

            $this->client->sendSMS(
                $fromAddress,
                $toAddress,
                $body
            );
        } catch (Throwable $e) {
            if ($this->isConnectionError($e) && $attempt < $this->maxAttempts) {
                logger()->warning('SmppSmsGateway: erreur de connexion lors de sendSms(), tentative de reconnexion…', [
                    'exception' => $e,
                ]);

                $this->resetClient();
                // on retente une fois après reconnexion
                goto RETRY;
            }

            // Si ce n’est pas une erreur de connexion, ou qu’on a épuisé les tentatives → on remonte
            logger()->error('SmppSmsGateway: échec sendSms()', [
                'exception' => $e,
                'attempts'  => $attempt,
            ]);

            throw $e;
        }
    }

    /**
     * Lecture d’un SMS entrant via SMPP, avec reconnexion si nécessaire.
     *
     * Retourne un objet standardisé ou null s’il n’y a rien à lire.
     */
    public function readSms(): ?object
    {
        $attempt = 0;

        RETRY:
        $attempt++;

        try {
            $this->ensureConnected();

            if (! $this->client instanceof Client) {
                throw new \RuntimeException('SmppSmsGateway: client SMPP non initialisé.');
            }

            if (! method_exists($this->client, 'readSMS')) {
                logger()->warning('SmppSmsGateway: méthode readSMS introuvable sur le client SMPP.');
                return null;
            }

            $sms = $this->client->readSMS();

            if (! $sms) {
                return null;
            }

            $from = $this->extractAddressValue($sms->source ?? null);
            $to   = $this->extractAddressValue($sms->destination ?? null);
            $body = $sms->message ?? null;

            logger()->debug('SmppSmsGateway: SMS inbound brut', [
                'from'    => $from,
                'to'      => $to,
                'body'    => $body,
                'attempt' => $attempt,
            ]);

            return (object) [
                'from' => $from,
                'to'   => $to,
                'body' => $body,
                'raw'  => $sms,
            ];
        } catch (Throwable $e) {
            // Cas normal de socket non bloquante → on ne reconnecte pas, on ignore
            if (str_contains($e->getMessage(), 'Resource temporarily unavailable')) {
                return null;
            }

            if ($this->isConnectionError($e) && $attempt < $this->maxAttempts) {
                logger()->warning('SmppSmsGateway: erreur de connexion lors de readSms(), tentative de reconnexion…', [
                    'exception' => $e,
                ]);

                $this->resetClient();
                goto RETRY;
            }

            logger()->error('SmppSmsGateway: échec readSms()', [
                'exception' => $e,
                'attempts'  => $attempt,
            ]);

            return null;
        }
    }

    /**
     * Assure que la connexion SMPP est établie.
     */
    protected function ensureConnected(): void
    {
        if (! $this->client instanceof Client) {
            $this->connect();
        }
    }

    /**
     * Invalide le client SMPP, forçant une reconnexion au prochain ensureConnected().
     */
    protected function resetClient(): void
    {
        $this->client = null;
    }

    /**
     * Détecte si une erreur est probablement liée à la connexion réseau / socket.
     */
    protected function isConnectionError(Throwable $e): bool
    {
        $msg = $e->getMessage();

        $patterns = [
            'Broken pipe',
            'Connection reset by peer',
            'Connection timed out',
            'Connection refused',
            'EOF',
            'failed to open stream',
            'bind failed',
            'closed connection',
        ];

        foreach ($patterns as $pattern) {
            if (stripos($msg, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Construit l’Address SMPP pour l’expéditeur.
     *
     * @throws SmppInvalidArgumentException
     */
    protected function makeAddressFrom(string $from): Address
    {
        // Numéro en +33… → TON_INTERNATIONAL / NPI_E164
        if (preg_match('/^\+?\d+$/', $from)) {
            return new Address($from, 1, 1); // TON_INTERNATIONAL, NPI_E164
        }

        // Sinon, alphanumérique
        return new Address($from, 5, 0); // TON_ALPHANUMERIC, NPI_UNKNOWN
    }

    /**
     * Construit l’Address SMPP pour le destinataire (E.164).
     */
    protected function makeAddressTo(string $to): Address
    {
        return new Address($to, 1, 1); // TON_INTERNATIONAL, NPI_E164
    }

    /**
     * Extrait la valeur d’une Address SMPP (Smpp\Pdu\Address).
     */
    protected function extractAddressValue(?object $address): ?string
    {
        if (! $address instanceof Address) {
            return null;
        }

        if (method_exists($address, 'getValue')) {
            return $address->getValue();
        }

        try {
            $ref = new \ReflectionClass($address);
            if ($ref->hasProperty('value')) {
                $prop = $ref->getProperty('value');
                $prop->setAccessible(true);

                $value = $prop->getValue($address);

                return is_string($value) ? $value : null;
            }
        } catch (Throwable $e) {
            // on ignore, on renverra null
        }

        return null;
    }
}
