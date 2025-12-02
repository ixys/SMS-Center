<?php

namespace App\Models;

use App\Models\Base\ContactGroup as BaseContactGroup;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ContactGroup extends BaseContactGroup
{
    use HasUuids;

	protected $fillable = [
		'uuid',
		'name',
		'description',
		'color',
		'is_system'
	];
}
