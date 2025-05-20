<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'codigo',
        'descripcion',
        'imagen',
        'activo',
        'empresa', // Campo agregado
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function listasPrecios(): BelongsToMany
    {
        return $this->belongsToMany(
            ListaPrecio::class,
            'producto_precio',
            'codigo_producto',
            'lista_precio_id'
        )->withPivot('precio')->withTimestamps();
    }

    public function getPrecioEnLista($listaPrecioId)
    {
        $relacion = $this->listasPrecios()->where('lista_precio_id', $listaPrecioId)->first();

        return $relacion ? $relacion->pivot->precio : null;
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    protected static function booted(): void
    {
        static::creating(function (Producto $producto) {
            if (Auth::check() && empty($producto->empresa)) {
                $producto->empresa = Auth::user()->empresa;
            }
        });
    }
}
