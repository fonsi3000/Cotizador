<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cotizacion extends Model
{
    use HasFactory;

    protected $table = 'cotizaciones';

    protected $fillable = [
        'nombre_cliente',
        'documento_cliente',
        'numero_celular_cliente',
        'correo_electronico_cliente',
        'usuario_id',
        'sala_venta_id',
        'fecha_cotizacion',
        'total_cotizacion',
    ];

    public function items()
    {
        return $this->hasMany(CotizacionItem::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function salaVenta()
    {
        return $this->belongsTo(SalaVenta::class, 'sala_venta_id');
    }
}
