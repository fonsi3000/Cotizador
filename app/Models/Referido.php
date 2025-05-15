<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Referido extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'referido',
        'documento',
        'sala_venta_id',
        'vigencia',
        'estado',
    ];

    public function salaVenta()
    {
        return $this->belongsTo(SalaVenta::class);
    }
}
