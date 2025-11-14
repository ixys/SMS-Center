<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SmsCampaignResource\Pages;
use App\Filament\Resources\SmsCampaignResource\RelationManagers;
use App\Models\SmsCampaign;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SmsCampaignResource extends Resource
{
    protected static ?string $model = SmsCampaign::class;

    protected static ?string $navigationIcon  = 'heroicon-o-megaphone';
    protected static ?string $navigationLabel = 'Campagnes SMS';
    protected static ?string $pluralLabel     = 'Campagnes SMS';
    protected static ?string $navigationGroup = 'SMS Center';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nom')
                    ->required(),

                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->rows(3),

                Forms\Components\Select::make('type')
                    ->label('Type')
                    ->options([
                        'bulk'          => 'Bulk',
                        'transactional' => 'Transactionnel',
                        'notification'  => 'Notification',
                    ])
                    ->default('bulk'),

                Forms\Components\Select::make('status')
                    ->label('Statut')
                    ->options([
                        'draft'     => 'Brouillon',
                        'scheduled' => 'Planifiée',
                        'running'   => 'En cours',
                        'paused'    => 'En pause',
                        'completed' => 'Terminée',
                        'cancelled' => 'Annulée',
                    ])
                    ->default('draft'),

                Forms\Components\Select::make('sms_template_id')
                    ->label('Template')
                    ->relationship('template', 'name')
                    ->searchable()
                    ->preload(),

                Forms\Components\Select::make('sim_card_id')
                    ->label('SIM')
                    ->relationship('simCard', 'name')
                    ->preload(),

                Forms\Components\DateTimePicker::make('scheduled_at')
                    ->label('Planifiée pour'),

                Forms\Components\TextInput::make('total_recipients')
                    ->label('Cibles')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false),

                Forms\Components\TextInput::make('total_sent')
                    ->label('Envoyés')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false),

                Forms\Components\TextInput::make('total_delivered')
                    ->label('Livrés')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false),

                Forms\Components\TextInput::make('total_failed')
                    ->label('Échecs')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),

                Tables\Columns\TextColumn::make('template.name')
                    ->label('Template'),

                Tables\Columns\TextColumn::make('simCard.name')
                    ->label('SIM'),

                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Planifiée')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_recipients')
                    ->label('Cibles')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_sent')
                    ->label('Envoyés')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'draft'     => 'Brouillon',
                        'scheduled' => 'Planifiée',
                        'running'   => 'En cours',
                        'paused'    => 'En pause',
                        'completed' => 'Terminée',
                        'cancelled' => 'Annulée',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('scheduled_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            // Plus tard : RelationManager pour SmsCampaignMessage
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSmsCampaigns::route('/'),
            'create' => Pages\CreateSmsCampaign::route('/create'),
            //'view'   => Pages\ViewSmsCampaign::route('/{record}'),
            'edit'   => Pages\EditSmsCampaign::route('/{record}/edit'),
        ];
    }
}
