<?php

namespace App\Models;

use App\Models\Base\Outbox as BaseOutbox;

class Outbox extends BaseOutbox
{
	protected $fillable = [
		'UpdatedInDB',
		'InsertIntoDB',
		'SendingDateTime',
		'SendBefore',
		'SendAfter',
		'Text',
		'DestinationNumber',
		'Coding',
		'UDH',
		'Class',
		'TextDecoded',
		'MultiPart',
		'RelativeValidity',
		'SenderID',
		'SendingTimeOut',
		'DeliveryReport',
		'CreatorID',
		'Retries',
		'Priority',
		'Status',
		'StatusCode'
	];
}
