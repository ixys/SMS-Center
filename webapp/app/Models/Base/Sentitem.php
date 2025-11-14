<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Abstracts\Model;
use Carbon\Carbon;

/**
 * Class Sentitem
 * 
 * @property Carbon $UpdatedInDB
 * @property Carbon $InsertIntoDB
 * @property Carbon $SendingDateTime
 * @property Carbon|null $DeliveryDateTime
 * @property string $Text
 * @property string $DestinationNumber
 * @property string $Coding
 * @property string $UDH
 * @property string $SMSCNumber
 * @property int $Class
 * @property string $TextDecoded
 * @property int $ID
 * @property string $SenderID
 * @property int $SequencePosition
 * @property string $Status
 * @property int $StatusError
 * @property int $TPMR
 * @property int $RelativeValidity
 * @property string $CreatorID
 * @property int $StatusCode
 *
 * @package App\Models\Base
 */
class Sentitem extends Model
{
	protected $table = 'sentitems';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'UpdatedInDB' => 'datetime',
		'InsertIntoDB' => 'datetime',
		'SendingDateTime' => 'datetime',
		'DeliveryDateTime' => 'datetime',
		'Class' => 'int',
		'ID' => 'int',
		'SequencePosition' => 'int',
		'StatusError' => 'int',
		'TPMR' => 'int',
		'RelativeValidity' => 'int',
		'StatusCode' => 'int'
	];
}
