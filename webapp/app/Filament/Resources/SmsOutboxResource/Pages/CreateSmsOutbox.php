<?php

namespace App\Filament\Resources\SmsOutboxResource\Pages;

use App\Filament\Resources\SmsOutboxResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSmsOutbox extends CreateRecord
{
    protected static string $resource = SmsOutboxResource::class;
}
