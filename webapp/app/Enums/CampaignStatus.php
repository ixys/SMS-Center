<?php

namespace App\Enums;

/**
 * Statuts d'une campagne SMS / multi-plateformes.
 */
enum CampaignStatus: string
{
    case Draft      = 'draft';       // En préparation
    case Scheduled  = 'scheduled';   // Planifiée
    case Sending    = 'sending';     // En cours d'envoi
    case Paused     = 'paused';      // Pause admin
    case Completed  = 'completed';   // Finie
    case Failed     = 'failed';      // Erreur globale

    /**
     * Libellé français du statut.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft     => 'Brouillon',
            self::Scheduled => 'Planifiée',
            self::Sending   => 'Envoi en cours',
            self::Paused    => 'En pause',
            self::Completed => 'Terminée',
            self::Failed    => 'Échec',
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
