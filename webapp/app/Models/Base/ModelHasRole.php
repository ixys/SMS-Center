<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Abstracts\Model;
use App\Models\Role;

/**
 * Class ModelHasRole
 * 
 * @property int $role_id
 * @property string $model_type
 * @property int $model_id
 * 
 * @property Role $role
 *
 * @package App\Models\Base
 */
class ModelHasRole extends Model
{
	protected $table = 'model_has_roles';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'role_id' => 'int',
		'model_id' => 'int'
	];

	public function role()
	{
		return $this->belongsTo(Role::class);
	}
}
