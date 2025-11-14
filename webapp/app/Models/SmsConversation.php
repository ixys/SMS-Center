<?php

namespace App\Models;

use App\Models\Base\SmsConversation as BaseSmsConversation;

class SmsConversation extends BaseSmsConversation
{
	protected $fillable = [
		'contact_id',
		'phone_number',
		'sim_card_id',
		'last_message_preview',
		'last_direction',
		'last_message_at',
		'unread_inbound_count',
		'is_archived',
		'is_muted',
		'metadata'
	];
}
