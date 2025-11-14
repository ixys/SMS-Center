<?php

namespace App\Models;

use App\Models\Base\Contact as BaseContact;

class Contact extends BaseContact
{
	protected $fillable = [
		'uuid',
		'name',
		'first_name',
		'last_name',
		'phone_number',
		'international_phone_number',
		'country_code',
		'email',
		'is_active',
		'metadata'
	];
}
