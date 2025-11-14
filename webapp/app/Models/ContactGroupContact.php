<?php

namespace App\Models;

use App\Models\Base\ContactGroupContact as BaseContactGroupContact;

class ContactGroupContact extends BaseContactGroupContact
{
	protected $fillable = [
		'contact_id',
		'contact_group_id'
	];
}
