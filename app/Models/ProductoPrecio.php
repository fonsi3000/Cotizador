<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductoPrecio extends Model
{
    use HasFactory;

    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'producto_precio';

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'codigo_producto',
        'lista_precio_id',
        'precio',
    ];

    /**
     * Los atributos que deben ser convertidos.
     *
     * @var array
     */
    protected $casts = [
        'precio' => 'decimal:2',
    ];

    /**
     * Obtiene el producto asociado.
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'codigo_producto', 'codigo');
    }

    /**
     * Obtiene la lista de precios asociada.
     */
    public function listaPrecio(): BelongsTo
    {
        return $this->belongsTo(ListaPrecio::class);
    }
}
