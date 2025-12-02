<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Abstracts\Model;
use App\Models\Contact;
use App\Models\Message;
use App\Models\PlatformAccount;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class Conversation
 * 
 * @property string $uuid
 * @property int $platform_account_id
 * @property string|null $contact_uuid
 * @property string|null $external_conversation_id
 * @property string|null $remote_user_id
 * @property string|null $remote_username
 * @property string|null $title
 * @property string $status
 * @property Carbon|null $last_message_at
 * @property Carbon|null $last_inbound_at
 * @property Carbon|null $last_outbound_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Contact|null $contact
 * @property PlatformAccount $platform_account
 * @property Collection|Message[] $messages
 *
 * @package App\Models\Base
 */
class Conversation extends Model
{
	protected $table = 'conversations';
	protected $primaryKey = 'uuid';
	public $incrementing = false;

	protected $casts = [
		'platform_account_id' => 'int',
		'last_message_at' => 'datetime',
		'last_inbound_at' => 'datetime',
		'last_outbound_at' => 'datetime'
	];

	public function contact()
	{
		return $this->belongsTo(Contact::class, 'contact_uuid');
	}

	public function platform_account()
	{
		return $this->belongsTo(PlatformAccount::class);
	}

	public function messages()
	{
		return $this->hasMany(Message::class, 'conversation_uuid');
	}
}
