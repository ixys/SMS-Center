<?php

namespace App\Models;

use App\Enums\MessageStatus;
use App\Models\Base\Message as BaseMessage;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Message extends BaseMessage
{
    use HasUuids;

    protected $fillable = [
        'conversation_uuid',
        'campaign_uuid',
        'direction',
        'from',
        'to',
        'status',
        'content',
        'sent_at',
        'received_at',
        'failed_at',
    ];

    protected $casts = [
        'status' => MessageStatus::class,
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

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}
