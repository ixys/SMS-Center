<?php

namespace App\Models;

use App\Models\Base\PlatformAccount as BasePlatformAccount;

class PlatformAccount extends BasePlatformAccount
{
	protected $fillable = [
		'platform_id',
		'name',
		'external_username',
		'external_id',
		'credentials',
		'settings',
		'is_active',
        'smpp_phone_number',
        'smpp_sender_id',
        'smpp_sim_slot'
	];

    protected $casts = [
        'credentials' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Instancie le driver associé à ce compte
     * (en passant par la Platform et son driver_class).
     */
    public function driver()
    {
        // On force le chargement de la relation si besoin
        $platform = $this->platform ?? $this->platform()->first();

        if (! $platform) {
            throw new \RuntimeException("PlatformAccount #{$this->id} n’a pas de platform associée (platform_id manquant).");
        }

        if (! $platform->driver_class) {
            throw new \RuntimeException("Platform #{$platform->id} ({$platform->code}) n’a pas de driver_class configuré.");
        }

        return app($platform->driver_class);
    }
}
