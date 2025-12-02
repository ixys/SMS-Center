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

    protected static ?string $navigationIcon = 'heroicon-o-signal';
    protected static ?string $navigationGroup = 'Messaging';
    protected static ?string $modelLabel = 'Compte Plateforme';
    protected static ?string $pluralModelLabel = 'Comptes Plateforme';

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Informations générales')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                                                  ->label('Nom du compte')
                                                                  ->required(),

                                        Forms\Components\Select::make('platform_id')
                                                               ->relationship('platform', 'name')
                                                               ->label('Plateforme')
                                                               ->required()
                                                               ->preload(),

                                        Forms\Components\KeyValue::make('credentials')
                                                                 ->label('Credentials')
                                                                 ->nullable()
                                                                 ->addButtonLabel('Ajouter'),

                                        Forms\Components\KeyValue::make('settings')
                                                                 ->label('Paramètres additionnels')
                                                                 ->nullable()
                                                                 ->addButtonLabel('Ajouter'),
                                    ])
                                    ->columns(2),

            Forms\Components\Section::make('Paramètres SMPP')
                                    ->description('Configuration spécifique aux cartes SIM et au routage SMPP')
                                    ->schema([

                                        Forms\Components\TextInput::make('smpp_phone_number')
                                                                  ->label('Numéro SMPP (E.164)')
                                                                  ->placeholder('+33612345678')
                                                                  ->required(),

                                        Forms\Components\TextInput::make('smpp_sender_id')
                                                                  ->label('Sender ID (alphanum)')
                                                                  ->placeholder('GOIP1'),

                                        Forms\Components\TextInput::make('smpp_sim_slot')
                                                                  ->label('Slot SIM (GoIP)')
                                                                  ->numeric()
                                                                  ->placeholder('1'),
                                    ])
                                    ->columns(2),

            Forms\Components\Section::make('Statut')
                                    ->schema([
                                        Forms\Components\Toggle::make('is_active')
                                                               ->label('Actif')
                                                               ->default(true),
                                    ])
                                    ->columns(1),
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

                Tables\Columns\TextColumn::make('platform.name')
                                         ->label('Plateforme')
                                         ->sortable(),

                Tables\Columns\TextColumn::make('smpp_phone_number')
                                         ->label('Tel SMPP')
                                         ->sortable(),

                Tables\Columns\TextColumn::make('smpp_sender_id')
                                         ->label('Sender ID')
                                         ->sortable(),

                Tables\Columns\TextColumn::make('smpp_sim_slot')
                                         ->label('Slot')
                                         ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                                         ->label('Actif')
                                         ->boolean(),
            ])

            ->filters([
                Tables\Filters\SelectFilter::make('smpp_sim_slot')
                                           ->label('Slot')
                                           ->options([
                                               1 => 'Slot 1',
                                               2 => 'Slot 2',
                                               3 => 'Slot 3',
                                               4 => 'Slot 4',
                                           ]),

                Tables\Filters\TernaryFilter::make('is_active')
                                            ->label('Actif'),
            ])

            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])

            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPlatformAccounts::route('/'),
            'create' => Pages\CreatePlatformAccount::route('/create'),
            'edit'   => Pages\EditPlatformAccount::route('/{record}/edit'),
        ];
    }
}
