<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalaVentaResource\Pages;
use App\Models\SalaVenta;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class SalaVentaResource extends Resource
{
    protected static ?string $model = SalaVenta::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationLabel = 'Salas de Ventas';
    protected static ?string $pluralLabel = 'Salas de Ventas';
    protected static ?string $modelLabel = 'Sala de Ventas';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('telefono')
                    ->label('Teléfono')
                    ->tel()
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('direccion')
                    ->label('Dirección')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('empresa')
                    ->label('Empresa')
                    ->options([
                        'Espumas Medellin S.A' => 'Espumas Medellin S.A',
                        'Espumados del Litoral S.A' => 'Espumados del Litoral S.A',
                    ])
                    ->nullable()
                    ->searchable()
                    ->preload(),

                Forms\Components\TextInput::make('codigo_sap')
                    ->label('Codigo SAP de la tienda')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('direccion')
                    ->label('Dirección')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('empresa')
                    ->label('Empresa')
                    ->sortable(),

                Tables\Columns\TextColumn::make('codigo_sap')
                    ->label('Codigo SAP')
                    ->searchable()
                    ->sortable(),

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

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        // Mostrar todo si es super_admin
        if ($user->hasRole('super_admin')) {
            return parent::getEloquentQuery();
        }

        // Filtrar por empresa del usuario autenticado
        return parent::getEloquentQuery()
            ->where('empresa', $user->empresa);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalaVentas::route('/'),
            'create' => Pages\CreateSalaVenta::route('/create'),
            'edit' => Pages\EditSalaVenta::route('/{record}/edit'),
        ];
    }
}
