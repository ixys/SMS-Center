<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Abstracts\Model;
use App\Models\GoipOutgoingQueue;
use App\Models\SmsCampaign;
use App\Models\SmsConversation;
use App\Models\SmsMessage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class SimCard
 * 
 * @property int $id
 * @property string $name
 * @property string $sender_id
 * @property string|null $phone_number
 * @property string|null $imei
 * @property string|null $imsi
 * @property bool $is_active
 * @property int $priority
 * @property int|null $daily_quota
 * @property int|null $monthly_quota
 * @property string $strategy
 * @property array|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|GoipOutgoingQueue[] $goip_outgoing_queues
 * @property Collection|SmsCampaign[] $sms_campaigns
 * @property Collection|SmsConversation[] $sms_conversations
 * @property Collection|SmsMessage[] $sms_messages
 *
 * @package App\Models\Base
 */
class SimCard extends Model
{
	protected $table = 'sim_cards';

	protected $casts = [
		'is_active' => 'bool',
		'priority' => 'int',
		'daily_quota' => 'int',
		'monthly_quota' => 'int',
		'metadata' => 'json'
	];

	public function goip_outgoing_queues()
	{
		return $this->hasMany(GoipOutgoingQueue::class);
	}

	public function sms_campaigns()
	{
		return $this->hasMany(SmsCampaign::class);
	}

	public function sms_conversations()
	{
		return $this->hasMany(SmsConversation::class);
	}

	public function sms_messages()
	{
		return $this->hasMany(SmsMessage::class);
	}
}
