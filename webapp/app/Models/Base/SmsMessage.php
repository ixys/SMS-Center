<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Abstracts\Model;
use App\Models\Contact;
use App\Models\GoipOutgoingQueue;
use App\Models\SimCard;
use App\Models\SmsCampaignMessage;
use App\Models\SmsConversation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class SmsMessage
 *
 * @property int $id
 * @property int|null $sms_conversation_id
 * @property int|null $contact_id
 * @property int|null $sim_card_id
 * @property string $phone_number
 * @property string $direction
 * @property string $status
 * @property string $body
 * @property string|null $charset
 * @property array|null $media_urls
 * @property string|null $provider_message_id
 * @property int|null $gammu_inbox_id
 * @property int|null $gammu_outbox_id
 * @property int|null $gammu_sentitems_id
 * @property Carbon|null $scheduled_at
 * @property Carbon|null $sent_at
 * @property Carbon|null $delivered_at
 * @property Carbon|null $failed_at
 * @property Carbon|null $read_at
 * @property string|null $error_code
 * @property string|null $error_message
 * @property array|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Contact|null $contact
 * @property SimCard|null $sim_card
 * @property SmsConversation|null $sms_conversation
 * @property Collection|GoipOutgoingQueue[] $goip_outgoing_queues
 * @property Collection|SmsCampaignMessage[] $sms_campaign_messages
 *
 * @package App\Models\Base
 */
class SmsMessage extends Model
{
	protected $table = 'sms_messages';

	protected $casts = [
		'sms_conversation_id' => 'int',
		'contact_id' => 'int',
		'sim_card_id' => 'int',
		'media_urls' => 'json',
		'gammu_inbox_id' => 'int',
		'gammu_outbox_id' => 'int',
		'gammu_sentitems_id' => 'int',
		'scheduled_at' => 'datetime',
		'sent_at' => 'datetime',
		'delivered_at' => 'datetime',
		'failed_at' => 'datetime',
		'read_at' => 'datetime',
		'metadata' => 'json'
	];

	public function contact()
	{
		return $this->belongsTo(Contact::class);
	}

	public function sim_card()
	{
		return $this->belongsTo(SimCard::class);
	}

	public function sms_conversation()
	{
		return $this->belongsTo(SmsConversation::class);
	}

	public function goip_outgoing_queues()
	{
		return $this->hasMany(GoipOutgoingQueue::class);
	}

	public function sms_campaign_messages()
	{
		return $this->hasMany(SmsCampaignMessage::class);
	}
}
