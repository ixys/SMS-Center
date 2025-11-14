<?php

namespace App\Filament\Resources\SmsWebhookLogResource\Pages;

use App\Filament\Resources\SmsWebhookLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSmsWebhookLogs extends ListRecords
{
    protected static string $resource = SmsWebhookLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
