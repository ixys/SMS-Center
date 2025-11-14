<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Abstracts\Model;
use App\Models\SimCard;
use App\Models\SmsMessage;
use Carbon\Carbon;

/**
 * Class GoipOutgoingQueue
 * 
 * @property int $id
 * @property int $sms_message_id
 * @property int|null $sim_card_id
 * @property string $phone_number
 * @property string $body
 * @property string $status
 * @property string|null $error_message
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property SimCard|null $sim_card
 * @property SmsMessage $sms_message
 *
 * @package App\Models\Base
 */
class GoipOutgoingQueue extends Model
{
	protected $table = 'goip_outgoing_queue';

	protected $casts = [
		'sms_message_id' => 'int',
		'sim_card_id' => 'int'
	];

	public function sim_card()
	{
		return $this->belongsTo(SimCard::class);
	}

	public function sms_message()
	{
		return $this->belongsTo(SmsMessage::class);
	}
}
