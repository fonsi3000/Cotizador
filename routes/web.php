<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    //return view('welcome');
    return redirect('/inicio');
});

Route::get('/cotizacion/tirilla/{cotizacion}', function (\App\Models\Cotizacion $cotizacion) {
    $cotizacion->load(['items.producto']);
    return view('Cotizacion.CotizacionTirilla', compact('cotizacion'));
})->name('cotizacion.tirilla');
