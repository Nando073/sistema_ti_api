<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CotizacionRequest;
use App\Models\Cotizacion;
use Exception;
use OpenApi\Attributes as OA;

class CotizacionController extends Controller
{
    #[OA\Get(
        path: "/api/cotizaciones",
        tags: ["Cotizaciones"],
        summary: "Obtener todas las cotizaciones activas",
        description: "Devuelve una lista completa de cotizaciones activas (estado = 1) con sus relaciones (oferta y detalles).",
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de cotizaciones obtenida correctamente"
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
            // Solo mostrar cotizaciones activas (estado = 1) con sus relaciones
            $cotizaciones = Cotizacion::with(['oferta', 'detalles'])
                ->where('estado', 1)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $cotizaciones
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las cotizaciones.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Post(
        path: "/api/cotizaciones",
        tags: ["Cotizaciones"],
        summary: "Crear una cotización",
        description: "Registra una nueva cotización en la base de datos. Por defecto, estado = 1 (activa).",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "id_orden", type: "integer", example: 1),
                    new OA\Property(property: "id_oferta", type: "integer", example: 1),
                    new OA\Property(property: "id_cliente", type: "integer", example: 1),
                    new OA\Property(property: "id_usuario", type: "integer", example: 1),
                    new OA\Property(property: "fecha_cad", type: "string", format: "date", example: "2026-08-21"),
                    new OA\Property(property: "monto_total", type: "number", format: "float", example: 1500.00),
                    new OA\Property(property: "descuento", type: "number", format: "float", example: 150.00),
                    new OA\Property(property: "estado", type: "integer", example: 1, default: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Cotización creada correctamente"
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
    public function store(CotizacionRequest $request)
    {
        try {
            // Asegurar que la cotización se crea con estado = 1 (activa)
            $data = $request->validated();
            $data['estado'] = 1; // Forzar estado activo al crear

            $cotizacion = Cotizacion::create($data);

            // Cargar las relaciones para la respuesta
            $cotizacion->load(['oferta', 'detalles']);

            return response()->json([
                'success' => true,
                'message' => 'Cotización registrada correctamente.',
                'data' => $cotizacion
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la cotización.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Get(
        path: "/api/cotizaciones/{id}",
        tags: ["Cotizaciones"],
        summary: "Obtener cotización por ID",
        description: "Busca una cotización específica mediante su identificador. Solo muestra cotizaciones activas.",
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID de la cotización",
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
                description: "Cotización encontrada"
            ),
            new OA\Response(
                response: 404,
                description: "Cotización no encontrada o inactiva"
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
            // Buscar cotización activa por ID con sus relaciones
            $cotizacion = Cotizacion::with(['oferta', 'detalles'])
                ->where('estado', 1)
                ->find($id);

            if (!$cotizacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cotización no encontrada o inactiva.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $cotizacion
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar la cotización.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Put(
        path: "/api/cotizaciones/{id}",
        tags: ["Cotizaciones"],
        summary: "Actualizar cotización",
        description: "Actualiza la información de una cotización existente. Solo permite actualizar cotizaciones activas.",
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID de la cotización",
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
                    new OA\Property(property: "id_orden", type: "integer", example: 1),
                    new OA\Property(property: "id_oferta", type: "integer", example: 1),
                    new OA\Property(property: "id_cliente", type: "integer", example: 1),
                    new OA\Property(property: "id_usuario", type: "integer", example: 1),
                    new OA\Property(property: "fecha_cad", type: "string", format: "date", example: "2026-09-21"),
                    new OA\Property(property: "monto_total", type: "number", format: "float", example: 1800.00),
                    new OA\Property(property: "descuento", type: "number", format: "float", example: 200.00),
                    new OA\Property(property: "estado", type: "integer", example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Cotización actualizada correctamente"
            ),
            new OA\Response(
                response: 404,
                description: "Cotización no encontrada o inactiva"
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
    public function update(CotizacionRequest $request, $id)
    {
        try {
            // Buscar cotización activa por ID
            $cotizacion = Cotizacion::where('estado', 1)->find($id);

            if (!$cotizacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cotización no encontrada o inactiva.'
                ], 404);
            }

            // Actualizar la cotización
            $cotizacion->update($request->validated());

            // Recargar la cotización con sus relaciones
            $cotizacion->refresh();
            $cotizacion->load(['oferta', 'detalles']);

            return response()->json([
                'success' => true,
                'message' => 'Cotización actualizada correctamente.',
                'data' => $cotizacion
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la cotización.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Delete(
        path: "/api/cotizaciones/{id}",
        tags: ["Cotizaciones"],
        summary: "Eliminar cotización (lógico)",
        description: "Cambia el estado de la cotización a 0 (inactiva) en lugar de eliminarla físicamente.",
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID de la cotización",
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
                description: "Cotización eliminada lógicamente (estado = 0)"
            ),
            new OA\Response(
                response: 404,
                description: "Cotización no encontrada o ya inactiva"
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
            // Buscar cotización activa por ID
            $cotizacion = Cotizacion::where('estado', 1)->find($id);

            if (!$cotizacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cotización no encontrada o ya inactiva.'
                ], 404);
            }

            // Verificar si tiene detalles asociados
            if ($cotizacion->detalles()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar la cotización porque tiene detalles asociados.'
                ], 400);
            }

            // Eliminación lógica: cambiar estado a 0
            $cotizacion->update(['estado' => 0]);

            return response()->json([
                'success' => true,
                'message' => 'Cotización eliminada correctamente (estado = 0).'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la cotización.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Método adicional para reactivar cotizaciones
     */
    #[OA\Patch(
        path: "/api/cotizaciones/{id}/reactivar",
        tags: ["Cotizaciones"],
        summary: "Reactivar cotización",
        description: "Cambia el estado de la cotización a 1 (activa).",
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID de la cotización",
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
                description: "Cotización reactivada correctamente"
            ),
            new OA\Response(
                response: 404,
                description: "Cotización no encontrada"
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
            $cotizacion = Cotizacion::find($id);

            if (!$cotizacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cotización no encontrada.'
                ], 404);
            }

            // Reactivar: cambiar estado a 1
            $cotizacion->update(['estado' => 1]);

            // Cargar relaciones
            $cotizacion->load(['oferta', 'detalles']);

            return response()->json([
                'success' => true,
                'message' => 'Cotización reactivada correctamente.',
                'data' => $cotizacion
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al reactivar la cotización.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Método para obtener cotizaciones por oferta
     */
    #[OA\Get(
        path: "/api/cotizaciones/oferta/{id_oferta}",
        tags: ["Cotizaciones"],
        summary: "Obtener cotizaciones por oferta",
        description: "Devuelve todas las cotizaciones activas de una oferta específica.",
        parameters: [
            new OA\Parameter(
                name: "id_oferta",
                description: "ID de la oferta",
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
                description: "Lista de cotizaciones de la oferta"
            ),
            new OA\Response(
                response: 404,
                description: "Oferta sin cotizaciones activas"
            ),
            new OA\Response(
                response: 500,
                description: "Error interno del servidor"
            )
        ]
    )]
    public function getByOferta($id_oferta)
    {
        try {
            $cotizaciones = Cotizacion::with(['oferta', 'detalles'])
                ->where('estado', 1)
                ->where('id_oferta', $id_oferta)
                ->get();

            if ($cotizaciones->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron cotizaciones activas para esta oferta.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $cotizaciones
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las cotizaciones de la oferta.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Método para obtener cotizaciones por cliente
     */
    #[OA\Get(
        path: "/api/cotizaciones/cliente/{id_cliente}",
        tags: ["Cotizaciones"],
        summary: "Obtener cotizaciones por cliente",
        description: "Devuelve todas las cotizaciones activas de un cliente específico.",
        parameters: [
            new OA\Parameter(
                name: "id_cliente",
                description: "ID del cliente",
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
                description: "Lista de cotizaciones del cliente"
            ),
            new OA\Response(
                response: 404,
                description: "Cliente sin cotizaciones activas"
            ),
            new OA\Response(
                response: 500,
                description: "Error interno del servidor"
            )
        ]
    )]
    public function getByCliente($id_cliente)
    {
        try {
            $cotizaciones = Cotizacion::with(['oferta', 'detalles'])
                ->where('estado', 1)
                ->where('id_cliente', $id_cliente)
                ->get();

            if ($cotizaciones->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron cotizaciones activas para este cliente.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $cotizaciones
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las cotizaciones del cliente.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}