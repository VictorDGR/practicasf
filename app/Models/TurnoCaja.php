<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TurnoCaja extends Model
{
    protected $table = 'turnos_caja';

    protected $fillable = [
        'arqueo_caja_id',
        'usuario_id',
        'monto_inicial',
        'fecha_inicio',
        'monto_final',
        'fecha_fin',
        'estado',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    public function arqueoCaja()
    {
        return $this->belongsTo(ArqueoCaja::class, 'arqueo_caja_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function cobros()
    {
        return $this->hasMany(Cobro::class, 'turno_caja_id');
    }

    public function totalRecaudado(): float
    {
        return (float) $this->cobros()->where('estado', 'pagado')->sum('monto_total');
    }
}
