<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class CotizacionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cotizacion_id',
        'producto_id',
        'lista_precio_id',
        'producto_codigo',
        'producto_nombre',
        'producto_imagen',
        'precio_unitario',
        'cantidad',
        'subtotal',
    ];

    protected static function booted()
    {
        static::creating(function ($item) {
            Log::info('Interceptando creación de CotizacionItem', ['producto_id' => $item->producto_id]);

            if (!$item->producto_codigo) {
                $producto = \App\Models\Producto::find($item->producto_id);

                if ($producto) {
                    $item->producto_codigo = $producto->codigo ?? '';
                    $item->producto_nombre = $producto->descripcion ?? '';
                    $item->producto_imagen = $producto->imagen ?? null;

                    Log::info('Datos completados en el evento creating', [
                        'producto_codigo' => $item->producto_codigo,
                        'producto_nombre' => $item->producto_nombre,
                        'producto_imagen' => $item->producto_imagen,
                    ]);
                }
            }
        });
    }

    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function listaPrecio()
    {
        return $this->belongsTo(ListaPrecio::class);
    }
}
