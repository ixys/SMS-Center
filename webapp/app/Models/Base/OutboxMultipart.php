<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Abstracts\Model;

/**
 * Class OutboxMultipart
 * 
 * @property string|null $Text
 * @property string $Coding
 * @property string|null $UDH
 * @property int $Class
 * @property string|null $TextDecoded
 * @property int $ID
 * @property int $SequencePosition
 * @property string $Status
 * @property int $StatusCode
 *
 * @package App\Models\Base
 */
class OutboxMultipart extends Model
{
	protected $table = 'outbox_multipart';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'Class' => 'int',
		'ID' => 'int',
		'SequencePosition' => 'int',
		'StatusCode' => 'int'
	];
}
