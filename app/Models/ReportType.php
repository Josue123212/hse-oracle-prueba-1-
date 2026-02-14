<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportType extends Model
{
    protected $fillable = [
        'nombre',
        'descripcion',
        'frecuencia_requerida',
        'es_obligatorio',
    ];

    protected $casts = [
        'es_obligatorio' => 'boolean',
    ];
}
