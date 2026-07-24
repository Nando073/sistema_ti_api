<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetalleCotizacion;
use Exception;
use OpenApi\Attributes as OA;

class DetalleCotizacionController extends Controller
{
    #[OA\Get(
        path: "/api/detalles-cotizacion",
        tags: ["Detalles de Cotización"],
        summary: "Obtener todos los detalles de cotización activos",
        description: "Devuelve una lista completa de detalles de cotización activos (estado = 1) con sus relaciones.",
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de detalles de cotización obtenida correctamente"
            ),
            new OA\Response(
                response: 500,
                description: "Error interno del servidor"
            )
        ]
    )]
    public function index()
    {
        try {
            $detalles = DetalleCotizacion::with(['cotizacion'])
                ->where('estado', 1)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $detalles
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los detalles de cotización.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Get(
        path: "/api/detalles-cotizacion/{id}",
        tags: ["Detalles de Cotización"],
        summary: "Obtener detalle de cotización por ID",
        description: "Busca un detalle de cotización específico mediante su identificador. Solo muestra detalles activos.",
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID del detalle de cotización",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Detalle de cotización encontrado"
            ),
            new OA\Response(
                response: 404,
                description: "Detalle de cotización no encontrado o inactivo"
            ),
            new OA\Response(
                response: 500,
                description: "Error interno del servidor"
            )
        ]
    )]
    public function show($id)
    {
        try {
            $detalle = DetalleCotizacion::with(['cotizacion'])
                ->where('estado', 1)
                ->find($id);

            if (!$detalle) {
                return response()->json([
                    'success' => false,
                    'message' => 'Detalle de cotización no encontrado o inactivo.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $detalle
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar el detalle de cotización.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Get(
        path: "/api/detalles-cotizacion/cotizacion/{id_cotizacion}",
        tags: ["Detalles de Cotización"],
        summary: "Obtener detalles por cotización",
        description: "Devuelve todos los detalles activos de una cotización específica.",
        parameters: [
            new OA\Parameter(
                name: "id_cotizacion",
                description: "ID de la cotización",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de detalles de la cotización"
            ),
            new OA\Response(
                response: 404,
                description: "Cotización sin detalles activos"
            ),
            new OA\Response(
                response: 500,
                description: "Error interno del servidor"
            )
        ]
    )]
    public function getByCotizacion($id_cotizacion)
    {
        try {
            $detalles = DetalleCotizacion::with(['cotizacion'])
                ->where('estado', 1)
                ->where('id_cotizacion', $id_cotizacion)
                ->get();

            if ($detalles->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron detalles activos para esta cotización.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $detalles
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los detalles de la cotización.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}