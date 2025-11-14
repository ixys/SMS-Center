<?php

namespace App\Models;

use App\Models\Base\SmsTaggable as BaseSmsTaggable;

class SmsTaggable extends BaseSmsTaggable
{
	protected $fillable = [
		'sms_tag_id',
		'taggable_type',
		'taggable_id'
	];
}
