<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Abstracts\Model;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Platform;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class PlatformAccount
 * 
 * @property int $id
 * @property int $platform_id
 * @property string $name
 * @property string|null $external_username
 * @property string|null $external_id
 * @property array|null $credentials
 * @property array|null $settings
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Platform $platform
 * @property Collection|Conversation[] $conversations
 * @property Collection|Message[] $messages
 *
 * @package App\Models\Base
 */
class PlatformAccount extends Model
{
	protected $table = 'platform_accounts';

	protected $casts = [
		'platform_id' => 'int',
		'credentials' => 'json',
		'settings' => 'json',
		'is_active' => 'bool'
	];

	public function platform()
	{
		return $this->belongsTo(Platform::class);
	}

	public function conversations()
	{
		return $this->hasMany(Conversation::class);
	}

	public function messages()
	{
		return $this->hasMany(Message::class);
	}
}
