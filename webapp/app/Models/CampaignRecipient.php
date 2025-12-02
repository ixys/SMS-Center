<?php

namespace App\Models;

use App\Models\Base\CampaignRecipient as BaseCampaignRecipient;

class CampaignRecipient extends BaseCampaignRecipient
{
	protected $fillable = [
		'campaign_id',
		'contact_id',
		'message_id',
		'status',
		'last_error',
		'sent_at'
	];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function message()
    {
        return $this->belongsTo(Message::class);
    }
}
