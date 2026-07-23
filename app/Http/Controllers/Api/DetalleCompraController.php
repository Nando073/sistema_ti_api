<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetalleCompra;
use App\Http\Requests\DetalleCompraRequest;
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
            // Solo mostrar detalles activos (estado = 1) con sus relaciones
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

    #[OA\Post(
        path: "/api/detalles-compra",
        tags: ["Detalles de Compra"],
        summary: "Crear un detalle de compra",
        description: "Registra un nuevo detalle de compra en la base de datos. Por defecto, estado = 1 (activo).",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "id_compra", type: "integer", example: 1),
                    new OA\Property(property: "id_repuesto", type: "integer", example: 1),
                    new OA\Property(property: "precio", type: "number", format: "float", example: 150.50),
                    new OA\Property(property: "cantidad", type: "integer", example: 3),
                    new OA\Property(property: "sub_total", type: "number", format: "float", example: 451.50),
                    new OA\Property(property: "estado", type: "integer", example: 1, default: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Detalle de compra creado correctamente"
            ),
            new OA\Response(
                response: 422,
                description: "Error de validación"
            ),
            new OA\Response(
                response: 500,
                description: "Error interno del servidor"
            )
        ]
    )]
    public function store(DetalleCompraRequest $request)
    {
        try {
            // Asegurar que el detalle se crea con estado = 1 (activo)
            $data = $request->validated();
            $data['estado'] = 1; // Forzar estado activo al crear

            $detalle = DetalleCompra::create($data);

            // Cargar la relación para la respuesta
            $detalle->load('compra');

            return response()->json([
                'success' => true,
                'message' => 'Detalle de compra registrado correctamente.',
                'data' => $detalle
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el detalle de compra.',
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
                schema: new OA\Schema(
                    type: "integer"
                )
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
            // Buscar detalle activo por ID con sus relaciones
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

    #[OA\Put(
        path: "/api/detalles-compra/{id}",
        tags: ["Detalles de Compra"],
        summary: "Actualizar detalle de compra",
        description: "Actualiza la información de un detalle de compra existente. Solo permite actualizar detalles activos.",
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID del detalle de compra",
                in: "path",
                required: true,
                schema: new OA\Schema(
                    type: "integer"
                )
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "id_compra", type: "integer", example: 1),
                    new OA\Property(property: "id_repuesto", type: "integer", example: 1),
                    new OA\Property(property: "precio", type: "number", format: "float", example: 175.75),
                    new OA\Property(property: "cantidad", type: "integer", example: 5),
                    new OA\Property(property: "sub_total", type: "number", format: "float", example: 878.75),
                    new OA\Property(property: "estado", type: "integer", example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Detalle de compra actualizado correctamente"
            ),
            new OA\Response(
                response: 404,
                description: "Detalle de compra no encontrado o inactivo"
            ),
            new OA\Response(
                response: 422,
                description: "Error de validación"
            ),
            new OA\Response(
                response: 500,
                description: "Error interno del servidor"
            )
        ]
    )]
    public function update(DetalleCompraRequest $request, $id)
    {
        try {
            // Buscar detalle activo por ID
            $detalle = DetalleCompra::where('estado', 1)->find($id);

            if (!$detalle) {
                return response()->json([
                    'success' => false,
                    'message' => 'Detalle de compra no encontrado o inactivo.'
                ], 404);
            }

            // Actualizar el detalle
            $detalle->update($request->validated());

            // Recargar el detalle con sus relaciones
            $detalle->refresh();
            $detalle->load('compra');

            return response()->json([
                'success' => true,
                'message' => 'Detalle de compra actualizado correctamente.',
                'data' => $detalle
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el detalle de compra.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Delete(
        path: "/api/detalles-compra/{id}",
        tags: ["Detalles de Compra"],
        summary: "Eliminar detalle de compra (lógico)",
        description: "Cambia el estado del detalle de compra a 0 (inactivo) en lugar de eliminarlo físicamente.",
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID del detalle de compra",
                in: "path",
                required: true,
                schema: new OA\Schema(
                    type: "integer"
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Detalle de compra eliminado lógicamente (estado = 0)"
            ),
            new OA\Response(
                response: 404,
                description: "Detalle de compra no encontrado o ya inactivo"
            ),
            new OA\Response(
                response: 500,
                description: "Error interno del servidor"
            )
        ]
    )]
    public function destroy($id)
    {
        try {
            // Buscar detalle activo por ID
            $detalle = DetalleCompra::where('estado', 1)->find($id);

            if (!$detalle) {
                return response()->json([
                    'success' => false,
                    'message' => 'Detalle de compra no encontrado o ya inactivo.'
                ], 404);
            }

            // Eliminación lógica: cambiar estado a 0
            $detalle->update(['estado' => 0]);

            return response()->json([
                'success' => true,
                'message' => 'Detalle de compra eliminado correctamente (estado = 0).'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el detalle de compra.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Método adicional para reactivar detalles de compra
     */
    #[OA\Patch(
        path: "/api/detalles-compra/{id}/reactivar",
        tags: ["Detalles de Compra"],
        summary: "Reactivar detalle de compra",
        description: "Cambia el estado del detalle de compra a 1 (activo).",
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID del detalle de compra",
                in: "path",
                required: true,
                schema: new OA\Schema(
                    type: "integer"
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Detalle de compra reactivado correctamente"
            ),
            new OA\Response(
                response: 404,
                description: "Detalle de compra no encontrado"
            ),
            new OA\Response(
                response: 500,
                description: "Error interno del servidor"
            )
        ]
    )]
    public function reactivar($id)
    {
        try {
            $detalle = DetalleCompra::find($id);

            if (!$detalle) {
                return response()->json([
                    'success' => false,
                    'message' => 'Detalle de compra no encontrado.'
                ], 404);
            }

            // Reactivar: cambiar estado a 1
            $detalle->update(['estado' => 1]);

            // Cargar relaciones
            $detalle->load('compra');

            return response()->json([
                'success' => true,
                'message' => 'Detalle de compra reactivado correctamente.',
                'data' => $detalle
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al reactivar el detalle de compra.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Método para obtener detalles por compra
     */
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
                schema: new OA\Schema(
                    type: "integer"
                )
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