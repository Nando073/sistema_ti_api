<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleCotizacion extends Model
{
    protected $table = 'detalle_cotizaciones';
    protected $primaryKey = 'id_detalle_cotizacion';

    protected $fillable = [
        'id_cotizacion',
        'id_equipo',
        'id_repuesto',
        'precio',
        'cantidad',
        'descuento',
        'estado'
    ];

    public function cotizacion()
    {
        return $this->belongsTo(
            Cotizacion::class,
            'id_cotizacion',
            'id_cotizacion'
        );
    }
}
