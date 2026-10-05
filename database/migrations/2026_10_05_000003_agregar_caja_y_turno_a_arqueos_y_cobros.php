<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('arqueos_caja', function (Blueprint $table) {
            $table->foreignId('caja_id')->nullable()->after('id')->constrained('cajas');
        });

        Schema::table('cobros', function (Blueprint $table) {
            $table->foreignId('turno_caja_id')->nullable()->after('arqueo_caja_id')->constrained('turnos_caja');
        });

        $arqueos = DB::table('arqueos_caja')->get();

        if ($arqueos->isEmpty()) {
            return;
        }

        $cajaId = DB::table('cajas')->insertGetId([
            'nombre' => 'Caja 1',
            'estado' => 'activo',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($arqueos as $arqueo) {
            $turnoId = DB::table('turnos_caja')->insertGetId([
                'arqueo_caja_id' => $arqueo->id,
                'usuario_id' => $arqueo->usuario_id,
                'monto_inicial' => $arqueo->monto_apertura,
                'fecha_inicio' => $arqueo->fecha_apertura,
                'monto_final' => $arqueo->monto_cierre_sistema,
                'fecha_fin' => $arqueo->fecha_cierre,
                'estado' => $arqueo->estado,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('arqueos_caja')->where('id', $arqueo->id)->update(['caja_id' => $cajaId]);
            DB::table('cobros')->where('arqueo_caja_id', $arqueo->id)->update(['turno_caja_id' => $turnoId]);
        }
    }

    public function down(): void
    {
        Schema::table('cobros', function (Blueprint $table) {
            $table->dropConstrainedForeignId('turno_caja_id');
        });

        Schema::table('arqueos_caja', function (Blueprint $table) {
            $table->dropConstrainedForeignId('caja_id');
        });
    }
};
