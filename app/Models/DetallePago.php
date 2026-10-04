<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetallePago extends Model
{
    protected $table = 'detalle_pagos';

    protected $fillable = [
        'cobro_id',
        'item_id',
        'cantidad',
        'subtotal',
    ];

    public function cobro()
    {
        return $this->belongsTo(Cobro::class, 'cobro_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function adicionales()
    {
        return $this->hasMany(DetallePagoAdicional::class, 'detalle_pago_id');
    }

    public function montoItem(): float
    {
        return (float) $this->subtotal - (float) $this->adicionales->sum('monto');
    }
}
