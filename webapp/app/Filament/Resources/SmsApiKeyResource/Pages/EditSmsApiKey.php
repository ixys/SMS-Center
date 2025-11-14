<?php

namespace App\Filament\Resources\SmsApiKeyResource\Pages;

use App\Filament\Resources\SmsApiKeyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSmsApiKey extends EditRecord
{
    protected static string $resource = SmsApiKeyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
