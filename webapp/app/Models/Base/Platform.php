<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Abstracts\Model;
use App\Models\PlatformAccount;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class Platform
 * 
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $driver_class
 * @property array|null $default_settings
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|PlatformAccount[] $platform_accounts
 *
 * @package App\Models\Base
 */
class Platform extends Model
{
	protected $table = 'platforms';

	protected $casts = [
		'default_settings' => 'json',
		'is_active' => 'bool'
	];

	public function platform_accounts()
	{
		return $this->hasMany(PlatformAccount::class);
	}
}
