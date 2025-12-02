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
     * Instancie le driver associé à cette plateforme (SmsDriver, OnlyFansDriver, etc.)
     */
    public function makeDriver()
    {
        if (! $this->driver_class) {
            throw new \RuntimeException("Platform #{$this->id} ({$this->code}) n’a pas de driver_class configuré.");
        }

        return app($this->driver_class);
    }
}
