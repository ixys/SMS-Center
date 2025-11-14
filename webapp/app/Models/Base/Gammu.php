<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Abstracts\Model;

/**
 * Class Gammu
 * 
 * @property int $Version
 *
 * @package App\Models\Base
 */
class Gammu extends Model
{
	protected $table = 'gammu';
	protected $primaryKey = 'Version';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'Version' => 'int'
	];
}
