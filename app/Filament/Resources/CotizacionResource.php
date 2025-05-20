<?php

namespace App\Filament\Resources;


use App\Filament\Resources\CotizacionResource\Pages;
use App\Models\Cotizacion;
use App\Models\Producto;
use App\Models\ProductoPrecio;
use App\Models\ListaPrecio;
use App\Models\SalaVenta;
use App\Services\WhatsAppService;
use App\Mail\Cotizacion as CotizacionMail;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class CotizacionResource extends Resource
{
    protected static ?string $model = Cotizacion::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Cotizaciones';
    protected static ?string $modelLabel = 'Cotización';
    protected static ?string $pluralModelLabel = 'Cotizaciones';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Datos del Cliente')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('nombre_cliente')
                                    ->label('Nombre del Cliente')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('documento_cliente')
                                    ->label('Documento del Cliente')
                                    ->required()
                                    ->maxLength(50),
                                TextInput::make('numero_celular_cliente')
                                    ->label('Número de Celular (WhatsApp)')
                                    ->required()
                                    ->maxLength(20),
                                TextInput::make('correo_electronico_cliente')
                                    ->label('Correo Electrónico')
                                    ->email()
                                    ->maxLength(255)
                                    ->nullable(),

                                Hidden::make('usuario_id')
                                    ->default(fn() => auth()->id()),
                            ]),
                        Select::make('sala_venta_id')
                            ->label('Sala de Ventas')
                            ->options(function () {
                                $user = auth()->user();
                                return \App\Models\SalaVenta::query()
                                    ->when(!$user->hasRole('super_admin'), fn($q) => $q->where('empresa', $user->empresa))
                                    ->pluck('nombre', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Section::make('Productos Cotizados')
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                Select::make('producto_id')
                                    ->label('Producto')
                                    ->options(function () {
                                        $user = auth()->user();
                                        return Producto::select('id', 'codigo', 'descripcion')
                                            ->when(!$user->hasRole('super_admin'), fn($q) => $q->where('empresa', $user->empresa))
                                            ->get()
                                            ->mapWithKeys(fn($producto) => [
                                                $producto->id => "{$producto->codigo} - {$producto->descripcion}",
                                            ]);
                                    })

                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function (callable $set) {
                                        $set('lista_precio_id', null);
                                        $set('precio_unitario', null);
                                        $set('cantidad', 1);
                                        $set('subtotal', null);
                                    }),

                                Select::make('lista_precio_id')
                                    ->label('Lista de Precio')
                                    ->options(function (callable $get) {
                                        $productoId = $get('producto_id');
                                        if (!$productoId) return [];

                                        $producto = \App\Models\Producto::find($productoId);
                                        if (!$producto) return [];

                                        // Obtener las listas de precio asociadas a ese producto
                                        $listasPrecioIds = \App\Models\ProductoPrecio::where('codigo_producto', $producto->codigo)
                                            ->pluck('lista_precio_id')
                                            ->toArray();

                                        $user = auth()->user();

                                        // Filtrar listas por empresa si no es super_admin
                                        return \App\Models\ListaPrecio::whereIn('id', $listasPrecioIds)
                                            ->when(!$user->hasRole('super_admin'), fn($q) => $q->where('empresa', $user->empresa))
                                            ->pluck('nombre', 'id')
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->required()
                                    ->preload()
                                    ->reactive()
                                    ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                        $productoId = $get('producto_id');

                                        if (!$productoId || !$state) {
                                            $set('precio_unitario', null);
                                            $set('subtotal', null);
                                            return;
                                        }

                                        $producto = \App\Models\Producto::find($productoId);
                                        if (!$producto) {
                                            $set('precio_unitario', null);
                                            $set('subtotal', null);
                                            return;
                                        }

                                        $precio = \App\Models\ProductoPrecio::where('codigo_producto', $producto->codigo)
                                            ->where('lista_precio_id', $state)
                                            ->value('precio');

                                        if ($precio !== null) {
                                            $set('precio_unitario', $precio);
                                            $cantidad = $get('cantidad') ?? 1;
                                            $set('subtotal', floatval($precio) * floatval($cantidad));
                                        } else {
                                            $set('precio_unitario', null);
                                            $set('subtotal', null);
                                        }
                                    }),


                                TextInput::make('precio_unitario')
                                    ->label('Precio Unitario')
                                    ->prefix('$')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated()
                                    ->required(),

                                TextInput::make('cantidad')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->default(1)
                                    ->reactive()
                                    ->afterStateUpdated(function (callable $set, callable $get) {
                                        $precio = $get('precio_unitario') ?? 0;
                                        $cantidad = $get('cantidad') ?? 1;
                                        $set('subtotal', floatval($precio) * floatval($cantidad));
                                    })
                                    ->required(),

                                TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->prefix('$')
                                    ->disabled()
                                    ->dehydrated(),
                            ])
                            ->columns(2)
                            ->defaultItems(1)
                            ->itemLabel(fn(array $state): ?string => "Producto seleccionado"),
                    ])
                    ->columns(1)
                    ->collapsible(),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre_cliente')->label('Cliente')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('documento_cliente')->label('Documento')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('usuario.name')->label('Asesor')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('salaVenta.nombre')->label('Sala de Ventas')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('total_cotizacion')->label('Total con IVA')->money('COP')->sortable()
                    ->getStateUsing(fn(Cotizacion $record) => $record->total_cotizacion * 1.19),
                Tables\Columns\TextColumn::make('created_at')->label('Fecha')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->actions([
                Action::make('view')
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading(fn(Cotizacion $record): string => "Cotización: {$record->nombre_cliente}")
                    ->modalWidth('5xl')
                    ->modalContent(function (Cotizacion $record) {
                        $record->load(['items.producto', 'items.listaPrecio', 'usuario', 'salaVenta']);
                        return view('Cotizacion.Cotizaciones', ['cotizacion' => $record]);
                    })
                    ->modalFooterActions([
                        Action::make('descargar')
                            ->label('Descargar')
                            ->icon('heroicon-o-arrow-down')
                            ->color('gray')
                            ->action(function (Cotizacion $record) {
                                return response()->streamDownload(function () use ($record) {
                                    $record->load(['items.producto', 'items.listaPrecio', 'usuario', 'salaVenta']);
                                    echo Pdf::loadView('Cotizacion.CotizacionPDF', [
                                        'cotizacion' => $record,
                                        'isPdfDownload' => true,
                                    ])->output();
                                }, "cotizacion-{$record->id}.pdf");
                            }),

                        Action::make('imprimir')
                            ->label('Imprimir')
                            ->icon('heroicon-o-printer')
                            ->color('success')
                            ->url(fn(Cotizacion $record) => route('cotizacion.tirilla', $record))
                            ->openUrlInNewTab(),

                        Action::make('enviar_whatsapp')
                            ->label('Enviar al WhatsApp')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->color('success')
                            ->action(function (Cotizacion $record) {
                                WhatsAppService::enviarCotizacion($record);
                                Notification::make()
                                    ->title('Cotización enviada por WhatsApp')
                                    ->success()
                                    ->send();
                            }),

                        Action::make('enviar_correo')
                            ->label('Enviar al Correo')
                            ->icon('heroicon-o-envelope')
                            ->color('primary')
                            ->visible(fn(Cotizacion $record) => !empty($record->correo_electronico_cliente))
                            ->action(function (Cotizacion $record) {
                                Mail::to($record->correo_electronico_cliente)
                                    ->send(new CotizacionMail(
                                        $record->load(['items.producto', 'items.listaPrecio', 'usuario', 'salaVenta'])
                                    ));

                                Notification::make()
                                    ->title("Cotización enviada al correo {$record->correo_electronico_cliente}")
                                    ->success()
                                    ->send();
                            }),

                        Action::make('cerrar')
                            ->label('Cerrar')
                            ->color('secondary')
                            ->action(fn() => null),
                    ]),

                EditAction::make()->modalHeading('Editar Cotización')->modalWidth('5xl'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->emptyStateHeading('No hay cotizaciones registradas')
            ->emptyStateDescription('Crea una nueva cotización haciendo clic en "Crear"')
            ->emptyStateIcon('heroicon-o-clipboard-document-list')
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Crear Cotización')
                    ->modalHeading('Nueva Cotización')
                    ->modalWidth('5xl'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }
    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        return parent::getEloquentQuery()
            ->when(!$user->hasRole('super_admin'), function (Builder $query) use ($user) {
                $query->whereHas('salaVenta', function (Builder $q) use ($user) {
                    $q->where('empresa', $user->empresa);
                });
            })
            ->when($user->hasRole('asesor'), function (Builder $query) use ($user) {
                $query->where('usuario_id', $user->id);
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCotizacions::route('/'),
            'create' => Pages\CreateCotizacion::route('/create'),
            'edit' => Pages\EditCotizacion::route('/{record}/edit'),
        ];
    }
}
