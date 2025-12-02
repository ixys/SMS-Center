<?php

namespace App\Filament\Resources\ConversationResource\RelationManagers;

use App\Models\Message;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class MessagesRelationManager extends RelationManager
{
    /**
     * Nom de la relation Eloquent sur le modèle parent (Conversation).
     */
    protected static string $relationship = 'messages';

    protected static ?string $title = 'Messages';

    /**
     * Optionnel : icône dans l’onglet de relation.
     */
    protected static ?string $icon = 'heroicon-o-chat-bubble-left-right';

    /**
     * Formulaire utilisé si tu autorises la création/édition directe d’un message
     * (pratique en debug, mais en prod tu utiliseras surtout l’action "Envoyer un message").
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('direction')
                                       ->label('Direction')
                                       ->options([
                                           'inbound' => 'Entrant',
                                           'outbound' => 'Sortant',
                                       ])
                                       ->default('outbound')
                                       ->required(),

                Forms\Components\TextInput::make('from')
                                          ->label('De')
                                          ->maxLength(255),

                Forms\Components\TextInput::make('to')
                                          ->label('Vers')
                                          ->maxLength(255),

                Forms\Components\Textarea::make('content')
                                         ->label('Message')
                                         ->rows(4)
                                         ->required(),

                Forms\Components\KeyValue::make('attachments')
                                         ->label('Pièces jointes')
                                         ->addButtonLabel('Ajouter')
                                         ->nullable(),

                Forms\Components\TextInput::make('status')
                                          ->label('Statut')
                                          ->maxLength(50),

                Forms\Components\DateTimePicker::make('sent_at')
                                               ->label('Envoyé le'),
            ]);
    }

    /**
     * Configuration de la table des messages (vue chat simplifiée).
     */
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('content')
            ->columns([
                // Direction inbound/outbound
                Tables\Columns\IconColumn::make('direction')
                                         ->label('')
                                         ->tooltip(fn (Message $record): string => $record->direction === 'inbound' ? 'Entrant' : 'Sortant')
                                         ->icon(fn (Message $record): string => $record->direction === 'inbound'
                                             ? 'heroicon-o-arrow-uturn-left'
                                             : 'heroicon-o-arrow-uturn-right'
                                         )
                                         ->size('lg'),

                Tables\Columns\TextColumn::make('from')
                                         ->label('De')
                                         ->limit(20)
                                         ->toggleable(),

                Tables\Columns\TextColumn::make('to')
                                         ->label('Vers')
                                         ->limit(20)
                                         ->toggleable(),

                Tables\Columns\TextColumn::make('content')
                                         ->label('Message')
                                         ->wrap()
                                         ->limit(120)
                                         ->tooltip(fn (Message $record): ?string => $record->content),

                Tables\Columns\TextColumn::make('status')
                                         ->label('Statut')
                                         ->badge()
                                         ->colors([
                                             'success' => ['delivered', 'sent'],
                                             'warning' => ['pending'],
                                             'danger' => ['failed'],
                                         ]),

                Tables\Columns\TextColumn::make('sent_at')
                                         ->label('Envoyé le')
                                         ->dateTime()
                                         ->sortable()
                                         ->since(), // affichage relatif + tooltip
            ])
            ->defaultSort('sent_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('direction')
                                           ->label('Direction')
                                           ->options([
                                               'inbound' => 'Entrant',
                                               'outbound' => 'Sortant',
                                           ]),
            ])
            ->headerActions([
                // Action "Envoyer un message" en haut de la liste
                Tables\Actions\Action::make('sendMessage')
                                     ->label('Envoyer un message')
                                     ->icon('heroicon-o-paper-airplane')
                                     ->modalHeading('Envoyer un message')
                                     ->form([
                                         Forms\Components\Textarea::make('content')
                                                                  ->label('Message')
                                                                  ->rows(4)
                                                                  ->required(),
                                     ])
                                     ->action(function (array $data, RelationManager $livewire): void {
                                         // Conversation parente (owner)
                                         $conversation = $livewire->getOwnerRecord();
                                         $account = $conversation->platformAccount;

                                         try {
                                             $driver = $account->driver();
                                             $driver->sendMessage($account, $conversation, $data['content']);

                                             Notification::make()
                                                         ->title('Message envoyé')
                                                         ->body('Le message a été envoyé via la plateforme associée.')
                                                         ->success()
                                                         ->send();
                                         } catch (Throwable $e) {
                                             report($e);

                                             Notification::make()
                                                         ->title('Erreur lors de l’envoi du message')
                                                         ->body($e->getMessage())
                                                         ->danger()
                                                         ->send();
                                         }
                                     }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Si tu veux pouvoir corriger un message à la main :
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // En général on évite de purger en masse un historique de messages,
                // mais tu peux l’activer si tu veux vraiment.
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
