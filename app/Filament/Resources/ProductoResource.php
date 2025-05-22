<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductoResource\Pages;
use App\Models\Producto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class ProductoResource extends Resource
{
    protected static ?string $model = Producto::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $modelLabel = 'Producto';
    protected static ?string $pluralModelLabel = 'Productos';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\TextInput::make('codigo')
                                    ->required()
                                    // ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->label('Código')
                                    ->placeholder('Ingrese el código único del producto'),

                                Forms\Components\TextInput::make('descripcion')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Descripción')
                                    ->placeholder('Ingrese el nombre o descripción del producto')
                                    ->columnSpan(2),
                            ])
                            ->columns(3),
                    ]),

                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\FileUpload::make('imagen')
                                    ->image()
                                    ->directory('productos')
                                    ->maxSize(5120)
                                    ->label('Imagen del producto')
                                    ->helperText('Suba una imagen del producto (máx. 5MB)')
                                    ->columnSpan(1),

                                Forms\Components\Toggle::make('activo')
                                    ->required()
                                    ->default(true)
                                    ->label('Producto activo')
                                    ->helperText('Los productos inactivos no se mostrarán en las cotizaciones')
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->columnSpan(1),
                            ])
                            ->columns(2),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('codigo')
                    ->searchable()
                    ->sortable()
                    ->label('Código')
                    ->copyable()
                    ->copyMessage('Código copiado'),

                Tables\Columns\TextColumn::make('descripcion')
                    ->searchable()
                    ->sortable()
                    ->label('Descripción')
                    ->limit(50)
                    ->wrap(),

                Tables\Columns\ImageColumn::make('imagen')
                    ->label('Imagen')
                    ->circular(),

                Tables\Columns\IconColumn::make('activo')
                    ->boolean()
                    ->sortable()
                    ->label('Activo')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('empresa')
                    ->label('Empresa')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->label('Fecha Creación')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->label('Última Actualización')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('activo')
                    ->query(fn(Builder $query): Builder => $query->where('activo', true))
                    ->label('Solo productos activos')
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make(),
                ]),
            ])
            ->defaultSort('codigo', 'asc');
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        return $user->hasRole('super_admin')
            ? parent::getEloquentQuery()
            : parent::getEloquentQuery()->where('empresa', $user->empresa);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductos::route('/'),
            'create' => Pages\CreateProducto::route('/create'),
            'edit' => Pages\EditProducto::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
