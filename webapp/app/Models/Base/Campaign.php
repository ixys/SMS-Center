<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Abstracts\Model;
use App\Models\CampaignRecipient;
use App\Models\ContactGroup;
use App\Models\Message;
use App\Models\PlatformAccount;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class Campaign
 * 
 * @property string $uuid
 * @property int $platform_account_id
 * @property string $name
 * @property string|null $description
 * @property string $content
 * @property string $status
 * @property Carbon|null $scheduled_at
 * @property int $total_recipients
 * @property int $sent_count
 * @property int $failed_count
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property PlatformAccount $platform_account
 * @property Collection|ContactGroup[] $contact_groups
 * @property Collection|CampaignRecipient[] $campaign_recipients
 * @property Collection|Message[] $messages
 *
 * @package App\Models\Base
 */
class Campaign extends Model
{
	protected $table = 'campaigns';
	protected $primaryKey = 'uuid';
	public $incrementing = false;

	protected $casts = [
		'platform_account_id' => 'int',
		'scheduled_at' => 'datetime',
		'total_recipients' => 'int',
		'sent_count' => 'int',
		'failed_count' => 'int'
	];

	public function platform_account()
	{
		return $this->belongsTo(PlatformAccount::class);
	}

	public function contact_groups()
	{
		return $this->belongsToMany(ContactGroup::class, 'campaign_contact_group', 'campaign_uuid', 'contact_group_uuid')
					->withPivot('id')
					->withTimestamps();
	}

	public function campaign_recipients()
	{
		return $this->hasMany(CampaignRecipient::class, 'campaign_uuid');
	}

	public function messages()
	{
		return $this->hasMany(Message::class, 'campaign_uuid');
	}
}
