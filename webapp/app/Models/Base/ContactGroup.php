<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Abstracts\Model;
use App\Models\Contact;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class ContactGroup
 * 
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string|null $description
 * @property string|null $color
 * @property bool $is_system
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Contact[] $contacts
 *
 * @package App\Models\Base
 */
class ContactGroup extends Model
{
	protected $table = 'contact_groups';

	protected $casts = [
		'is_system' => 'bool'
	];

	public function contacts()
	{
		return $this->belongsToMany(Contact::class, 'contact_group_contact')
					->withPivot('id')
					->withTimestamps();
	}
}
