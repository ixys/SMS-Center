<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Abstracts\Model;
use App\Models\Campaign;
use App\Models\ContactGroup;
use Carbon\Carbon;

/**
 * Class CampaignContactGroup
 * 
 * @property int $id
 * @property string $campaign_uuid
 * @property string $contact_group_uuid
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Campaign $campaign
 * @property ContactGroup $contact_group
 *
 * @package App\Models\Base
 */
class CampaignContactGroup extends Model
{
	protected $table = 'campaign_contact_group';

	public function campaign()
	{
		return $this->belongsTo(Campaign::class, 'campaign_uuid');
	}

	public function contact_group()
	{
		return $this->belongsTo(ContactGroup::class, 'contact_group_uuid');
	}
}
