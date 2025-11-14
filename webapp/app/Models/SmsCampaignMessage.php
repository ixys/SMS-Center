<?php

namespace App\Models;

use App\Models\Base\SmsCampaignMessage as BaseSmsCampaignMessage;

class SmsCampaignMessage extends BaseSmsCampaignMessage
{
	protected $fillable = [
		'sms_campaign_id',
		'contact_id',
		'sms_message_id',
		'phone_number',
		'status',
		'scheduled_at',
		'sent_at',
		'delivered_at',
		'failed_at',
		'error_code',
		'error_message',
		'rendered_body'
	];
}
