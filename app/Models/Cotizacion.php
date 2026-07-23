<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cotizacion extends Model
{
    protected $table = 'cotizaciones';
    protected $primaryKey = 'id_cotizacion';

    protected $fillable = [
        'id_orden',
        'id_oferta',
        'id_cliente',
        'id_usuario',
        'fecha_cad',
        'monto_total',
        'descuento',
        'estado'
    ];

    public function oferta()
    {
        return $this->belongsTo(
            Oferta::class,
            'id_oferta',
            'id_oferta'
        );
    }

    public function detalles()
    {
        return $this->hasMany(
            DetalleCotizacion::class,
            'id_cotizacion',
            'id_cotizacion'
        );
    }
}