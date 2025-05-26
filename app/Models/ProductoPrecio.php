<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class ProductoPrecio extends Model
{
    use HasFactory;

    protected $table = 'producto_precios';

    protected $fillable = [
        'codigo_producto',
        'lista_precio_id',
        'empresa', // 👈 Se asigna automáticamente si no viene
        'precio',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'codigo_producto', 'codigo');
    }

    public function listaPrecio(): BelongsTo
    {
        return $this->belongsTo(ListaPrecio::class);
    }

    /**
     * Asignar automáticamente la empresa desde el usuario autenticado
     */
    protected static function booted(): void
    {
        static::creating(function (ProductoPrecio $precio) {
            if (Auth::check() && empty($precio->empresa)) {
                $precio->empresa = Auth::user()->empresa;
            }
        });
    }
}
