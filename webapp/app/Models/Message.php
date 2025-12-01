<?php

namespace App\Models;

use App\Models\Base\Message as BaseMessage;

class Message extends BaseMessage
{
	protected $fillable = [
		'conversation_id',
		'platform_account_id',
		'external_message_id',
		'direction',
		'from',
		'to',
		'content',
		'attachments',
		'status',
		'sent_at'
	];

    protected $casts = [
        'attachments' => 'array',
        'sent_at' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function platformAccount()
    {
        return $this->belongsTo(PlatformAccount::class);
    }
}
