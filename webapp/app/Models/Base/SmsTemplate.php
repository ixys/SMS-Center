<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Abstracts\Model;
use App\Models\SmsCampaign;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class SmsTemplate
 * 
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $category
 * @property string $body
 * @property array|null $placeholders
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|SmsCampaign[] $sms_campaigns
 *
 * @package App\Models\Base
 */
class SmsTemplate extends Model
{
	protected $table = 'sms_templates';

	protected $casts = [
		'placeholders' => 'json',
		'is_active' => 'bool'
	];

	public function sms_campaigns()
	{
		return $this->hasMany(SmsCampaign::class);
	}
}
