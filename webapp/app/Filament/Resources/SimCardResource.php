<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SimCardResource\Pages;
use App\Filament\Resources\SimCardResource\RelationManagers;
use App\Models\SimCard;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SimCardResource extends Resource
{
    protected static ?string $model = SimCard::class;

    protected static ?string $navigationIcon  = 'heroicon-o-signal';
    protected static ?string $navigationLabel = 'Cartes SIM';
    protected static ?string $pluralLabel     = 'Cartes SIM';
    protected static ?string $navigationGroup = 'SMS Center';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nom')
                    ->required(),

                Forms\Components\TextInput::make('sender_id')
                    ->label('SenderID (Gammu)')
                    ->required()
                    ->helperText('Doit correspondre au SenderID dans la conf Gammu.'),

                Forms\Components\TextInput::make('phone_number')
                    ->label('Numéro de la SIM')
                    ->maxLength(32),

                Forms\Components\TextInput::make('imei')
                    ->label('IMEI')
                    ->maxLength(35),

                Forms\Components\TextInput::make('imsi')
                    ->label('IMSI')
                    ->maxLength(35),

                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),

                Forms\Components\TextInput::make('priority')
                    ->label('Priorité')
                    ->numeric()
                    ->default(0),

                Forms\Components\TextInput::make('daily_quota')
                    ->label('Quota/jour')
                    ->numeric()
                    ->nullable(),

                Forms\Components\TextInput::make('monthly_quota')
                    ->label('Quota/mois')
                    ->numeric()
                    ->nullable(),

                Forms\Components\Select::make('strategy')
                    ->label('Stratégie')
                    ->options([
                        'manual'        => 'Manuelle',
                        'round_robin'   => 'Round robin',
                        'load_balancing'=> 'Répartition charge',
                    ])
                    ->default('manual'),

                Forms\Components\Textarea::make('metadata')
                    ->label('Métadonnées (JSON)')
                    ->rows(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sender_id')
                    ->label('SenderID')
                    ->searchable(),

                Tables\Columns\TextColumn::make('phone_number')
                    ->label('Numéro'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('priority')
                    ->label('Prio')
                    ->sortable(),

                Tables\Columns\TextColumn::make('daily_quota')
                    ->label('Quota/jour')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('monthly_quota')
                    ->label('Quota/mois')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [
            // Plus tard : conversations, messages, campagnes…
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSimCards::route('/'),
            'create' => Pages\CreateSimCard::route('/create'),
            'edit'   => Pages\EditSimCard::route('/{record}/edit'),
        ];
    }
}
