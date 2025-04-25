<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductoPrecioResource\Pages;
use App\Filament\Resources\ProductoPrecioResource\RelationManagers;
use App\Models\ProductoPrecio;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductoPrecioResource extends Resource
{
    protected static ?string $model = ProductoPrecio::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('codigo_producto')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('lista_precio_id')
                    ->relationship('listaPrecio', 'id')
                    ->required(),
                Forms\Components\TextInput::make('precio')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('codigo_producto')
                    ->searchable(),
                Tables\Columns\TextColumn::make('listaPrecio.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('precio')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListProductoPrecios::route('/'),
            'create' => Pages\CreateProductoPrecio::route('/create'),
            'edit' => Pages\EditProductoPrecio::route('/{record}/edit'),
        ];
    }
}
