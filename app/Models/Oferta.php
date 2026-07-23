<?php

namespace App\Models;
use App\Models\Cotizacion;

use Illuminate\Database\Eloquent\Model;

class Oferta extends Model
{
    protected $table = 'ofertas';
    protected $primaryKey = 'id_oferta';

    protected $fillable = [
        'of_nombre',
        'descripcion',
        'porcentaje',
        'fecha_inc',
        'fecha_fin',
        'estado'
    ];

    public function cotizaciones()
    {
        return $this->hasMany(
            Cotizacion::class,
            'id_oferta',
            'id_oferta'
        );
    }
}
