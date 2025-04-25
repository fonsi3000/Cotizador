<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('producto_precio', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_producto');
            $table->foreignId('lista_precio_id')->constrained('listas_precios')->onDelete('cascade');
            $table->decimal('precio', 15, 2);
            $table->timestamps();

            // Restricción única para evitar duplicados
            $table->unique(['codigo_producto', 'lista_precio_id']);

            // Clave foránea al campo codigo de la tabla productos
            $table->foreign('codigo_producto')
                ->references('codigo')
                ->on('productos')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('producto_precio');
    }
};
