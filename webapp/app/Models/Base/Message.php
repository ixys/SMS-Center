<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Abstracts\Model;
use App\Models\Conversation;
use App\Models\PlatformAccount;
use Carbon\Carbon;

/**
 * Class Message
 * 
 * @property int $id
 * @property int $conversation_id
 * @property int $platform_account_id
 * @property string|null $external_message_id
 * @property string $direction
 * @property string|null $from
 * @property string|null $to
 * @property string|null $content
 * @property array|null $attachments
 * @property string $status
 * @property Carbon|null $sent_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Conversation $conversation
 * @property PlatformAccount $platform_account
 *
 * @package App\Models\Base
 */
class Message extends Model
{
	protected $table = 'messages';

	protected $casts = [
		'conversation_id' => 'int',
		'platform_account_id' => 'int',
		'attachments' => 'json',
		'sent_at' => 'datetime'
	];

	public function conversation()
	{
		return $this->belongsTo(Conversation::class);
	}

	public function platform_account()
	{
		return $this->belongsTo(PlatformAccount::class);
	}
}
