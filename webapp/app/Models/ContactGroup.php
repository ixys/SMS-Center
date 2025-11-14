<?php

namespace App\Models;

use App\Models\Base\ContactGroup as BaseContactGroup;

class ContactGroup extends BaseContactGroup
{
	protected $fillable = [
		'uuid',
		'name',
		'description',
		'color',
		'is_system'
	];
}
