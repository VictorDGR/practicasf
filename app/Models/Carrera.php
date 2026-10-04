<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carrera extends Model
{
    protected $table = 'carreras';

    protected $fillable = [
        'nombre',
        'estado',
    ];

    public function estudiantes()
    {
        return $this->hasMany(Estudiante::class, 'carrera_id');
    }

    public function scopeActivas($query)
    {
        return $query->where('estado', 'activo');
    }
}
