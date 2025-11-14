<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SmsMessageResource\Pages;
use App\Filament\Resources\SmsMessageResource\RelationManagers;
use App\Models\SmsMessage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SmsMessageResource extends Resource
{
    protected static ?string $model = SmsMessage::class;

    protected static ?string $navigationIcon  = 'heroicon-o-envelope';
    protected static ?string $navigationLabel = 'Messages';
    protected static ?string $pluralLabel     = 'Messages';
    protected static ?string $navigationGroup = 'SMS Center';

    public static function form(Form $form): Form
    {
        // On ne laisse remplir que ce qui est utile pour envoyer un SMS
        return $form
            ->schema([
                Forms\Components\Select::make('contact_id')
                    ->label('Contact')
                    ->relationship('contact', 'name')
                    ->searchable()
                    ->preload()
                    ->helperText('Optionnel. Si renseigné, le numéro peut être pré-rempli.'),

                Forms\Components\Select::make('sim_card_id')
                    ->label('SIM / Canal GoIP')
                    ->relationship('simCard', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\TextInput::make('phone_number')
                    ->label('Numéro destinataire')
                    ->required()
                    ->maxLength(32)
                    ->helperText('Inclure l’indicatif pays si nécessaire, ex : +336...'),

                Forms\Components\Textarea::make('body')
                    ->label('Message')
                    ->required()
                    ->rows(4)
                    ->maxLength(1600),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('direction')
                    ->label('Dir.')
                    ->badge(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone_number')
                    ->label('Numéro')
                    ->searchable(),

                Tables\Columns\TextColumn::make('simCard.name')
                    ->label('SIM')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('body')
                    ->label('Message')
                    ->limit(60),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('direction')
                    ->label('Direction')
                    ->options([
                        'inbound'  => 'Entrant',
                        'outbound' => 'Sortant',
                        'system'   => 'Système',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'queued'    => 'En file',
                        'sending'   => 'Envoi',
                        'sent'      => 'Envoyé',
                        'delivered' => 'Livré',
                        'failed'    => 'Échec',
                        'received'  => 'Reçu',
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
            'index'  => Pages\ListSmsMessages::route('/'),
            'create' => Pages\CreateSmsMessage::route('/create'),
            //'view'   => Pages\ViewSmsMessage::route('/{record}'),
        ];
    }

    // 🚨 Contrairement à ce qu’on avait mis au début, on autorise la création
    public static function canCreate(): bool
    {
        return true;
    }

    public static function canEdit($record): bool
    {
        // On ne modifie pas les messages après coup
        return false;
    }
}
