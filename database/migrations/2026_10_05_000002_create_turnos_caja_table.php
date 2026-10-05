<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turnos_caja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('arqueo_caja_id')->constrained('arqueos_caja');
            $table->foreignId('usuario_id')->constrained('usuarios');
            $table->decimal('monto_inicial', 10, 2);
            $table->dateTime('fecha_inicio');
            $table->decimal('monto_final', 10, 2)->nullable();
            $table->dateTime('fecha_fin')->nullable();
            $table->enum('estado', ['abierto', 'cerrado'])->default('abierto');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turnos_caja');
    }
};
