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
// COMPRAS
Route::apiResource('compras', CompraController::class);

// DETALLE COMPRAS
Route::apiResource('detalle-compras', DetalleCompraController::class);

// OFERTAS
Route::apiResource('ofertas', OfertaController::class);

// COTIZACIONES
Route::apiResource('cotizaciones', CotizacionController::class);

// DETALLE COTIZACIONES
Route::apiResource('detalle-cotizaciones', DetalleCotizacionController::class);