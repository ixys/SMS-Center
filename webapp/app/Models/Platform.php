<?php

namespace App\Models;

use App\Models\Base\Platform as BasePlatform;

class Platform extends BasePlatform
{
	protected $fillable = [
		'name',
		'code',
		'driver_class',
		'default_settings',
		'is_active'
	];

    protected $casts = [
        'default_settings' => 'array',
        'is_active' => 'boolean',
    ];

    public function accounts()
    {
        return $this->hasMany(PlatformAccount::class);
    }

    /**
     * Instancie le driver associé à cette plateforme.
     * // Permet d’appeler dynamiquement la logique métier (API externes, webhooks, etc.)
     */
    public function driver(): object
    {
        $class = $this->driver_class;

        return new $class($this);
    }
}
