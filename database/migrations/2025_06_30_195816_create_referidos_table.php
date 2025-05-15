<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('referidos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // quien refiere
            $table->string('referido'); // nombre del referido
            $table->string('documento'); // documento del referido
            $table->foreignId('sala_venta_id')->constrained('sala_ventas')->cascadeOnDelete();
            $table->date('vigencia'); // fecha de vigencia
            $table->enum('estado', ['activo', 'usado', 'vencido'])->default('activo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referidos');
    }
};
