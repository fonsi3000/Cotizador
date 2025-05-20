<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;

class ListaPrecio extends Model
{
    use HasFactory;

    protected $table = 'listas_precios';

    protected $fillable = [
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'activo',
        'empresa', // 👈 Asegúrate de incluir este campo en fillable
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'activo' => 'boolean',
    ];

    /**
     * Relación: Productos asociados a esta lista de precios.
     */
    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(
            Producto::class,
            'producto_precio',
            'lista_precio_id',
            'codigo_producto'
        )
            ->withPivot('precio')
            ->withTimestamps();
    }

    /**
     * Scope para filtrar listas de precios activas.
     */
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para filtrar listas de precios vigentes.
     */
    public function scopeVigentes($query)
    {
        $hoy = now()->format('Y-m-d');

        return $query->where('fecha_inicio', '<=', $hoy)
            ->where(function ($query) use ($hoy) {
                $query->whereNull('fecha_fin')
                    ->orWhere('fecha_fin', '>=', $hoy);
            });
    }

    /**
     * Verifica si la lista de precios está vigente.
     */
    public function estaVigente()
    {
        $hoy = now()->format('Y-m-d');

        return $this->fecha_inicio <= $hoy &&
            ($this->fecha_fin === null || $this->fecha_fin >= $hoy);
    }

    /**
     * Booted: asigna automáticamente la empresa del usuario autenticado.
     */
    protected static function booted(): void
    {
        static::creating(function (ListaPrecio $listaPrecio) {
            if (Auth::check() && empty($listaPrecio->empresa)) {
                $listaPrecio->empresa = Auth::user()->empresa;
            }
        });
    }
}
