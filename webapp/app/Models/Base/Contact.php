<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Abstracts\Model;
use App\Models\ContactGroupContact;
use App\Models\SmsCampaignMessage;
use App\Models\SmsConversation;
use App\Models\SmsMessage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class Contact
 * 
 * @property int $id
 * @property string $uuid
 * @property string|null $name
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string $phone_number
 * @property string|null $international_phone_number
 * @property string|null $country_code
 * @property string|null $email
 * @property bool $is_active
 * @property array|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|ContactGroupContact[] $contact_group_contacts
 * @property Collection|SmsCampaignMessage[] $sms_campaign_messages
 * @property Collection|SmsConversation[] $sms_conversations
 * @property Collection|SmsMessage[] $sms_messages
 *
 * @package App\Models\Base
 */
class Contact extends Model
{
	protected $table = 'contacts';

	protected $casts = [
		'is_active' => 'bool',
		'metadata' => 'json'
	];

	public function contact_group_contacts()
	{
		return $this->hasMany(ContactGroupContact::class);
	}

	public function sms_campaign_messages()
	{
		return $this->hasMany(SmsCampaignMessage::class);
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
