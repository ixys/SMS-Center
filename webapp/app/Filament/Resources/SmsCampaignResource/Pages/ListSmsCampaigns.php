<?php

namespace App\Filament\Resources\SmsCampaignResource\Pages;

use App\Filament\Resources\SmsCampaignResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSmsCampaigns extends ListRecords
{
    protected static string $resource = SmsCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
