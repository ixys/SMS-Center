<?php

namespace App\Filament\Resources\SmsWebhookLogResource\Pages;

use App\Filament\Resources\SmsWebhookLogResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSmsWebhookLog extends CreateRecord
{
    protected static string $resource = SmsWebhookLogResource::class;
}
