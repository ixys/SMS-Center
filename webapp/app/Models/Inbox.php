<?php

namespace App\Models;

use App\Models\Base\Inbox as BaseInbox;

class Inbox extends BaseInbox
{
	protected $fillable = [
		'UpdatedInDB',
		'ReceivingDateTime',
		'Text',
		'SenderNumber',
		'Coding',
		'UDH',
		'SMSCNumber',
		'Class',
		'TextDecoded',
		'RecipientID',
		'Processed',
		'Status'
	];
}
