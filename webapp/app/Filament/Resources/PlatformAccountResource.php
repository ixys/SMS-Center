<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlatformAccountResource\Pages;
use App\Models\PlatformAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PlatformAccountResource extends Resource
{
    protected static ?string $model = PlatformAccount::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationGroup = 'Messaging';
    protected static ?string $navigationLabel = 'Comptes plateformes';
    protected static ?string $pluralLabel = 'Comptes plateformes';
    protected static ?string $modelLabel = 'Compte plateforme';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('platform_id')
                                       ->label('Plateforme')
                                       ->relationship('platform', 'name')
                                       ->required()
                                       ->searchable(),

                Forms\Components\TextInput::make('name')
                                          ->label('Nom interne')
                                          ->helperText('Ex : Compte OF principal, Numéro SMS prod...')
                                          ->required()
                                          ->maxLength(255),

                Forms\Components\TextInput::make('external_username')
                                          ->label('Identifiant externe')
                                          ->helperText('Handle / pseudo sur la plateforme')
                                          ->maxLength(255),

                Forms\Components\TextInput::make('external_id')
                                          ->label('ID externe')
                                          ->helperText('ID compte côté plateforme (optionnel)')
                                          ->maxLength(255),

                Forms\Components\KeyValue::make('credentials')
                                         ->label('Identifiants / credentials')
                                         ->helperText('API keys, tokens... idéalement chiffrés via cast.'),

                Forms\Components\KeyValue::make('settings')
                                         ->label('Paramètres spécifiques')
                                         ->helperText('Ex : default_number pour SMS, options OnlyFans, etc.'),

                Forms\Components\Toggle::make('is_active')
                                       ->label('Actif')
                                       ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('platform.name')
                                         ->label('Plateforme')
                                         ->sortable()
                                         ->searchable(),

                Tables\Columns\TextColumn::make('name')
                                         ->label('Nom')
                                         ->searchable()
                                         ->sortable(),

                Tables\Columns\TextColumn::make('external_username')
                                         ->label('Identifiant externe')
                                         ->searchable(),

                Tables\Columns\IconColumn::make('is_active')
                                         ->label('Actif')
                                         ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                                         ->label('Créé le')
                                         ->dateTime()
                                         ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('platform_id')
                                           ->label('Plateforme')
                                           ->relationship('platform', 'name'),
                Tables\Filters\TernaryFilter::make('is_active')
                                            ->label('Actif'),
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
            // On pourrait ajouter un RelationManager vers Conversations
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlatformAccounts::route('/'),
            'create' => Pages\CreatePlatformAccount::route('/create'),
            'edit' => Pages\EditPlatformAccount::route('/{record}/edit'),
        ];
    }
}
