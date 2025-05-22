<?php

namespace App\Imports;

use App\Models\Producto;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProductoImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        Log::info('Iniciando importación de productos', ['total_rows' => count($rows)]);

        if (count($rows) > 0) {
            Log::info('Estructura de la primera fila', ['row_keys' => array_keys($rows[0]->toArray())]);
        }

        DB::beginTransaction();
        try {
            $totalImportados = 0;

            foreach ($rows as $index => $row) {
                Log::info("Procesando fila #{$index}", ['fila' => json_encode($row)]);

                // Si la fila está completamente vacía, omitir
                if (empty(array_filter($row->toArray()))) {
                    Log::info("Omitiendo fila vacía");
                    continue;
                }

                // Determinar la empresa (si viene en Excel o usar la del usuario autenticado)
                $empresa = $row['empresa'] ?? Auth::user()->empresa ?? null;

                if (!$empresa) {
                    Log::warning("Fila #{$index} sin empresa definida, se omite.");
                    continue;
                }

                // Verificar si ya existe un producto con mismo código y misma empresa
                $existe = Producto::where('codigo', $row['codigo'] ?? null)
                    ->where('empresa', $empresa)
                    ->exists();

                if ($existe) {
                    Log::info("Omitiendo producto duplicado: {$row['codigo']} en empresa {$empresa}");
                    continue;
                }

                // Preparar datos para crear el producto
                $data = [
                    'codigo' => $row['codigo'] ?? null,
                    'descripcion' => $row['descripcion'] ?? $row['codigo'] ?? 'Sin descripción',
                    'activo' => isset($row['activo']) ? (bool)$row['activo'] : true,
                    'imagen' => $row['imagen'] ?? null,
                    'empresa' => $empresa,
                ];

                Log::info("Creando producto", $data);

                Producto::create($data);
                $totalImportados++;
            }

            Log::info("Importación completada: {$totalImportados} productos importados");
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en importación: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
