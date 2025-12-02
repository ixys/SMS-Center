<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Abstracts\Model;
use App\Models\Campaign;
use App\Models\Contact;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class ContactGroup
 * 
 * @property string $uuid
 * @property string $name
 * @property string|null $description
 * @property string|null $color
 * @property bool $is_system
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Campaign[] $campaigns
 * @property Collection|Contact[] $contacts
 *
 * @package App\Models\Base
 */
class ContactGroup extends Model
{
	protected $table = 'contact_groups';
	protected $primaryKey = 'uuid';
	public $incrementing = false;

	protected $casts = [
		'is_system' => 'bool'
	];

	public function campaigns()
	{
		return $this->belongsToMany(Campaign::class, 'campaign_contact_group', 'contact_group_uuid', 'campaign_uuid')
					->withPivot('id')
					->withTimestamps();
	}

	public function contacts()
	{
		return $this->belongsToMany(Contact::class, 'contact_group_contact', 'contact_group_uuid', 'contact_uuid')
					->withPivot('id')
					->withTimestamps();
	}
}
