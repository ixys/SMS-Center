<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Abstracts\Model;
use Carbon\Carbon;

/**
 * Class Phone
 * 
 * @property string $ID
 * @property Carbon $UpdatedInDB
 * @property Carbon $InsertIntoDB
 * @property Carbon $TimeOut
 * @property string $Send
 * @property string $Receive
 * @property string $IMEI
 * @property string $IMSI
 * @property string $NetCode
 * @property string $NetName
 * @property string $Client
 * @property int $Battery
 * @property int $Signal
 * @property int $Sent
 * @property int $Received
 *
 * @package App\Models\Base
 */
class Phone extends Model
{
	protected $table = 'phones';
	protected $primaryKey = 'IMEI';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'UpdatedInDB' => 'datetime',
		'InsertIntoDB' => 'datetime',
		'TimeOut' => 'datetime',
		'Battery' => 'int',
		'Signal' => 'int',
		'Sent' => 'int',
		'Received' => 'int'
	];
}
