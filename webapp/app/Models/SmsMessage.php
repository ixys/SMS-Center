<?php

namespace App\Models;

use App\Models\Base\SmsMessage as BaseSmsMessage;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsMessage extends BaseSmsMessage
{
	protected $fillable = [
		'sms_conversation_id',
		'contact_id',
		'sim_card_id',
		'phone_number',
		'direction',
		'status',
		'body',
		'charset',
		'media_urls',
		'provider_message_id',
		'gammu_inbox_id',
		'gammu_outbox_id',
		'gammu_sentitems_id',
		'scheduled_at',
		'sent_at',
		'delivered_at',
		'failed_at',
		'read_at',
		'error_code',
		'error_message',
		'metadata'
	];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(SmsConversation::class, 'sms_conversation_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function simCard(): BelongsTo
    {
        return $this->belongsTo(SimCard::class);
    }
}
