<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlatformResource\Pages;
use App\Models\Platform;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PlatformResource extends Resource
{
    protected static ?string $model = Platform::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Messaging';
    protected static ?string $navigationLabel = 'Plateformes';
    protected static ?string $pluralLabel = 'Plateformes';
    protected static ?string $modelLabel = 'Plateforme';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                                          ->label('Nom')
                                          ->required()
                                          ->maxLength(255),

                Forms\Components\TextInput::make('code')
                                          ->label('Code technique')
                                          ->helperText('Ex : sms, onlyfans, x, mym...')
                                          ->required()
                                          ->unique(ignoreRecord: true)
                                          ->maxLength(50),

                Forms\Components\TextInput::make('driver_class')
                                          ->label('Classe driver')
                                          ->helperText('Ex : App\\Messaging\\Drivers\\SmsDriver')
                                          ->required()
                                          ->maxLength(255),

                Forms\Components\KeyValue::make('default_settings')
                                         ->label('Paramètres par défaut')
                                         ->helperText('Configuration générique, fusionnée avec les paramètres du compte.'),

                Forms\Components\Toggle::make('is_active')
                                       ->label('Active')
                                       ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                                         ->label('Nom')
                                         ->searchable()
                                         ->sortable(),

                Tables\Columns\TextColumn::make('code')
                                         ->label('Code')
                                         ->searchable()
                                         ->sortable(),

                Tables\Columns\TextColumn::make('driver_class')
                                         ->label('Driver')
                                         ->limit(40)
                                         ->tooltip(fn ($record) => $record->driver_class),

                Tables\Columns\IconColumn::make('is_active')
                                         ->label('Active')
                                         ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                                         ->label('Créée le')
                                         ->dateTime()
                                         ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                                            ->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // On pourrait ajouter un RelationManager pour les PlatformAccounts
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlatforms::route('/'),
            'create' => Pages\CreatePlatform::route('/create'),
            'edit' => Pages\EditPlatform::route('/{record}/edit'),
        ];
    }
}
