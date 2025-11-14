<?php

namespace App\Filament\Resources\SmsTagResource\Pages;

use App\Filament\Resources\SmsTagResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSmsTags extends ListRecords
{
    protected static string $resource = SmsTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
