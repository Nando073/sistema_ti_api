<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ProveedorController;
use App\Http\Controllers\Api\CompraController;
use App\Http\Controllers\Api\DetalleCompraController;
use App\Http\Controllers\Api\OfertaController;
use App\Http\Controllers\Api\CotizacionController;
use App\Http\Controllers\Api\DetalleCotizacionController;


// PROVEEDORES
Route::apiResource('proveedores', ProveedorController::class)
    ->parameters([
        'proveedores' => 'id'
    ]);
Route::patch('proveedores/{id}/reactivar', [ProveedorController::class, 'reactivar']);

// COMPRAS
Route::apiResource('compras', CompraController::class)->parameters(['compras' => 'id']);
Route::patch('compras/{id}/reactivar', [CompraController::class, 'reactivar']);
Route::get('compras/proveedor/{id_proveedor}', [CompraController::class, 'getByProveedor']);

// DETALLE COMPRAS
Route::apiResource('detalle-compras', DetalleCompraController::class)->parameters(['detalle-compras' => 'id']);
Route::get('detalle-compras/compra/{id_compra}', [DetalleCompraController::class, 'getByCompra']);

// OFERTAS
Route::apiResource('ofertas', OfertaController::class)->parameters(['ofertas' => 'id']);
Route::patch('ofertas/{id}/reactivar', [OfertaController::class, 'reactivar']);

// COTIZACIONES
Route::apiResource('cotizaciones', CotizacionController::class)->parameters(['cotizaciones' => 'id']);
Route::patch('cotizaciones/{id}/reactivar', [CotizacionController::class, 'reactivar']);
Route::get('cotizaciones/oferta/{id_oferta}', [CotizacionController::class, 'getByOferta']);
Route::get('cotizaciones/cliente/{id_cliente}', [CotizacionController::class, 'getByCliente']);

// DETALLE COTIZACIONES
Route::apiResource('detalle-cotizaciones', DetalleCotizacionController::class)->parameters(['detalle-cotizaciones' => 'id']);
Route::get('detalle-cotizaciones/cotizacion/{id_cotizacion}', [DetalleCotizacionController::class, 'getByCotizacion']);