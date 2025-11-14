<?php

namespace App\Models;

use App\Models\Base\Phone as BasePhone;

class Phone extends BasePhone
{
	protected $fillable = [
		'ID',
		'UpdatedInDB',
		'InsertIntoDB',
		'TimeOut',
		'Send',
		'Receive',
		'IMSI',
		'NetCode',
		'NetName',
		'Client',
		'Battery',
		'Signal',
		'Sent',
		'Received'
	];
}
