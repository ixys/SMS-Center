<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Abstracts\Model;
use App\Models\SmsTag;
use Carbon\Carbon;

/**
 * Class SmsTaggable
 * 
 * @property int $id
 * @property int $sms_tag_id
 * @property string $taggable_type
 * @property int $taggable_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property SmsTag $sms_tag
 *
 * @package App\Models\Base
 */
class SmsTaggable extends Model
{
	protected $table = 'sms_taggables';

	protected $casts = [
		'sms_tag_id' => 'int',
		'taggable_id' => 'int'
	];

	public function sms_tag()
	{
		return $this->belongsTo(SmsTag::class);
	}
}
