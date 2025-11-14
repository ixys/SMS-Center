<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SmsWebhookLogResource\Pages;
use App\Filament\Resources\SmsWebhookLogResource\RelationManagers;
use App\Models\SmsWebhookLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SmsWebhookLogResource extends Resource
{
    protected static ?string $model = SmsWebhookLog::class;

    protected static ?string $navigationIcon  = 'heroicon-o-cloud-arrow-down';
    protected static ?string $navigationLabel = 'Webhooks';
    protected static ?string $pluralLabel     = 'Webhooks';
    protected static ?string $navigationGroup = 'SMS Center';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('direction')
                    ->label('Direction')
                    ->disabled(),

                Forms\Components\TextInput::make('event')
                    ->label('Événement')
                    ->disabled(),

                Forms\Components\TextInput::make('url')
                    ->label('URL')
                    ->disabled(),

                Forms\Components\Textarea::make('payload')
                    ->label('Payload')
                    ->rows(8)
                    ->disabled(),

                Forms\Components\Textarea::make('headers')
                    ->label('Headers')
                    ->rows(5)
                    ->disabled(),

                Forms\Components\TextInput::make('status_code')
                    ->label('Code HTTP')
                    ->disabled(),

                Forms\Components\Toggle::make('is_processed')
                    ->label('Traité')
                    ->disabled(),

                Forms\Components\DateTimePicker::make('processed_at')
                    ->label('Traité le')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('direction')
                    ->label('Dir.')
                    ->badge(),

                Tables\Columns\TextColumn::make('event')
                    ->label('Événement')
                    ->searchable(),

                Tables\Columns\TextColumn::make('url')
                    ->label('URL')
                    ->limit(40),

                Tables\Columns\TextColumn::make('status_code')
                    ->label('HTTP'),

                Tables\Columns\IconColumn::make('is_processed')
                    ->label('Traité')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Reçu le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('direction')
                    ->label('Direction')
                    ->options([
                        'incoming' => 'Incoming',
                        'outgoing' => 'Outgoing',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSmsWebhookLogs::route('/'),
            //'view'  => Pages\ViewSmsWebhookLog::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }
}
