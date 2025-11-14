<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Abstracts\Model;
use Carbon\Carbon;

/**
 * Class Outbox
 * 
 * @property Carbon $UpdatedInDB
 * @property Carbon $InsertIntoDB
 * @property Carbon $SendingDateTime
 * @property Carbon $SendBefore
 * @property Carbon $SendAfter
 * @property string|null $Text
 * @property string $DestinationNumber
 * @property string $Coding
 * @property string|null $UDH
 * @property int $Class
 * @property string $TextDecoded
 * @property int $ID
 * @property string $MultiPart
 * @property int $RelativeValidity
 * @property string|null $SenderID
 * @property Carbon|null $SendingTimeOut
 * @property string $DeliveryReport
 * @property string $CreatorID
 * @property int $Retries
 * @property int $Priority
 * @property string $Status
 * @property int $StatusCode
 *
 * @package App\Models\Base
 */
class Outbox extends Model
{
	protected $table = 'outbox';
	protected $primaryKey = 'ID';
	public $timestamps = false;

	protected $casts = [
		'UpdatedInDB' => 'datetime',
		'InsertIntoDB' => 'datetime',
		'SendingDateTime' => 'datetime',
		'SendBefore' => 'datetime',
		'SendAfter' => 'datetime',
		'Class' => 'int',
		'RelativeValidity' => 'int',
		'SendingTimeOut' => 'datetime',
		'Retries' => 'int',
		'Priority' => 'int',
		'StatusCode' => 'int'
	];
}
