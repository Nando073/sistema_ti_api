<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return '¡API Soporte TI funcionando correctamente! Visita /api-docs para ver la documentación.';
});

// Ruta para redirigir /api-docs a la documentación Swagger
Route::get('/api-docs', function () {
    return redirect('/docs/index.html');
});
