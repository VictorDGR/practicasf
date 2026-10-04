<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemAdicional extends Model
{
    protected $table = 'item_adicionales';

    protected $fillable = [
        'item_id',
        'nombre',
        'monto',
        'estado',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
