<?php

namespace App\Models;

use App\Models\Base\SimCard as BaseSimCard;

class SimCard extends BaseSimCard
{
	protected $fillable = [
		'name',
		'sender_id',
		'phone_number',
		'imei',
		'imsi',
		'is_active',
		'priority',
		'daily_quota',
		'monthly_quota',
		'strategy',
		'metadata'
	];
}
