<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignResource\Pages;
use App\Models\Campaign;
use App\Models\ContactGroup;
use App\Services\Campaigns\CampaignSender;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class CampaignResource extends Resource
{
    protected static ?string $model = Campaign::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $navigationGroup = 'Messaging';
    protected static ?string $modelLabel = 'Campagne';
    protected static ?string $pluralModelLabel = 'Campagnes';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Général')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                                                  ->label('Nom de la campagne')
                                                                  ->required(),

                                        Forms\Components\Select::make('platform_account_id')
                                                               ->label('Compte / SIM')
                                                               ->relationship('platformAccount', 'name')
                                                               ->required()
                                                               ->preload(),

                                        Forms\Components\Textarea::make('description')
                                                                 ->label('Description')
                                                                 ->rows(2)
                                                                 ->nullable(),
                                    ])
                                    ->columns(2),

            Forms\Components\Section::make('Contenu')
                                    ->schema([
                                        Forms\Components\Textarea::make('content')
                                                                 ->label('Message')
                                                                 ->rows(5)
                                                                 ->required(),
                                    ]),

            Forms\Components\Section::make('Ciblage')
                                    ->schema([
                                        Forms\Components\Select::make('contact_group_ids')
                                                               ->label('Groupes de contacts')
                                                               ->multiple()
                                                               ->preload()
                                                               ->relationship('contactGroups', 'name'),
                                    ]),

            Forms\Components\Section::make('Planification')
                                    ->schema([
                                        Forms\Components\DateTimePicker::make('scheduled_at')
                                                                       ->label('Date/heure d’envoi (optionnel)')
                                                                       ->seconds(false),
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

                Tables\Columns\TextColumn::make('platformAccount.name')
                                         ->label('Compte')
                                         ->sortable(),

                Tables\Columns\TextColumn::make('status')
                                         ->label('Statut')
                                         ->badge(),

                Tables\Columns\TextColumn::make('total_recipients')
                                         ->label('Destinataires')
                                         ->sortable(),

                Tables\Columns\TextColumn::make('sent_count')
                                         ->label('Envoyés')
                                         ->sortable(),

                Tables\Columns\TextColumn::make('failed_count')
                                         ->label('Échecs')
                                         ->sortable(),

                Tables\Columns\TextColumn::make('scheduled_at')
                                         ->label('Planifiée')
                                         ->dateTime()
                                         ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                                         ->label('Créée le')
                                         ->dateTime()
                                         ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                                           ->label('Statut')
                                           ->options([
                                               'draft'     => 'Brouillon',
                                               'scheduled' => 'Planifiée',
                                               'running'   => 'En cours',
                                               'completed' => 'Terminée',
                                               'cancelled' => 'Annulée',
                                           ]),
            ])
            ->actions([
                Tables\Actions\Action::make('prepareRecipients')
                                     ->label('Préparer destinataires')
                                     ->icon('heroicon-o-user-group')
                                     ->requiresConfirmation()
                                     ->visible(fn (Campaign $record) => in_array($record->status, ['draft', 'scheduled']))
                                     ->action(function (Campaign $record, CampaignSender $sender) {
                                         $sender->buildRecipients($record);

                                         Notification::make()
                                                     ->title('Destinataires préparés')
                                                     ->body("Total destinataires : {$record->fresh()->total_recipients}")
                                                     ->success()
                                                     ->send();
                                     }),

                Tables\Actions\Action::make('sendNow')
                                     ->label('Lancer maintenant')
                                     ->icon('heroicon-o-paper-airplane')
                                     ->requiresConfirmation()
                                     ->visible(fn (Campaign $record) => in_array($record->status, ['draft', 'scheduled', 'running']))
                                     ->action(function (Campaign $record, CampaignSender $sender) {
                                         $sender->buildRecipients($record);
                                         $sender->send($record->fresh());

                                         Notification::make()
                                                     ->title('Campagne envoyée')
                                                     ->body("Envoyés : {$record->fresh()->sent_count}, échecs : {$record->fresh()->failed_count}")
                                                     ->success()
                                                     ->send();
                                     }),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCampaigns::route('/'),
            'create' => Pages\CreateCampaign::route('/create'),
            'edit'   => Pages\EditCampaign::route('/{record}/edit'),
        ];
    }
}
