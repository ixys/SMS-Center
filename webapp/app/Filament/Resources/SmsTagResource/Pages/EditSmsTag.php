<?php

namespace App\Filament\Resources\SmsTagResource\Pages;

use App\Filament\Resources\SmsTagResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSmsTag extends EditRecord
{
    protected static string $resource = SmsTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
