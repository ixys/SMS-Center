<?php

namespace App\Models;

use App\Models\Base\Contact as BaseContact;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Contact extends BaseContact
{
    use HasUuids;

	protected $fillable = [
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
