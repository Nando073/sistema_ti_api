<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Compra;
use App\Http\Requests\CompraRequest;
use Exception;
use OpenApi\Attributes as OA;

class CompraController extends Controller
{
    #[OA\Get(
        path: "/api/compras",
        tags: ["Compras"],
        summary: "Obtener todas las compras activas",
        description: "Devuelve una lista completa de compras activas (estado = 1) con sus relaciones (proveedor y detalles).",
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de compras obtenida correctamente"
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
            // Solo mostrar compras activas (estado = 1) con sus relaciones
            $compras = Compra::with(['proveedor', 'detalles'])
                ->where('estado', 1)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $compras
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las compras.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Post(
        path: "/api/compras",
        tags: ["Compras"],
        summary: "Crear una compra",
        description: "Registra una nueva compra en la base de datos. Por defecto, estado = 1 (activa).",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "n_documento", type: "string", example: "FACT-001"),
                    new OA\Property(property: "id_proveedor", type: "integer", example: 1),
                    new OA\Property(property: "id_usuario", type: "integer", example: 1),
                    new OA\Property(property: "total_compra", type: "number", format: "float", example: 1500.50),
                    new OA\Property(property: "forma_pago", type: "string", example: "Efectivo"),
                    new OA\Property(property: "observacion", type: "string", example: "Compra urgente"),
                    new OA\Property(property: "fecha", type: "string", format: "date", example: "2026-07-21"),
                    new OA\Property(property: "estado", type: "integer", example: 1, default: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Compra creada correctamente"
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
    public function store(CompraRequest $request)
    {
        try {
            // Asegurar que la compra se crea con estado = 1 (activa)
            $data = $request->validated();
            $data['estado'] = 1; // Forzar estado activo al crear

            $compra = Compra::create($data);

            // Cargar las relaciones para la respuesta
            $compra->load(['proveedor', 'detalles']);

            return response()->json([
                'success' => true,
                'message' => 'Compra registrada correctamente.',
                'data' => $compra
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la compra.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Get(
        path: "/api/compras/{id}",
        tags: ["Compras"],
        summary: "Obtener compra por ID",
        description: "Busca una compra específica mediante su identificador. Solo muestra compras activas.",
        parameters: [
            new OA\Parameter(
                name: "id",
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
                description: "Compra encontrada"
            ),
            new OA\Response(
                response: 404,
                description: "Compra no encontrada o inactiva"
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
            // Buscar compra activa por ID con sus relaciones
            $compra = Compra::with(['proveedor', 'detalles'])
                ->where('estado', 1)
                ->find($id);

            if (!$compra) {
                return response()->json([
                    'success' => false,
                    'message' => 'Compra no encontrada o inactiva.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $compra
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar la compra.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Put(
        path: "/api/compras/{id}",
        tags: ["Compras"],
        summary: "Actualizar compra",
        description: "Actualiza la información de una compra existente. Solo permite actualizar compras activas.",
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID de la compra",
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
                    new OA\Property(property: "n_documento", type: "string", example: "FACT-001-A"),
                    new OA\Property(property: "id_proveedor", type: "integer", example: 1),
                    new OA\Property(property: "id_usuario", type: "integer", example: 1),
                    new OA\Property(property: "total_compra", type: "number", format: "float", example: 1800.75),
                    new OA\Property(property: "forma_pago", type: "string", example: "Transferencia"),
                    new OA\Property(property: "observacion", type: "string", example: "Compra actualizada"),
                    new OA\Property(property: "fecha", type: "string", format: "date", example: "2026-07-22"),
                    new OA\Property(property: "estado", type: "integer", example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Compra actualizada correctamente"
            ),
            new OA\Response(
                response: 404,
                description: "Compra no encontrada o inactiva"
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
    public function update(CompraRequest $request, $id)
    {
        try {
            // Buscar compra activa por ID
            $compra = Compra::where('estado', 1)->find($id);

            if (!$compra) {
                return response()->json([
                    'success' => false,
                    'message' => 'Compra no encontrada o inactiva.'
                ], 404);
            }

            // Actualizar la compra
            $compra->update($request->validated());

            // Recargar la compra con sus relaciones
            $compra->refresh();
            $compra->load(['proveedor', 'detalles']);

            return response()->json([
                'success' => true,
                'message' => 'Compra actualizada correctamente.',
                'data' => $compra
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la compra.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Delete(
        path: "/api/compras/{id}",
        tags: ["Compras"],
        summary: "Eliminar compra (lógico)",
        description: "Cambia el estado de la compra a 0 (inactiva) en lugar de eliminarla físicamente.",
        parameters: [
            new OA\Parameter(
                name: "id",
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
                description: "Compra eliminada lógicamente (estado = 0)"
            ),
            new OA\Response(
                response: 404,
                description: "Compra no encontrada o ya inactiva"
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
            // Buscar compra activa por ID
            $compra = Compra::where('estado', 1)->find($id);

            if (!$compra) {
                return response()->json([
                    'success' => false,
                    'message' => 'Compra no encontrada o ya inactiva.'
                ], 404);
            }

            // Verificar si tiene detalles asociados
            if ($compra->detalles()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar la compra porque tiene detalles asociados.'
                ], 400);
            }

            // Eliminación lógica: cambiar estado a 0
            $compra->update(['estado' => 0]);

            return response()->json([
                'success' => true,
                'message' => 'Compra eliminada correctamente (estado = 0).'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la compra.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Método adicional para reactivar compras (opcional pero útil)
     */
    #[OA\Patch(
        path: "/api/compras/{id}/reactivar",
        tags: ["Compras"],
        summary: "Reactivar compra",
        description: "Cambia el estado de la compra a 1 (activa).",
        parameters: [
            new OA\Parameter(
                name: "id",
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
                description: "Compra reactivada correctamente"
            ),
            new OA\Response(
                response: 404,
                description: "Compra no encontrada"
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
            $compra = Compra::find($id);

            if (!$compra) {
                return response()->json([
                    'success' => false,
                    'message' => 'Compra no encontrada.'
                ], 404);
            }

            // Reactivar: cambiar estado a 1
            $compra->update(['estado' => 1]);

            // Cargar relaciones
            $compra->load(['proveedor', 'detalles']);

            return response()->json([
                'success' => true,
                'message' => 'Compra reactivada correctamente.',
                'data' => $compra
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al reactivar la compra.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Método para obtener compras por proveedor
     */
    #[OA\Get(
        path: "/api/compras/proveedor/{id_proveedor}",
        tags: ["Compras"],
        summary: "Obtener compras por proveedor",
        description: "Devuelve todas las compras activas de un proveedor específico.",
        parameters: [
            new OA\Parameter(
                name: "id_proveedor",
                description: "ID del proveedor",
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
                description: "Lista de compras del proveedor"
            ),
            new OA\Response(
                response: 404,
                description: "Proveedor sin compras"
            ),
            new OA\Response(
                response: 500,
                description: "Error interno del servidor"
            )
        ]
    )]
    public function getByProveedor($id_proveedor)
    {
        try {
            $compras = Compra::with(['proveedor', 'detalles'])
                ->where('estado', 1)
                ->where('id_proveedor', $id_proveedor)
                ->get();

            if ($compras->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron compras para este proveedor.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $compras
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las compras del proveedor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}