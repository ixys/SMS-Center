<?php

namespace App\Abstracts;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Mail\Mailable;
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
abstract class Mail extends Mailable
{
    /**
     * The data for merging into the email
     */
    public $payload = [];

    public function __construct()
    {
        // set template variables
        $this->payload = [
            'email_signature' => 'email_signature',
            'email_footer'    => 'email_footer',
        ];

        return $this;
    }
}
