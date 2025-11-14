<?php

namespace App\Abstracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Utils;

/**
 * Class Model.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Model newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Model newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Model query()
 * @method static \Illuminate\Database\Eloquent\Builder|Model scope(bool $publicId = false, bool $accountId = false)
 * @method static \Illuminate\Database\Eloquent\Builder|Model withActiveOrSelected($id = false)
 * @method static \Illuminate\Database\Eloquent\Builder|Model withArchived()
 * @mixin Eloquent
 */
abstract class Model extends Eloquent
{
    use LogsActivity;

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
                         ->useLogName('system')
                         ->dontSubmitEmptyLogs();
    }
}
