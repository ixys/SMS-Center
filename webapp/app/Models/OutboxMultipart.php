<?php

namespace App\Models;

use App\Models\Base\OutboxMultipart as BaseOutboxMultipart;

class OutboxMultipart extends BaseOutboxMultipart
{
	protected $fillable = [
		'Text',
		'Coding',
		'UDH',
		'Class',
		'TextDecoded',
		'Status',
		'StatusCode'
	];
}
