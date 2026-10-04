<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_pago_adicionales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('detalle_pago_id')->constrained('detalle_pagos');
            $table->foreignId('item_adicional_id')->constrained('item_adicionales');
            $table->decimal('monto', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_pago_adicionales');
    }
};
