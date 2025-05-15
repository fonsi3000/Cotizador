<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalaVenta extends Model
{
    use HasFactory;

    protected $table = 'sala_ventas';

    protected $fillable = [
        'nombre',
        'telefono',
        'direccion',
        'empresa',
    ];
}
