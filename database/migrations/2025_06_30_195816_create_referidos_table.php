<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('referidos', function (Blueprint $table) {
            $table->id();

            // Datos del referidor
            $table->string('correo_referidor')->nullable();
            $table->string('documento_referidor')->nullable();
            $table->string('nombre_referidor')->nullable();
            $table->string('codigo_referidor')->nullable();
            $table->boolean('referidor_validado')->default(false);

            // Datos del referido
            $table->string('nombre_referido')->nullable();
            $table->string('documento_referido')->nullable();
            $table->string('correo_referido')->nullable();
            $table->string('codigo_referido')->nullable();
            $table->boolean('referido_validado')->default(false);

            // Vigencia fija de un mes desde la creación
            $table->date('vigencia')->nullable();

            // Estado del proceso
            $table->enum('estado', ['pendiente', 'activo', 'usado', 'vencido'])->default('pendiente');
            $table->string('codigo_venta')->nullable();
            $table->foreignId('sala_venta_id')->nullable()->constrained('sala_ventas')->nullOnDelete();

            // Usuario que cambió el estado
            $table->foreignId('modificado_por_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referidos');
    }
};
