<?php

namespace App\Imports;

use App\Models\Producto;
use App\Models\ListaPrecio;
use App\Models\ProductoPrecio;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;
use Exception;

class ProductoPrecioImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        $listasPrecios = ListaPrecio::where('activo', true)->get();
        $nombreListaPrecio = $listasPrecios->mapWithKeys(fn($lista) => [strtolower($lista->nombre) => $lista->id]);

        $productos = Producto::select('codigo')->pluck('codigo')->toArray();

        Log::info('Iniciando importación de precios', [
            'cantidad_listas' => $listasPrecios->count(),
            'cantidad_registros' => $rows->count(),
            'cantidad_productos' => count($productos),
        ]);

        $procesados = 0;
        $errores = 0;
        $registrosActualizados = 0;

        foreach ($rows as $index => $row) {
            try {
                if (empty($row['codigo_producto'])) {
                    continue;
                }

                $codigoProducto = trim((string) $row['codigo_producto']);

                if (!in_array($codigoProducto, $productos)) {
                    Log::error('Producto no encontrado', [
                        'fila' => $index + 1,
                        'codigo_producto' => $codigoProducto,
                    ]);
                    $errores++;
                    continue;
                }

                $actualizacionesEnProducto = 0;

                foreach ($row as $encabezado => $valor) {
                    if (in_array(strtolower($encabezado), ['codigo_producto', 'descripcion_producto'])) {
                        continue;
                    }

                    $encabezadoMinusculas = strtolower($encabezado);
                    $listaPrecioId = $nombreListaPrecio[$encabezadoMinusculas] ?? null;

                    if ($listaPrecioId !== null && $valor !== '' && is_numeric($valor)) {
                        $precio = $this->normalizarPrecio($valor);

                        if ($precio !== null) {
                            try {
                                Log::info('Actualizando o creando precio', [
                                    'producto' => $codigoProducto,
                                    'lista_precio' => $encabezadoMinusculas,
                                    'lista_precio_id' => $listaPrecioId,
                                    'precio' => $precio,
                                ]);

                                ProductoPrecio::updateOrCreate(
                                    [
                                        'codigo_producto' => $codigoProducto,
                                        'lista_precio_id' => $listaPrecioId,
                                    ],
                                    [
                                        'precio' => $precio,
                                    ]
                                );

                                $registrosActualizados++;
                                $actualizacionesEnProducto++;
                            } catch (Exception $e) {
                                Log::error('Error actualizando precio específico', [
                                    'fila' => $index + 1,
                                    'producto' => $codigoProducto,
                                    'lista_precio' => $encabezado,
                                    'error' => $e->getMessage(),
                                ]);
                                $errores++;
                            }
                        }
                    } elseif ($valor !== '' && is_numeric($valor)) {
                        Log::warning('Valor numérico encontrado pero no corresponde a una lista de precios activa', [
                            'fila' => $index + 1,
                            'producto' => $codigoProducto,
                            'encabezado' => $encabezado,
                            'valor' => $valor,
                        ]);
                    }
                }

                if ($actualizacionesEnProducto > 0) {
                    $procesados++;
                    Log::info('Precios actualizados para producto', [
                        'codigo_producto' => $codigoProducto,
                        'cantidad_actualizaciones' => $actualizacionesEnProducto,
                    ]);
                } else {
                    Log::info('Producto sin precios para actualizar', [
                        'codigo_producto' => $codigoProducto,
                    ]);
                }
            } catch (Exception $e) {
                Log::error('Error procesando fila', [
                    'fila' => $index + 1,
                    'error' => $e->getMessage(),
                ]);
                $errores++;
            }
        }

        Log::info('Importación completada', [
            'productos_procesados' => $procesados,
            'registros_actualizados' => $registrosActualizados,
            'errores' => $errores,
        ]);

        return [
            'procesados' => $procesados,
            'registros_actualizados' => $registrosActualizados,
            'errores' => $errores,
        ];
    }

    private function normalizarPrecio($precio)
    {
        if (is_numeric($precio)) {
            return (float) $precio;
        }

        if (is_string($precio)) {
            $precio = str_replace(',', '.', $precio);
            $precio = preg_replace('/[^0-9.]/', '', $precio);

            if (is_numeric($precio)) {
                return (float) $precio;
            }
        }

        return null;
    }
}
