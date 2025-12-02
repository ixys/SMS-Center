<?php

namespace App\Enums;

/**
 * Statuts possibles d'une conversation.
 */
enum ConversationStatus: string
{
    case Open    = 'open';        // Conversation active
    case Closed  = 'closed';      // Conversation fermée manuellement
    case Pending = 'pending';     // En attente (assignation, validation…)

    /**
     * Libellé français du statut.
     */
    public function label(): string
    {
        return match ($this) {
            self::Open    => 'Ouverte',
            self::Closed  => 'Fermée',
            self::Pending => 'En attente',
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
