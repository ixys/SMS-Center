<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Abstracts\Model;
use App\Models\CampaignRecipient;
use App\Models\ContactGroupContact;
use App\Models\Conversation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class Contact
 * 
 * @property string $uuid
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $name
 * @property string $phone_number
 * @property string|null $international_phone_number
 * @property string|null $country_code
 * @property string|null $email
 * @property bool $is_active
 * @property array|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|CampaignRecipient[] $campaign_recipients
 * @property Collection|ContactGroupContact[] $contact_group_contacts
 * @property Collection|Conversation[] $conversations
 *
 * @package App\Models\Base
 */
class Contact extends Model
{
	protected $table = 'contacts';
	protected $primaryKey = 'uuid';
	public $incrementing = false;

	protected $casts = [
		'is_active' => 'bool',
		'metadata' => 'json'
	];

	public function campaign_recipients()
	{
		return $this->hasMany(CampaignRecipient::class, 'contact_uuid');
	}

	public function contact_group_contacts()
	{
		return $this->hasMany(ContactGroupContact::class, 'contact_uuid');
	}

	public function conversations()
	{
		return $this->hasMany(Conversation::class, 'contact_uuid');
	}
}
