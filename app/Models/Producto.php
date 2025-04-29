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
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Relación: Listas de precios asociadas al producto.
     *
     * Se define como una relación de muchos a muchos con la tabla pivote
     * 'producto_precio', usando 'codigo_producto' como clave foránea en
     * productos y 'lista_precio_id' como clave foránea en lista_precios.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function listasPrecios(): BelongsToMany
    {
        return $this->belongsToMany(
            ListaPrecio::class,         // Modelo relacionado
            'producto_precio',           // Tabla pivote
            'codigo_producto',           // Foreign key de este modelo (Producto)
            'lista_precio_id'             // Foreign key del modelo relacionado (ListaPrecio)
        )
            ->withPivot('precio')             // Incluir campo 'precio' de la tabla pivote
            ->withTimestamps();               // Incluir timestamps en la tabla pivote
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
