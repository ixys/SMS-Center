<?php

namespace App\Models;

use App\Models\Base\SmsWebhookLog as BaseSmsWebhookLog;

class SmsWebhookLog extends BaseSmsWebhookLog
{
	protected $fillable = [
		'direction',
		'event',
		'url',
		'payload',
		'headers',
		'status_code',
		'is_processed',
		'processed_at'
	];
}
