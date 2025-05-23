<?php

use Illuminate\Support\Facades\Route;
use App\Models\Cotizacion;
use App\Http\Controllers\ReferidoPublicController;

// Redireccionar raíz al panel "inicio"
Route::get('/', function () {
    return redirect('/inicio');
});

// Vista tipo tirilla de una cotización
Route::get('/cotizacion/tirilla/{cotizacion}', function (Cotizacion $cotizacion) {
    $cotizacion->load(['items.producto']);
    return view('Cotizacion.CotizacionTirilla', compact('cotizacion'));
})->name('cotizacion.tirilla');

// ============================
// Registro público de referidos
// ============================

// Paso 1: Ingresar correo del referidor

Route::post('/referir', [ReferidoPublicController::class, 'sendReferidorCode'])->name('referido.public.send-code');

// Paso 2: Validar el código que le llega al referidor
Route::get('/referir/validar/{id}', [ReferidoPublicController::class, 'showCodeForm'])->name('referido.validar-codigo');
Route::post('/referir/validar/{id}', [ReferidoPublicController::class, 'validateReferidorCode'])->name('referido.validar-codigo.post');

// Paso 3: Registrar los datos del referido
Route::post('/referir/completar/{id}', [ReferidoPublicController::class, 'storeReferido'])->name('referido.public.store');

// Éxito
Route::get('/referido-verificado', [ReferidoPublicController::class, 'success'])->name('referido.public.success');
