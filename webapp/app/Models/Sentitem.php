<?php

namespace App\Models;

use App\Models\Base\Sentitem as BaseSentitem;

class Sentitem extends BaseSentitem
{
	protected $fillable = [
		'UpdatedInDB',
		'InsertIntoDB',
		'SendingDateTime',
		'DeliveryDateTime',
		'Text',
		'DestinationNumber',
		'Coding',
		'UDH',
		'SMSCNumber',
		'Class',
		'TextDecoded',
		'SenderID',
		'Status',
		'StatusError',
		'TPMR',
		'RelativeValidity',
		'CreatorID',
		'StatusCode'
	];
}
