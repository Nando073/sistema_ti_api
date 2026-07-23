<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Compra;


class DetalleCompra extends Model
{
    protected $table = 'detalle_compras';
    protected $primaryKey = 'id_detalle_compra';

    protected $fillable = [
        'precio',
        'sub_total',
        'cantidad',
        'estado',
        'id_compra',
        'id_repuesto'
    ];

    public function compra()
    {
        return $this->belongsTo(
            Compra::class,
            'id_compra',
            'id_compra'
        );
    }

//   public function repuesto()
//     {
//         return $this->belongsTo(
//             Repuesto::class,
//             'id_repuesto',
//             'id_repuesto'
//         );
//     }

  
}