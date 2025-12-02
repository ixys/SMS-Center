<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Abstracts\Model;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\Message;
use Carbon\Carbon;

/**
 * Class CampaignRecipient
 * 
 * @property int $id
 * @property string $campaign_uuid
 * @property string $contact_uuid
 * @property string|null $message_uuid
 * @property string $status
 * @property string|null $last_error
 * @property Carbon|null $sent_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Campaign $campaign
 * @property Contact $contact
 * @property Message|null $message
 *
 * @package App\Models\Base
 */
class CampaignRecipient extends Model
{
	protected $table = 'campaign_recipients';

	protected $casts = [
		'sent_at' => 'datetime'
	];

	public function campaign()
	{
		return $this->belongsTo(Campaign::class, 'campaign_uuid');
	}

	public function contact()
	{
		return $this->belongsTo(Contact::class, 'contact_uuid');
	}

	public function message()
	{
		return $this->belongsTo(Message::class, 'message_uuid');
	}
}
