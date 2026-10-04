<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetallePagoAdicional extends Model
{
    protected $table = 'detalle_pago_adicionales';

    protected $fillable = [
        'detalle_pago_id',
        'item_adicional_id',
        'monto',
    ];

    public function detallePago()
    {
        return $this->belongsTo(DetallePago::class, 'detalle_pago_id');
    }

    public function itemAdicional()
    {
        return $this->belongsTo(ItemAdicional::class, 'item_adicional_id');
    }
}
