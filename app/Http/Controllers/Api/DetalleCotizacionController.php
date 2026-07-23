<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetalleCotizacion;
use App\Http\Requests\DetalleCotizacionRequest;
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
            // Solo mostrar detalles activos (estado = 1) con sus relaciones
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

    #[OA\Post(
        path: "/api/detalles-cotizacion",
        tags: ["Detalles de Cotización"],
        summary: "Crear un detalle de cotización",
        description: "Registra un nuevo detalle de cotización en la base de datos. Por defecto, estado = 1 (activo).",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "id_cotizacion", type: "integer", example: 1),
                    new OA\Property(property: "id_equipo", type: "integer", example: 1),
                    new OA\Property(property: "id_repuesto", type: "integer", example: 1),
                    new OA\Property(property: "precio", type: "number", format: "float", example: 250.50),
                    new OA\Property(property: "cantidad", type: "integer", example: 2),
                    new OA\Property(property: "descuento", type: "number", format: "float", example: 10.00),
                    new OA\Property(property: "estado", type: "integer", example: 1, default: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Detalle de cotización creado correctamente"
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
    public function store(DetalleCotizacionRequest $request)
    {
        try {
            // Asegurar que el detalle se crea con estado = 1 (activo)
            $data = $request->validated();
            $data['estado'] = 1; // Forzar estado activo al crear

            $detalle = DetalleCotizacion::create($data);

            // Cargar la relación para la respuesta
            $detalle->load('cotizacion');

            return response()->json([
                'success' => true,
                'message' => 'Detalle de cotización registrado correctamente.',
                'data' => $detalle
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el detalle de cotización.',
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
                schema: new OA\Schema(
                    type: "integer"
                )
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
            // Buscar detalle activo por ID con sus relaciones
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

    #[OA\Put(
        path: "/api/detalles-cotizacion/{id}",
        tags: ["Detalles de Cotización"],
        summary: "Actualizar detalle de cotización",
        description: "Actualiza la información de un detalle de cotización existente. Solo permite actualizar detalles activos.",
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID del detalle de cotización",
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
                    new OA\Property(property: "id_cotizacion", type: "integer", example: 1),
                    new OA\Property(property: "id_equipo", type: "integer", example: 1),
                    new OA\Property(property: "id_repuesto", type: "integer", example: 1),
                    new OA\Property(property: "precio", type: "number", format: "float", example: 275.75),
                    new OA\Property(property: "cantidad", type: "integer", example: 3),
                    new OA\Property(property: "descuento", type: "number", format: "float", example: 15.00),
                    new OA\Property(property: "estado", type: "integer", example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Detalle de cotización actualizado correctamente"
            ),
            new OA\Response(
                response: 404,
                description: "Detalle de cotización no encontrado o inactivo"
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
    public function update(DetalleCotizacionRequest $request, $id)
    {
        try {
            // Buscar detalle activo por ID
            $detalle = DetalleCotizacion::where('estado', 1)->find($id);

            if (!$detalle) {
                return response()->json([
                    'success' => false,
                    'message' => 'Detalle de cotización no encontrado o inactivo.'
                ], 404);
            }

            // Actualizar el detalle usando datos validados (CORREGIDO)
            $detalle->update($request->validated());

            // Recargar el detalle con sus relaciones
            $detalle->refresh();
            $detalle->load('cotizacion');

            return response()->json([
                'success' => true,
                'message' => 'Detalle de cotización actualizado correctamente.',
                'data' => $detalle
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el detalle de cotización.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Delete(
        path: "/api/detalles-cotizacion/{id}",
        tags: ["Detalles de Cotización"],
        summary: "Eliminar detalle de cotización (lógico)",
        description: "Cambia el estado del detalle de cotización a 0 (inactivo) en lugar de eliminarlo físicamente.",
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID del detalle de cotización",
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
                description: "Detalle de cotización eliminado lógicamente (estado = 0)"
            ),
            new OA\Response(
                response: 404,
                description: "Detalle de cotización no encontrado o ya inactivo"
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
            $detalle = DetalleCotizacion::where('estado', 1)->find($id);

            if (!$detalle) {
                return response()->json([
                    'success' => false,
                    'message' => 'Detalle de cotización no encontrado o ya inactivo.'
                ], 404);
            }

            // Eliminación lógica: cambiar estado a 0
            $detalle->update(['estado' => 0]);

            return response()->json([
                'success' => true,
                'message' => 'Detalle de cotización eliminado correctamente (estado = 0).'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el detalle de cotización.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Método adicional para reactivar detalles de cotización
     */
    #[OA\Patch(
        path: "/api/detalles-cotizacion/{id}/reactivar",
        tags: ["Detalles de Cotización"],
        summary: "Reactivar detalle de cotización",
        description: "Cambia el estado del detalle de cotización a 1 (activo).",
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID del detalle de cotización",
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
                description: "Detalle de cotización reactivado correctamente"
            ),
            new OA\Response(
                response: 404,
                description: "Detalle de cotización no encontrado"
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
            $detalle = DetalleCotizacion::find($id);

            if (!$detalle) {
                return response()->json([
                    'success' => false,
                    'message' => 'Detalle de cotización no encontrado.'
                ], 404);
            }

            // Reactivar: cambiar estado a 1
            $detalle->update(['estado' => 1]);

            // Cargar relaciones
            $detalle->load('cotizacion');

            return response()->json([
                'success' => true,
                'message' => 'Detalle de cotización reactivado correctamente.',
                'data' => $detalle
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al reactivar el detalle de cotización.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Método para obtener detalles por cotización
     */
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
                schema: new OA\Schema(
                    type: "integer"
                )
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