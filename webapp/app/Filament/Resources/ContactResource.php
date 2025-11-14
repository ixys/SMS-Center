<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactResource\Pages;
use App\Filament\Resources\ContactResource\RelationManagers;
use App\Models\Contact;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContactResource extends Resource
{
    protected static ?string $model = Contact::class;

    protected static ?string $navigationIcon  = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Contacts';
    protected static ?string $pluralLabel     = 'Contacts';
    protected static ?string $navigationGroup = 'SMS Center';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nom complet')
                    ->maxLength(255),

                Forms\Components\TextInput::make('first_name')
                    ->label('Prénom')
                    ->maxLength(255),

                Forms\Components\TextInput::make('last_name')
                    ->label('Nom')
                    ->maxLength(255),

                Forms\Components\TextInput::make('phone_number')
                    ->label('Numéro')
                    ->required()
                    ->maxLength(32),

                Forms\Components\TextInput::make('international_phone_number')
                    ->label('Numéro international')
                    ->maxLength(32),

                Forms\Components\TextInput::make('country_code')
                    ->label('Code pays')
                    ->maxLength(4),

                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255),

                Forms\Components\Toggle::make('is_active')
                    ->label('Actif')
                    ->default(true),

                Forms\Components\Textarea::make('metadata')
                    ->label('Métadonnées (JSON)')
                    ->rows(3)
                    ->hint('Stockage libre au format JSON'),
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

                Tables\Columns\TextColumn::make('phone_number')
                    ->label('Numéro')
                    ->searchable(),

                Tables\Columns\TextColumn::make('international_phone_number')
                    ->label('Numéro intl.')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('country_code')
                    ->label('Pays')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Actif'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            // Tu pourras ajouter des RelationManagers (conversations, campagnes...) ici
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListContacts::route('/'),
            'create' => Pages\CreateContact::route('/create'),
            'edit'   => Pages\EditContact::route('/{record}/edit'),
        ];
    }
}
