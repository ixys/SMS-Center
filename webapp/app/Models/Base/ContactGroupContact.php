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
 * @property int $contact_id
 * @property int $contact_group_id
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

	protected $casts = [
		'contact_id' => 'int',
		'contact_group_id' => 'int'
	];

	public function contact_group()
	{
		return $this->belongsTo(ContactGroup::class);
	}

	public function contact()
	{
		return $this->belongsTo(Contact::class);
	}
}
