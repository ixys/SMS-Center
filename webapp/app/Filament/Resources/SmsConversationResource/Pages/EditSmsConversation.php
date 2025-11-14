<?php

namespace App\Filament\Resources\SmsConversationResource\Pages;

use App\Filament\Resources\SmsConversationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSmsConversation extends EditRecord
{
    protected static string $resource = SmsConversationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
