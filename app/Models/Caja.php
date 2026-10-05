<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    protected $table = 'cajas';

    protected $fillable = [
        'nombre',
        'estado',
    ];

    public function arqueos()
    {
        return $this->hasMany(ArqueoCaja::class, 'caja_id');
    }

    public function arqueoAbierto()
    {
        return $this->arqueos()->where('estado', 'abierto')->latest('fecha_apertura')->first();
    }
}
