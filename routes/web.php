<?php

use Illuminate\Support\Facades\Route;
use App\Models\Cotizacion;

// Redireccionar raíz al panel "inicio"
Route::get('/', function () {
    return redirect('/inicio');
});

// Vista tipo tirilla de una cotización
Route::get('/cotizacion/tirilla/{cotizacion}', function (Cotizacion $cotizacion) {
    $cotizacion->load(['items.producto']);
    return view('Cotizacion.CotizacionTirilla', compact('cotizacion'));
})->name('cotizacion.tirilla');
