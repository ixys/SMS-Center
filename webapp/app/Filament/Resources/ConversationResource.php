<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConversationResource\Pages;
use App\Filament\Resources\ConversationResource\RelationManagers;
use App\Models\Conversation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ConversationResource extends Resource
{
    protected static ?string $model = Conversation::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';
    protected static ?string $navigationGroup = 'Messaging';
    protected static ?string $navigationLabel = 'Conversations';
    protected static ?string $pluralLabel = 'Conversations';
    protected static ?string $modelLabel = 'Conversation';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('platform_account_id')
                                       ->label('Compte plateforme')
                                       ->relationship('platformAccount', 'name')
                                       ->required()
                                       ->searchable(),

                Forms\Components\TextInput::make('external_conversation_id')
                                          ->label('ID conversation externe')
                                          ->maxLength(255),

                Forms\Components\TextInput::make('remote_user_id')
                                          ->label('ID utilisateur distant')
                                          ->maxLength(255),

                Forms\Components\TextInput::make('remote_username')
                                          ->label('Identifiant / pseudo distant')
                                          ->maxLength(255),

                Forms\Components\TextInput::make('title')
                                          ->label('Titre interne')
                                          ->helperText('Libellé utilisé dans l’admin.')
                                          ->maxLength(255),

                Forms\Components\Select::make('status')
                                       ->label('Statut')
                                       ->options([
                                           'open' => 'Ouverte',
                                           'closed' => 'Fermée',
                                           'blocked' => 'Bloquée',
                                       ])
                                       ->default('open'),

                Forms\Components\DateTimePicker::make('last_message_at')
                                               ->label('Dernier message')
                                               ->disabled(),

                Forms\Components\DateTimePicker::make('last_inbound_at')
                                               ->label('Dernier entrant')
                                               ->disabled(),

                Forms\Components\DateTimePicker::make('last_outbound_at')
                                               ->label('Dernier sortant')
                                               ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('platformAccount.platform.name')
                                         ->label('Plateforme')
                                         ->sortable()
                                         ->badge(),

                Tables\Columns\TextColumn::make('platformAccount.name')
                                         ->label('Compte')
                                         ->sortable()
                                         ->limit(20),

                Tables\Columns\TextColumn::make('remote_username')
                                         ->label('Contact')
                                         ->searchable()
                                         ->limit(25),

                Tables\Columns\TextColumn::make('status')
                                         ->label('Statut')
                                         ->badge()
                                         ->colors([
                                             'success' => 'open',
                                             'danger' => 'blocked',
                                             'gray' => 'closed',
                                         ]),

                Tables\Columns\TextColumn::make('last_message_at')
                                         ->label('Dernier message')
                                         ->dateTime()
                                         ->sortable(),

                Tables\Columns\TextColumn::make('messages_count')
                                         ->label('Messages')
                                         ->counts('messages'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('platform_account_id')
                                           ->label('Compte')
                                           ->relationship('platformAccount', 'name'),
                Tables\Filters\SelectFilter::make('status')
                                           ->label('Statut')
                                           ->options([
                                               'open' => 'Ouverte',
                                               'closed' => 'Fermée',
                                               'blocked' => 'Bloquée',
                                           ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('last_message_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\MessagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConversations::route('/'),
            'create' => Pages\CreateConversation::route('/create'),
            //'view' => Pages\ViewConversation::route('/{record}'),
            'edit' => Pages\EditConversation::route('/{record}/edit'),
        ];
    }
}
