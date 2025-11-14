<?php

namespace App\Filament\Resources\SmsSentItemResource\Pages;

use App\Filament\Resources\SmsSentItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSmsSentItem extends EditRecord
{
    protected static string $resource = SmsSentItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
