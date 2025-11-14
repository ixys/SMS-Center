<?php

namespace App\Filament\Resources\SmsWebhookLogResource\Pages;

use App\Filament\Resources\SmsWebhookLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSmsWebhookLog extends EditRecord
{
    protected static string $resource = SmsWebhookLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
