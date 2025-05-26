<?php

namespace App\Imports;

use App\Models\Producto;
use App\Models\ListaPrecio;
use App\Models\ProductoPrecio;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;
use Exception;

class ProductoPrecioImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        $empresa = Auth::user()?->empresa;

        if (!$empresa) {
            Log::error('No se puede determinar la empresa del usuario autenticado.');
            throw new \Exception('Empresa no definida para el usuario.');
        }

        $normalizar = fn($nombre) => strtolower(trim($nombre));

        $listasPrecios = ListaPrecio::where('activo', true)->get();
        $nombreListaPrecio = $listasPrecios->mapWithKeys(fn($lista) => [
            $normalizar($lista->nombre) => $lista->id,
        ]);

        $productos = Producto::where('empresa', $empresa)->pluck('codigo')->toArray();

        Log::info('Iniciando importación de precios', [
            'empresa' => $empresa,
            'cantidad_listas' => $listasPrecios->count(),
            'cantidad_registros' => $rows->count(),
            'cantidad_productos' => count($productos),
        ]);

        $procesados = 0;
        $errores = 0;
        $creados = 0;
        $actualizados = 0;

        foreach ($rows as $index => $row) {
            try {
                if (empty($row['codigo_producto'])) {
                    continue;
                }

                $codigoProducto = trim((string) $row['codigo_producto']);

                if (!in_array($codigoProducto, $productos)) {
                    Log::error('Producto no encontrado para esta empresa', [
                        'fila' => $index + 1,
                        'codigo_producto' => $codigoProducto,
                        'empresa' => $empresa,
                    ]);
                    $errores++;
                    continue;
                }

                $accionesEnProducto = 0;

                foreach ($row as $encabezado => $valor) {
                    if (in_array($normalizar($encabezado), ['codigo_producto', 'descripcion_producto'])) {
                        continue;
                    }

                    $encabezadoNormalizado = $normalizar($encabezado);
                    $listaPrecioId = $nombreListaPrecio[$encabezadoNormalizado] ?? null;

                    if ($listaPrecioId !== null && $valor !== '' && is_numeric($valor)) {
                        $precio = $this->normalizarPrecio($valor);

                        if ($precio !== null) {
                            try {
                                Log::debug('Verificando combinación', [
                                    'codigo_producto' => $codigoProducto,
                                    'lista_precio_id' => $listaPrecioId,
                                    'empresa' => $empresa,
                                ]);

                                $precioExistente = ProductoPrecio::where('codigo_producto', $codigoProducto)
                                    ->where('lista_precio_id', $listaPrecioId)
                                    ->where('empresa', $empresa)
                                    ->first();

                                if ($precioExistente) {
                                    $precioExistente->update(['precio' => $precio]);
                                    $actualizados++;
                                    Log::info('Precio actualizado', [
                                        'codigo_producto' => $codigoProducto,
                                        'lista_precio_id' => $listaPrecioId,
                                        'empresa' => $empresa,
                                        'precio' => $precio,
                                    ]);
                                } else {
                                    ProductoPrecio::create([
                                        'codigo_producto' => $codigoProducto,
                                        'lista_precio_id' => $listaPrecioId,
                                        'empresa' => $empresa,
                                        'precio' => $precio,
                                    ]);
                                    $creados++;
                                    Log::info('Precio creado', [
                                        'codigo_producto' => $codigoProducto,
                                        'lista_precio_id' => $listaPrecioId,
                                        'empresa' => $empresa,
                                        'precio' => $precio,
                                    ]);
                                }

                                $accionesEnProducto++;
                            } catch (Exception $e) {
                                Log::error('Error actualizando/creando precio', [
                                    'fila' => $index + 1,
                                    'codigo_producto' => $codigoProducto,
                                    'lista_precio' => $encabezado,
                                    'empresa' => $empresa,
                                    'error' => $e->getMessage(),
                                ]);
                                $errores++;
                            }
                        }
                    } elseif ($valor !== '' && is_numeric($valor)) {
                        Log::warning('Valor numérico con lista no válida', [
                            'fila' => $index + 1,
                            'producto' => $codigoProducto,
                            'encabezado_original' => $encabezado,
                            'encabezado_normalizado' => $encabezadoNormalizado,
                            'valor' => $valor,
                            'empresa' => $empresa,
                            'listas_disponibles' => $nombreListaPrecio->keys()->toArray(),
                        ]);
                    }
                }

                if ($accionesEnProducto > 0) {
                    $procesados++;
                    Log::info('Precios procesados para producto', [
                        'codigo_producto' => $codigoProducto,
                        'acciones_realizadas' => $accionesEnProducto,
                    ]);
                } else {
                    Log::info('Producto sin precios para actualizar o crear', [
                        'codigo_producto' => $codigoProducto,
                    ]);
                }
            } catch (Exception $e) {
                Log::error('Error procesando fila completa', [
                    'fila' => $index + 1,
                    'error' => $e->getMessage(),
                ]);
                $errores++;
            }
        }

        Log::info('Importación completada', [
            'empresa' => $empresa,
            'productos_procesados' => $procesados,
            'precios_creados' => $creados,
            'precios_actualizados' => $actualizados,
            'errores' => $errores,
        ]);

        return [
            'procesados' => $procesados,
            'creados' => $creados,
            'actualizados' => $actualizados,
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
