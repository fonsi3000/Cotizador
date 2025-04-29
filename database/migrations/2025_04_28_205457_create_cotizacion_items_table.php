<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cotizacion_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cotizacion_id')->constrained('cotizaciones')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('lista_precio_id')->constrained('listas_precios');
            $table->string('producto_codigo');
            $table->string('producto_nombre');
            $table->string('producto_imagen')->nullable();
            $table->decimal('precio_unitario', 15, 2);
            $table->integer('cantidad');
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cotizacion_items');
    }
};
