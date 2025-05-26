<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductoPrecioResource\Pages;
use App\Models\ProductoPrecio;
use App\Models\Producto;
use App\Models\ListaPrecio;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class ProductoPrecioResource extends Resource
{
    protected static ?string $model = ProductoPrecio::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Precios de Productos';
    protected static ?string $modelLabel = 'Precio de Producto';
    protected static ?string $pluralModelLabel = 'Precios de Productos';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Select::make('codigo_producto')
                            ->label('Producto')
                            ->options(
                                Producto::where('activo', true)
                                    ->where('empresa', Auth::user()->empresa)
                                    ->get()
                                    ->mapWithKeys(fn($producto) => [
                                        $producto->codigo => "{$producto->codigo} - {$producto->descripcion}"
                                    ])
                            )
                            ->searchable()
                            ->required()
                            ->preload(),

                        Forms\Components\Select::make('lista_precio_id')
                            ->label('Lista de Precios')
                            ->options(
                                ListaPrecio::where('activo', true)
                                    ->get()
                                    ->pluck('nombre', 'id')
                            )
                            ->searchable()
                            ->required()
                            ->preload(),

                        Forms\Components\TextInput::make('precio')
                            ->label('Precio')
                            ->prefix('$')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->placeholder('0.00')
                            ->helperText('Ingrese el precio sin separadores de miles'),

                        Forms\Components\Hidden::make('empresa')
                            ->default(fn() => Auth::user()->empresa)
                            ->dehydrated(), // se envía en el form aunque esté oculto
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('codigo_producto')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('producto.descripcion')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('listaPrecio.nombre')
                    ->label('Lista de Precios')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('precio')
                    ->label('Precio')
                    ->money('COP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('empresa')
                    ->label('Empresa')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última Actualización')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('lista_precio_id')
                    ->label('Lista de Precios')
                    ->options(
                        ListaPrecio::pluck('nombre', 'id')
                    )
                    ->searchable(),

                Tables\Filters\Filter::make('precio_minimo')
                    ->form([
                        Forms\Components\TextInput::make('precio_min')
                            ->label('Precio Mínimo')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['precio_min'],
                            fn(Builder $query, $precio) => $query->where('precio', '>=', $precio)
                        );
                    }),

                Tables\Filters\Filter::make('precio_maximo')
                    ->form([
                        Forms\Components\TextInput::make('precio_max')
                            ->label('Precio Máximo')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['precio_max'],
                            fn(Builder $query, $precio) => $query->where('precio', '<=', $precio)
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading('Editar Precio de Producto'),
                Tables\Actions\DeleteAction::make()
                    ->modalHeading('Eliminar Precio de Producto'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->modalHeading('Eliminar Precios de Productos'),
                    ExportBulkAction::make()
                        ->label('Exportar seleccionados'),
                ]),
            ])
            ->emptyStateHeading('No hay precios registrados')
            ->emptyStateDescription('Comienza agregando precios a los productos o importándolos desde un archivo Excel.')
            ->emptyStateIcon('heroicon-o-currency-dollar')
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductoPrecios::route('/'),
            'create' => Pages\CreateProductoPrecio::route('/create'),
            'edit' => Pages\EditProductoPrecio::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        return $user->hasRole('super_admin')
            ? parent::getEloquentQuery()
            : parent::getEloquentQuery()->where('empresa', $user->empresa);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('empresa', Auth::user()->empresa)->count();
    }
}
