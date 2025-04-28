<?php

namespace App\Filament\Resources\ProductoPrecioResource\Pages;

use App\Filament\Resources\ProductoPrecioResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use EightyNine\ExcelImport\ExcelImportAction;
use App\Models\Producto;
use App\Models\ListaPrecio;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;
use App\Imports\ProductoPrecioImport;

class ListProductoPrecios extends ListRecords
{
    protected static string $resource = ProductoPrecioResource::class;

    protected function getHeaderActions(): array
    {
        // Obtener todos los productos activos
        $productos = Producto::where('activo', true)
            ->select('codigo', 'descripcion')
            ->get();

        // Obtener todas las listas de precios activas
        $listasPrecios = ListaPrecio::where('activo', true)
            ->select('id', 'nombre')
            ->get();

        // Crear la plantilla de muestra para descargar
        $sampleData = [];

        foreach ($productos as $producto) {
            $row = [
                'codigo_producto' => $producto->codigo,
                'descripcion_producto' => $producto->descripcion,
            ];

            foreach ($listasPrecios as $listaPrecio) {
                $row[$listaPrecio->nombre] = '';
            }

            $sampleData[] = $row;
        }

        return [
            ExcelImportAction::make()
                ->label('Importar Precios')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->modalHeading('Importar Precios de Productos')
                ->modalDescription('Seleccione el archivo Excel con los precios a importar. Descargue la plantilla para ver el formato esperado.')
                ->sampleExcel(
                    sampleData: $sampleData,
                    fileName: 'plantilla-precios-' . now()->format('Y-m-d') . '.xlsx',
                    sampleButtonLabel: 'Descargar Plantilla'
                )
                ->processCollectionUsing(function (string $modelClass, Collection $collection) {
                    $importer = new ProductoPrecioImport();
                    $resultado = $importer->collection($collection);

                    Notification::make()
                        ->title('Importación completada')
                        ->body("Se actualizaron {$resultado['procesados']} productos.\nHubo {$resultado['errores']} errores.")
                        ->success()
                        ->send();

                    return $collection;
                }),

            Actions\CreateAction::make()
                ->label('Agregar Precio')
                ->icon('heroicon-o-plus'),
        ];
    }
}
