<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArqueoCaja extends Model
{
    protected $table = 'arqueos_caja';

    protected $fillable = [
        'caja_id',
        'usuario_id',
        'monto_apertura',
        'fecha_apertura',
        'monto_cierre_sistema',
        'monto_cierre_fisico',
        'diferencia',
        'estado',
        'fecha_cierre',
    ];

    protected $casts = [
        'fecha_apertura' => 'datetime',
        'fecha_cierre' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function caja()
    {
        return $this->belongsTo(Caja::class, 'caja_id');
    }

    public function turnos()
    {
        return $this->hasMany(TurnoCaja::class, 'arqueo_caja_id');
    }

    public function turnoAbierto()
    {
        return $this->turnos()->where('estado', 'abierto')->latest('fecha_inicio')->first();
    }

    public function cobros()
    {
        return $this->hasMany(Cobro::class, 'arqueo_caja_id');
    }

    public function totalRecaudado(): float
    {
        return (float) $this->cobros()->where('estado', 'pagado')->sum('monto_total');
    }

    public function montoSistemaCalculado(): float
    {
        return (float) $this->monto_apertura + $this->totalRecaudado();
    }
}
