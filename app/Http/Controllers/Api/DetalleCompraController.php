<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetalleCompra;
use Exception;
use OpenApi\Attributes as OA;

class DetalleCompraController extends Controller
{
    #[OA\Get(
        path: "/api/detalles-compra",
        tags: ["Detalles de Compra"],
        summary: "Obtener todos los detalles de compra activos",
        description: "Devuelve una lista completa de detalles de compra activos (estado = 1) con sus relaciones.",
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de detalles de compra obtenida correctamente"
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
            $detalles = DetalleCompra::with(['compra'])
                ->where('estado', 1)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $detalles
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los detalles de compra.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Get(
        path: "/api/detalles-compra/{id}",
        tags: ["Detalles de Compra"],
        summary: "Obtener detalle de compra por ID",
        description: "Busca un detalle de compra específico mediante su identificador. Solo muestra detalles activos.",
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID del detalle de compra",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Detalle de compra encontrado"
            ),
            new OA\Response(
                response: 404,
                description: "Detalle de compra no encontrado o inactivo"
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
            $detalle = DetalleCompra::with(['compra'])
                ->where('estado', 1)
                ->find($id);

            if (!$detalle) {
                return response()->json([
                    'success' => false,
                    'message' => 'Detalle de compra no encontrado o inactivo.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $detalle
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar el detalle de compra.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Get(
        path: "/api/detalles-compra/compra/{id_compra}",
        tags: ["Detalles de Compra"],
        summary: "Obtener detalles por compra",
        description: "Devuelve todos los detalles activos de una compra específica.",
        parameters: [
            new OA\Parameter(
                name: "id_compra",
                description: "ID de la compra",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de detalles de la compra"
            ),
            new OA\Response(
                response: 404,
                description: "Compra sin detalles activos"
            ),
            new OA\Response(
                response: 500,
                description: "Error interno del servidor"
            )
        ]
    )]
    public function getByCompra($id_compra)
    {
        try {
            $detalles = DetalleCompra::with(['compra'])
                ->where('estado', 1)
                ->where('id_compra', $id_compra)
                ->get();

            if ($detalles->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron detalles activos para esta compra.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $detalles
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los detalles de la compra.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}