<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Abstracts\Model;
use App\Models\Contact;
use App\Models\SmsCampaign;
use App\Models\SmsMessage;
use Carbon\Carbon;

/**
 * Class SmsCampaignMessage
 * 
 * @property int $id
 * @property int $sms_campaign_id
 * @property int|null $contact_id
 * @property int|null $sms_message_id
 * @property string $phone_number
 * @property string $status
 * @property Carbon|null $scheduled_at
 * @property Carbon|null $sent_at
 * @property Carbon|null $delivered_at
 * @property Carbon|null $failed_at
 * @property string|null $error_code
 * @property string|null $error_message
 * @property string|null $rendered_body
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Contact|null $contact
 * @property SmsCampaign $sms_campaign
 * @property SmsMessage|null $sms_message
 *
 * @package App\Models\Base
 */
class SmsCampaignMessage extends Model
{
	protected $table = 'sms_campaign_messages';

	protected $casts = [
		'sms_campaign_id' => 'int',
		'contact_id' => 'int',
		'sms_message_id' => 'int',
		'scheduled_at' => 'datetime',
		'sent_at' => 'datetime',
		'delivered_at' => 'datetime',
		'failed_at' => 'datetime'
	];

	public function contact()
	{
		return $this->belongsTo(Contact::class);
	}

	public function sms_campaign()
	{
		return $this->belongsTo(SmsCampaign::class);
	}

	public function sms_message()
	{
		return $this->belongsTo(SmsMessage::class);
	}
}
