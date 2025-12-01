<?php

namespace App\Models;

use App\Models\Base\Conversation as BaseConversation;

class Conversation extends BaseConversation
{
	protected $fillable = [
		'platform_account_id',
		'external_conversation_id',
		'remote_user_id',
		'remote_username',
		'title',
		'status',
		'last_message_at',
		'last_inbound_at',
		'last_outbound_at'
	];

    protected $casts = [
        'last_message_at' => 'datetime',
        'last_inbound_at' => 'datetime',
        'last_outbound_at' => 'datetime',
    ];

    public function platformAccount()
    {
        return $this->belongsTo(PlatformAccount::class);
    }

    public function platform()
    {
        return $this->platformAccount->platform();
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->latest('sent_at');
    }
}
