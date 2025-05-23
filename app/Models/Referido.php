<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Referido extends Model
{
    use HasFactory;

    protected $fillable = [
        // Referidor
        'correo_referidor',
        'documento_referidor',
        'nombre_referidor',
        'codigo_referidor',
        'referidor_validado',

        // Referido
        'nombre_referido',
        'documento_referido',
        'correo_referido',
        'codigo_referido',
        'referido_validado',

        // Proceso
        'estado',
        'codigo_venta',
        'sala_venta_id',
        'modificado_por_id',
        'vigencia', // <-- actualizado
    ];

    protected $casts = [
        'referidor_validado' => 'boolean',
        'referido_validado' => 'boolean',
        'vigencia' => 'date', // <-- actualizado
    ];

    /**
     * Asignar vigencia automática al crear.
     */
    protected static function booted(): void
    {
        static::creating(function ($referido) {
            $referido->vigencia = now()->addMonth(); // 1 mes desde la fecha de creación
        });
    }

    /**
     * Relación con la sala de ventas.
     */
    public function salaVenta(): BelongsTo
    {
        return $this->belongsTo(SalaVenta::class);
    }

    /**
     * Relación con el usuario que cambió el estado (asesor o administrador).
     */
    public function modificadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modificado_por_id');
    }

    /**
     * Devuelve true si el referido puede ser usado (validado y activo).
     */
    public function getEsValidoParaUsoAttribute(): bool
    {
        return $this->referidor_validado && $this->referido_validado && $this->estado === 'activo' && !$this->estaVencido();
    }

    /**
     * Verifica si el referido ya venció.
     */
    public function estaVencido(): bool
    {
        return $this->vigencia && $this->vigencia->isPast();
    }

    /**
     * Scope para referidos vencidos automáticamente.
     */
    public function scopeVencidos($query)
    {
        return $query->whereDate('vigencia', '<', now());
    }
}
