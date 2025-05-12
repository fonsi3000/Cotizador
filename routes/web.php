<?php

use Illuminate\Support\Facades\Route;
use App\Models\Cotizacion;

Route::get('/', function () {
    return redirect('/inicio');
});

Route::get('/cotizacion/tirilla/{cotizacion}', function (Cotizacion $cotizacion) {
    $cotizacion->load(['items.producto']);
    return view('Cotizacion.CotizacionTirilla', compact('cotizacion'));
})->name('cotizacion.tirilla');

// ✅ NUEVA RUTA: Servir PDF directamente desde Laravel
Route::get('/cotizaciones/pdf/{cotizacion}', function (Cotizacion $cotizacion) {
    $path = storage_path("app/public/cotizaciones/cotizacion-{$cotizacion->id}.pdf");

    if (!file_exists($path)) {
        abort(404, 'Archivo no encontrado.');
    }

    return response()->file($path, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="cotizacion-' . $cotizacion->id . '.pdf"',
    ]);
})->name('cotizacion.pdf');
