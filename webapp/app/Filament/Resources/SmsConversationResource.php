<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SmsConversationResource\Pages;
use App\Filament\Resources\SmsConversationResource\RelationManagers;
use App\Models\SmsConversation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SmsConversationResource extends Resource
{
    protected static ?string $model = SmsConversation::class;

    protected static ?string $navigationIcon  = 'heroicon-o-chat-bubble-bottom-center-text';
    protected static ?string $navigationLabel = 'Conversations';
    protected static ?string $pluralLabel     = 'Conversations';
    protected static ?string $navigationGroup = 'SMS Center';

    public static function form(Form $form): Form
    {
        // Plutôt lecture seule, on laisse Filament en "view" principalement
        return $form
            ->schema([
                Forms\Components\TextInput::make('phone_number')
                    ->label('Numéro')
                    ->disabled(),

                Forms\Components\Select::make('contact_id')
                    ->label('Contact')
                    ->relationship('contact', 'name')
                    ->searchable()
                    ->preload(),

                Forms\Components\Select::make('sim_card_id')
                    ->label('SIM')
                    ->relationship('simCard', 'name')
                    ->preload(),

                Forms\Components\Textarea::make('last_message_preview')
                    ->label('Dernier message')
                    ->rows(3)
                    ->disabled(),

                Forms\Components\Toggle::make('is_archived')
                    ->label('Archivée'),

                Forms\Components\Toggle::make('is_muted')
                    ->label('Muette'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('contact.name')
                    ->label('Contact')
                    ->searchable(),

                Tables\Columns\TextColumn::make('phone_number')
                    ->label('Numéro')
                    ->searchable(),

                Tables\Columns\TextColumn::make('simCard.name')
                    ->label('SIM')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('last_message_preview')
                    ->label('Dernier message')
                    ->limit(50),

                Tables\Columns\TextColumn::make('last_message_at')
                    ->label('Dernier échange')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('unread_inbound_count')
                    ->label('Non lus')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_archived')
                    ->label('Archivée')
                    ->boolean()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_archived')->label('Archivée'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('last_message_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSmsConversations::route('/'),
            // 'view'  => Pages\ViewSmsConversation::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        // On ne crée pas de conversation à la main
        return false;
    }

    public static function canEdit($record): bool
    {
        // Tu peux passer à true si tu veux les éditer
        return true;
    }
}
