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
		'is_active'
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

    public function driver(): object
    {
        return $this->platform->driver();
    }
}
