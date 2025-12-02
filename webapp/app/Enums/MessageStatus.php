<?php

namespace App\Enums;

/**
 * Statuts d'un message individuel.
 */
enum MessageStatus: string
{
    case Received  = 'received';    // Message inbound reçu
    case Pending = 'pending';       // En attente d'envoi'
    case Sending = 'sending';
    case Sent      = 'sent';        // Message outbound envoyé avec succès
    case Failed    = 'failed';      // Envoi en erreur
    case Delivered = 'delivered';   // Accusé de réception opérateur
    case Read      = 'read';        // Si tu supportes le read receipt (WhatsApp, RCS...)

    /**
     * Libellé français du statut.
     */
    public function label(): string
    {
        return match ($this) {
            self::Received  => 'Reçu',
            self::Pending   => 'En attente',
            self::Sending   => 'En cours d\'envoi',
            self::Sent      => 'Envoyé',
            self::Failed    => 'Échec',
            self::Delivered => 'Livré',
            self::Read      => 'Lu',
        };
    }

    /**
     * Tableau associatif "value => label".
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
