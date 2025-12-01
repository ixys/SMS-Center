<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MessageResource\Pages;
use App\Models\Message;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MessageResource extends Resource
{
    protected static ?string $model = Message::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope-open';
    protected static ?string $navigationGroup = 'Messaging';
    protected static ?string $navigationLabel = 'Messages (raw)';
    protected static ?string $pluralLabel = 'Messages';
    protected static ?string $modelLabel = 'Message';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('conversation_id')
                                       ->label('Conversation')
                                       ->relationship('conversation', 'title')
                                       ->searchable()
                                       ->required(),

                Forms\Components\Select::make('platform_account_id')
                                       ->label('Compte')
                                       ->relationship('platformAccount', 'name')
                                       ->required(),

                Forms\Components\TextInput::make('external_message_id')
                                          ->label('ID message externe')
                                          ->maxLength(255),

                Forms\Components\Select::make('direction')
                                       ->label('Direction')
                                       ->options([
                                           'inbound' => 'Entrant',
                                           'outbound' => 'Sortant',
                                       ])
                                       ->required(),

                Forms\Components\TextInput::make('from')
                                          ->label('De')
                                          ->maxLength(255),

                Forms\Components\TextInput::make('to')
                                          ->label('Vers')
                                          ->maxLength(255),

                Forms\Components\Textarea::make('content')
                                         ->label('Contenu'),

                Forms\Components\KeyValue::make('attachments')
                                         ->label('Pièces jointes'),

                Forms\Components\TextInput::make('status')
                                          ->label('Statut')
                                          ->maxLength(50),

                Forms\Components\DateTimePicker::make('sent_at')
                                               ->label('Envoyé le'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('conversation.title')
                                         ->label('Conversation')
                                         ->limit(30)
                                         ->searchable(),

                Tables\Columns\TextColumn::make('platformAccount.platform.name')
                                         ->label('Plateforme')
                                         ->badge(),

                Tables\Columns\TextColumn::make('direction')
                                         ->label('Dir.')
                                         ->badge()
                                         ->colors([
                                             'success' => 'outbound',
                                             'primary' => 'inbound',
                                         ]),

                Tables\Columns\TextColumn::make('from')
                                         ->label('De')
                                         ->limit(18),

                Tables\Columns\TextColumn::make('to')
                                         ->label('Vers')
                                         ->limit(18),

                Tables\Columns\TextColumn::make('content')
                                         ->label('Contenu')
                                         ->limit(60)
                                         ->tooltip(fn ($record) => $record->content),

                Tables\Columns\TextColumn::make('status')
                                         ->label('Statut')
                                         ->badge(),

                Tables\Columns\TextColumn::make('sent_at')
                                         ->label('Envoyé le')
                                         ->dateTime()
                                         ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('platform_account_id')
                                           ->label('Compte')
                                           ->relationship('platformAccount', 'name'),

                Tables\Filters\SelectFilter::make('direction')
                                           ->label('Dir.')
                                           ->options([
                                               'inbound' => 'Entrant',
                                               'outbound' => 'Sortant',
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
            ->defaultSort('sent_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMessages::route('/'),
            //'view' => Pages\ViewMessage::route('/{record}'),
            'edit' => Pages\EditMessage::route('/{record}/edit'),
        ];
    }
}
