<?php

namespace App\Filament\Resources\SmsSentItemResource\Pages;

use App\Filament\Resources\SmsSentItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSmsSentItems extends ListRecords
{
    protected static string $resource = SmsSentItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
