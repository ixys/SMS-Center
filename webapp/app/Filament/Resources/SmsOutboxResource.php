<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SmsOutboxResource\Pages;
use App\Filament\Resources\SmsOutboxResource\RelationManagers;
use App\Models\Outbox;
use App\Models\SmsOutbox;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SmsOutboxResource extends Resource
{
    protected static ?string $model = Outbox::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSmsOutboxes::route('/'),
            'create' => Pages\CreateSmsOutbox::route('/create'),
            'edit' => Pages\EditSmsOutbox::route('/{record}/edit'),
        ];
    }
}
