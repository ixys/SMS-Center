<?php

namespace App\Filament\Resources\SmsConversationResource\Pages;

use App\Filament\Resources\SmsConversationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSmsConversations extends ListRecords
{
    protected static string $resource = SmsConversationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
