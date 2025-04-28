<?php

namespace App\Imports;

use App\Models\Producto;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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

                // Si el código existe, omite el registro
                if (isset($row['codigo']) && Producto::where('codigo', $row['codigo'])->exists()) {
                    Log::info("Omitiendo producto con código existente: {$row['codigo']}");
                    continue;
                }

                // Si no hay ningún dato en la fila, omite el registro
                if (empty(array_filter($row->toArray()))) {
                    Log::info("Omitiendo fila vacía");
                    continue;
                }

                // Preparar datos para crear el producto
                $data = [
                    'codigo' => $row['codigo'] ?? null,
                    'descripcion' => $row['descripcion'] ?? $row['codigo'] ?? 'Sin descripción',
                    'activo' => isset($row['activo']) ? (bool)$row['activo'] : true,
                    'imagen' => $row['imagen'] ?? null
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
