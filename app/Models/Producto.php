<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Producto extends Model
{
    use HasFactory;

    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'productos';

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'codigo',
        'descripcion',
        'imagen',
        'activo',
    ];

    /**
     * Los atributos que deben ser convertidos.
     *
     * @var array
     */
    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Obtiene las listas de precios asociadas al producto.
     */
    public function listasPrecios(): BelongsToMany
    {
        return $this->belongsToMany(ListaPrecio::class, 'producto_precio')
            ->withPivot('precio')
            ->withTimestamps();
    }

    /**
     * Obtiene el precio del producto para una lista de precios específica.
     *
     * @param int $listaPrecioId
     * @return float|null
     */
    public function getPrecioEnLista($listaPrecioId)
    {
        $relacion = $this->listasPrecios()->where('lista_precio_id', $listaPrecioId)->first();

        return $relacion ? $relacion->pivot->precio : null;
    }

    /**
     * Scope para filtrar productos activos.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
