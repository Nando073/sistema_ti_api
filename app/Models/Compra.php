<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    protected $table = 'compras';
    protected $primaryKey = 'id_compra';

    protected $fillable = [
        'n_documento',
        'id_proveedor',
        'id_usuario',
        'total_compra',
        'forma_pago',
        'observacion',
        'fecha',
        'estado'
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor', 'id_proveedor');
    }

    public function detalles()
    {
        return $this->hasMany(
            DetalleCompra::class,
            'id_compra',
            'id_compra'
        );
    }
}