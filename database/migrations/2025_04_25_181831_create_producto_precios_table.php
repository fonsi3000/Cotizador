<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producto_precios', function (Blueprint $table) {
            $table->id();

            $table->string('codigo_producto');

            $table->foreignId('lista_precio_id')->constrained('listas_precios')->onDelete('cascade');

            $table->decimal('precio', 15, 2);
            $table->timestamps();

            $table->unique(['codigo_producto', 'lista_precio_id'], 'codigo_producto_lista_precio_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_precios');
    }
};
