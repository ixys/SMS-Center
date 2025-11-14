<?php

namespace App\Models;

use App\Models\Base\SmsTag as BaseSmsTag;

class SmsTag extends BaseSmsTag
{
	protected $fillable = [
		'name',
		'slug',
		'color',
		'is_system'
	];
}
