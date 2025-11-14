<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactGroupResource\Pages;
use App\Filament\Resources\ContactGroupResource\RelationManagers;
use App\Models\ContactGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContactGroupResource extends Resource
{
    protected static ?string $model = ContactGroup::class;

    protected static ?string $navigationIcon  = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Groupes de contacts';
    protected static ?string $pluralLabel     = 'Groupes de contacts';
    protected static ?string $navigationGroup = 'SMS Center';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nom')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->rows(3),

                Forms\Components\ColorPicker::make('color')
                    ->label('Couleur'),

                Forms\Components\Toggle::make('is_system')
                    ->label('Groupe système')
                    ->helperText('Non supprimable si activé.'),
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

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50),

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

    public static function getRelations(): array
    {
        return [
            // Plus tard : RelationManager pour les contacts du groupe
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListContactGroups::route('/'),
            'create' => Pages\CreateContactGroup::route('/create'),
            'edit'   => Pages\EditContactGroup::route('/{record}/edit'),
        ];
    }
}
