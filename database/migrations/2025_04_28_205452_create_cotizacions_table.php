<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_cliente');
            $table->string('documento_cliente');
            $table->string('numero_celular_cliente');
            $table->string('correo_electronico_cliente')->nullable();
            $table->foreignId('usuario_id')->constrained('users');
            $table->timestamp('fecha_cotizacion')->useCurrent();
            $table->decimal('total_cotizacion', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cotizaciones');
    }
};
