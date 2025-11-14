<?php

namespace App\Models;

use App\Models\Base\GoipOutgoingQueue as BaseGoipOutgoingQueue;

class GoipOutgoingQueue extends BaseGoipOutgoingQueue
{
	protected $fillable = [
		'sms_message_id',
		'sim_card_id',
		'phone_number',
		'body',
		'status',
		'error_message'
	];
}
