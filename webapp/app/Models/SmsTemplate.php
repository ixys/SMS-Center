<?php

namespace App\Models;

use App\Models\Base\SmsTemplate as BaseSmsTemplate;

class SmsTemplate extends BaseSmsTemplate
{
	protected $fillable = [
		'name',
		'slug',
		'category',
		'body',
		'placeholders',
		'is_active'
	];
}
