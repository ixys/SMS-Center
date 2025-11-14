<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Abstracts\Model;
use App\Models\SmsTaggable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class SmsTag
 * 
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $color
 * @property bool $is_system
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|SmsTaggable[] $sms_taggables
 *
 * @package App\Models\Base
 */
class SmsTag extends Model
{
	protected $table = 'sms_tags';

	protected $casts = [
		'is_system' => 'bool'
	];

	public function sms_taggables()
	{
		return $this->hasMany(SmsTaggable::class);
	}
}
