<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Abstracts\Model;
use Carbon\Carbon;

/**
 * Class Inbox
 * 
 * @property Carbon $UpdatedInDB
 * @property Carbon $ReceivingDateTime
 * @property string $Text
 * @property string $SenderNumber
 * @property string $Coding
 * @property string $UDH
 * @property string $SMSCNumber
 * @property int $Class
 * @property string $TextDecoded
 * @property int $ID
 * @property string $RecipientID
 * @property string $Processed
 * @property int $Status
 *
 * @package App\Models\Base
 */
class Inbox extends Model
{
	protected $table = 'inbox';
	protected $primaryKey = 'ID';
	public $timestamps = false;

	protected $casts = [
		'UpdatedInDB' => 'datetime',
		'ReceivingDateTime' => 'datetime',
		'Class' => 'int',
		'Status' => 'int'
	];
}
