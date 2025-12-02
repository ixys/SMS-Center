<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Abstracts\Model;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Conversation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class Message
 * 
 * @property string $uuid
 * @property string $conversation_uuid
 * @property string|null $campaign_uuid
 * @property string|null $external_message_id
 * @property string $direction
 * @property string|null $from
 * @property string|null $to
 * @property string|null $content
 * @property array|null $attachments
 * @property string $status
 * @property Carbon|null $sent_at
 * @property Carbon|null $received_at
 * @property Carbon|null $failed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Campaign|null $campaign
 * @property Conversation $conversation
 * @property Collection|CampaignRecipient[] $campaign_recipients
 *
 * @package App\Models\Base
 */
class Message extends Model
{
	protected $table = 'messages';
	protected $primaryKey = 'uuid';
	public $incrementing = false;

	protected $casts = [
		'attachments' => 'json',
		'sent_at' => 'datetime',
		'received_at' => 'datetime',
		'failed_at' => 'datetime'
	];

	public function campaign()
	{
		return $this->belongsTo(Campaign::class, 'campaign_uuid');
	}

	public function conversation()
	{
		return $this->belongsTo(Conversation::class, 'conversation_uuid');
	}

	public function campaign_recipients()
	{
		return $this->hasMany(CampaignRecipient::class, 'message_uuid');
	}
}
