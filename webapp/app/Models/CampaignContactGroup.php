<?php

namespace App\Models;

use App\Models\Base\CampaignContactGroup as BaseCampaignContactGroup;

class CampaignContactGroup extends BaseCampaignContactGroup
{
	protected $fillable = [
		'campaign_id',
		'contact_group_id'
	];
}
