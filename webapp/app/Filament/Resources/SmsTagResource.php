<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SmsTagResource\Pages;
use App\Filament\Resources\SmsTagResource\RelationManagers;
use App\Models\SmsTag;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SmsTagResource extends Resource
{
    protected static ?string $model = SmsTag::class;

    protected static ?string $navigationIcon  = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Tags';
    protected static ?string $pluralLabel     = 'Tags';
    protected static ?string $navigationGroup = 'SMS Center';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nom')
                    ->required(),

                Forms\Components\TextInput::make('slug')
                    ->label('Slug')
                    ->required(),

                Forms\Components\ColorPicker::make('color')
                    ->label('Couleur'),

                Forms\Components\Toggle::make('is_system')
                    ->label('Tag système')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),

                Tables\Columns\ColorColumn::make('color')
                    ->label('Couleur'),

                Tables\Columns\IconColumn::make('is_system')
                    ->label('Système')
                    ->boolean(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->hidden(fn ($records) => $records->contains('is_system', true)),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSmsTags::route('/'),
            'create' => Pages\CreateSmsTag::route('/create'),
            'edit'   => Pages\EditSmsTag::route('/{record}/edit'),
        ];
    }
}
