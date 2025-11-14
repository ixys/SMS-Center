<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Abstracts\Model;
use Carbon\Carbon;

/**
 * Class SmsApiKey
 * 
 * @property int $id
 * @property string $name
 * @property string $api_key
 * @property bool $is_active
 * @property array|null $allowed_ips
 * @property int|null $rate_limit_per_minute
 * @property Carbon|null $last_used_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models\Base
 */
class SmsApiKey extends Model
{
	protected $table = 'sms_api_keys';

	protected $casts = [
		'is_active' => 'bool',
		'allowed_ips' => 'json',
		'rate_limit_per_minute' => 'int',
		'last_used_at' => 'datetime'
	];
}
