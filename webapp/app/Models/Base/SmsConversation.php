<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Abstracts\Model;
use App\Models\Contact;
use App\Models\SimCard;
use App\Models\SmsMessage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class SmsConversation
 * 
 * @property int $id
 * @property int|null $contact_id
 * @property string $phone_number
 * @property int|null $sim_card_id
 * @property string|null $last_message_preview
 * @property string|null $last_direction
 * @property Carbon|null $last_message_at
 * @property int $unread_inbound_count
 * @property bool $is_archived
 * @property bool $is_muted
 * @property array|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Contact|null $contact
 * @property SimCard|null $sim_card
 * @property Collection|SmsMessage[] $sms_messages
 *
 * @package App\Models\Base
 */
class SmsConversation extends Model
{
	protected $table = 'sms_conversations';

	protected $casts = [
		'contact_id' => 'int',
		'sim_card_id' => 'int',
		'last_message_at' => 'datetime',
		'unread_inbound_count' => 'int',
		'is_archived' => 'bool',
		'is_muted' => 'bool',
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

	public function sms_messages()
	{
		return $this->hasMany(SmsMessage::class);
	}
}
