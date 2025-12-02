<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Abstracts\Model;
use App\Models\Contact;
use App\Models\ContactGroup;
use Carbon\Carbon;

/**
 * Class ContactGroupContact
 * 
 * @property int $id
 * @property string $contact_uuid
 * @property string $contact_group_uuid
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property ContactGroup $contact_group
 * @property Contact $contact
 *
 * @package App\Models\Base
 */
class ContactGroupContact extends Model
{
	protected $table = 'contact_group_contact';

	public function contact_group()
	{
		return $this->belongsTo(ContactGroup::class, 'contact_group_uuid');
	}

	public function contact()
	{
		return $this->belongsTo(Contact::class, 'contact_uuid');
	}
}
