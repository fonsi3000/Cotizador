<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ListaPrecioResource\Pages;
use App\Models\ListaPrecio;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListaPrecioResource extends Resource
{
    protected static ?string $model = ListaPrecio::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Listas de Precios';
    protected static ?string $pluralModelLabel = 'Listas de Precios';
    protected static ?string $modelLabel = 'Lista de Precios';
    protected static ?int $navigationSort = 15;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),

                Forms\Components\DatePicker::make('fecha_inicio')
                    ->label('Fecha de inicio')
                    ->required(),

                Forms\Components\DatePicker::make('fecha_fin')
                    ->label('Fecha de fin'),

                Forms\Components\Toggle::make('activo')
                    ->label('¿Activa?')
                    ->default(true)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable(),

                Tables\Columns\TextColumn::make('fecha_inicio')
                    ->label('Fecha de inicio')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('fecha_fin')
                    ->label('Fecha de fin')
                    ->date()
                    ->sortable(),

                Tables\Columns\IconColumn::make('activo')
                    ->label('Activa')
                    ->boolean(),

                Tables\Columns\TextColumn::make('empresa')
                    ->label('Empresa')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Puedes agregar filtros si lo necesitas
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

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        if ($user->hasRole('super_admin')) {
            return parent::getEloquentQuery();
        }

        return parent::getEloquentQuery()
            ->where('empresa', $user->empresa);
    }

    public static function getRelations(): array
    {
        return [
            // Aquí puedes añadir RelationManagers si los usas
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListListaPrecios::route('/'),
            'create' => Pages\CreateListaPrecio::route('/create'),
            'edit' => Pages\EditListaPrecio::route('/{record}/edit'),
        ];
    }
}
