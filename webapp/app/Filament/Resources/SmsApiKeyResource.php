<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SmsApiKeyResource\Pages;
use App\Filament\Resources\SmsApiKeyResource\RelationManagers;
use App\Models\SmsApiKey;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SmsApiKeyResource extends Resource
{
    protected static ?string $model = SmsApiKey::class;

    protected static ?string $navigationIcon  = 'heroicon-o-key';
    protected static ?string $navigationLabel = 'Clés API SMS';
    protected static ?string $pluralLabel     = 'Clés API SMS';
    protected static ?string $navigationGroup = 'SMS Center';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nom')
                    ->required(),

                Forms\Components\TextInput::make('api_key')
                    ->label('API Key')
                    ->required()
                    ->password()
                    ->revealable(),

                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),

                Forms\Components\Textarea::make('allowed_ips')
                    ->label('IPs autorisées (JSON)')
                    ->rows(3),

                Forms\Components\TextInput::make('rate_limit_per_minute')
                    ->label('Rate limit / minute')
                    ->numeric()
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('rate_limit_per_minute')
                    ->label('Rate limit')
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_used_at')
                    ->label('Dernière utilisation')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSmsApiKeys::route('/'),
            'create' => Pages\CreateSmsApiKey::route('/create'),
            'edit'   => Pages\EditSmsApiKey::route('/{record}/edit'),
        ];
    }
}
