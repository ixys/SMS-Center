<?php

namespace App\Models;

use App\Models\Base\SmsCampaign as BaseSmsCampaign;

class SmsCampaign extends BaseSmsCampaign
{
	protected $fillable = [
		'name',
		'description',
		'type',
		'status',
		'sms_template_id',
		'sim_card_id',
		'scheduled_at',
		'started_at',
		'finished_at',
		'total_recipients',
		'total_sent',
		'total_delivered',
		'total_failed',
		'metadata'
	];
}
