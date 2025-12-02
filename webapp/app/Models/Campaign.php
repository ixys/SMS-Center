<?php

namespace App\Models;

use App\Enums\CampaignStatus;
use App\Models\Base\Campaign as BaseCampaign;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Campaign extends BaseCampaign
{
    use HasUuids;

	protected $fillable = [
		'platform_account_id',
		'name',
		'description',
		'content',
		'status',
		'scheduled_at',
		'total_recipients',
		'sent_count',
		'failed_count'
	];

    protected $casts = [
        'status' => CampaignStatus::class,
        'scheduled_at' => 'datetime',
    ];

    public function platformAccount()
    {
        return $this->belongsTo(PlatformAccount::class);
    }

    public function recipients()
    {
        return $this->hasMany(CampaignRecipient::class);
    }

    public function contactGroups()
    {
        return $this->belongsToMany(ContactGroup::class, 'campaign_contact_group');
    }
}
