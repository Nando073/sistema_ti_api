<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "API Soporte TI/ Modulo 2",
    version: "1.0.0",
    description: "API REST para la gestión de compras, cotizaciones, proveedores y más. Documentación completa de todos los endpoints."
)]
#[OA\Server(
    url: "https://sistematiapi-production.up.railway.app",
    description: "Servidor de producción"
)]
class Swagger
{
}