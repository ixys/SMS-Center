<?php

namespace App\Models;

use App\Models\Base\SmsApiKey as BaseSmsApiKey;

class SmsApiKey extends BaseSmsApiKey
{
	protected $fillable = [
		'name',
		'api_key',
		'is_active',
		'allowed_ips',
		'rate_limit_per_minute',
		'last_used_at'
	];
}
