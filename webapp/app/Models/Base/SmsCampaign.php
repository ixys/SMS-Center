<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Abstracts\Model;
use App\Models\SimCard;
use App\Models\SmsCampaignMessage;
use App\Models\SmsTemplate;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class SmsCampaign
 * 
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string $type
 * @property string $status
 * @property int|null $sms_template_id
 * @property int|null $sim_card_id
 * @property Carbon|null $scheduled_at
 * @property Carbon|null $started_at
 * @property Carbon|null $finished_at
 * @property int $total_recipients
 * @property int $total_sent
 * @property int $total_delivered
 * @property int $total_failed
 * @property array|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property SimCard|null $sim_card
 * @property SmsTemplate|null $sms_template
 * @property Collection|SmsCampaignMessage[] $sms_campaign_messages
 *
 * @package App\Models\Base
 */
class SmsCampaign extends Model
{
	protected $table = 'sms_campaigns';

	protected $casts = [
		'sms_template_id' => 'int',
		'sim_card_id' => 'int',
		'scheduled_at' => 'datetime',
		'started_at' => 'datetime',
		'finished_at' => 'datetime',
		'total_recipients' => 'int',
		'total_sent' => 'int',
		'total_delivered' => 'int',
		'total_failed' => 'int',
		'metadata' => 'json'
	];

	public function sim_card()
	{
		return $this->belongsTo(SimCard::class);
	}

	public function sms_template()
	{
		return $this->belongsTo(SmsTemplate::class);
	}

	public function sms_campaign_messages()
	{
		return $this->hasMany(SmsCampaignMessage::class);
	}
}
