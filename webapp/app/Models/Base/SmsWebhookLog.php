<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Abstracts\Model;
use Carbon\Carbon;

/**
 * Class SmsWebhookLog
 * 
 * @property int $id
 * @property string $direction
 * @property string|null $event
 * @property string|null $url
 * @property array|null $payload
 * @property array|null $headers
 * @property int|null $status_code
 * @property bool $is_processed
 * @property Carbon|null $processed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models\Base
 */
class SmsWebhookLog extends Model
{
	protected $table = 'sms_webhook_logs';

	protected $casts = [
		'payload' => 'json',
		'headers' => 'json',
		'status_code' => 'int',
		'is_processed' => 'bool',
		'processed_at' => 'datetime'
	];
}
