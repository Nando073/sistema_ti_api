<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Oferta;
use App\Http\Requests\OfertaRequest;
use Exception;
use OpenApi\Attributes as OA;

class OfertaController extends Controller
{
    #[OA\Get(
        path: "/api/ofertas",
        tags: ["Ofertas"],
        summary: "Obtener todas las ofertas activas",
        description: "Devuelve una lista completa de ofertas activas (estado = 1).",
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de ofertas obtenida correctamente"
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
            // Solo mostrar ofertas activas (estado = 1)
            $ofertas = Oferta::where('estado', 1)->get();

            return response()->json([
                'success' => true,
                'data' => $ofertas
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las ofertas.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Post(
        path: "/api/ofertas",
        tags: ["Ofertas"],
        summary: "Crear una oferta",
        description: "Registra una nueva oferta en la base de datos. Por defecto, estado = 1 (activa).",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "of_nombre", type: "string", example: "Oferta especial"),
                    new OA\Property(property: "descripcion", type: "string", example: "Descripción de la oferta"),
                    new OA\Property(property: "porcentaje", type: "number", format: "float", example: 20.5),
                    new OA\Property(property: "fecha_inc", type: "string", format: "date", example: "2026-07-21"),
                    new OA\Property(property: "fecha_fin", type: "string", format: "date", example: "2026-08-21"),
                    new OA\Property(property: "estado", type: "integer", example: 1, default: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Oferta creada correctamente"
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
    public function store(OfertaRequest $request)
    {
        try {
            // Asegurar que la oferta se crea con estado = 1 (activa)
            $data = $request->validated();
            $data['estado'] = 1; // Forzar estado activo al crear
            
            $oferta = Oferta::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Oferta creada correctamente.',
                'data' => $oferta
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la oferta.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Get(
        path: "/api/ofertas/{id}",
        tags: ["Ofertas"],
        summary: "Obtener oferta por ID",
        description: "Busca una oferta específica mediante su identificador. Solo muestra ofertas activas.",
        parameters: [
            new OA\Parameter(
                name: "id",
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
                description: "Oferta encontrada"
            ),
            new OA\Response(
                response: 404,
                description: "Oferta no encontrada o inactiva"
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
            // Buscar oferta activa por ID usando la clave primaria correcta
            $oferta = Oferta::where('estado', 1)->find($id);

            if (!$oferta) {
                return response()->json([
                    'success' => false,
                    'message' => 'Oferta no encontrada o inactiva.'
                ], 404);
            }

            // Cargar las cotizaciones relacionadas si es necesario
            // $oferta->load('cotizaciones');

            return response()->json([
                'success' => true,
                'data' => $oferta
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar la oferta.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Put(
        path: "/api/ofertas/{id}",
        tags: ["Ofertas"],
        summary: "Actualizar oferta",
        description: "Actualiza la información de una oferta existente. Solo permite actualizar ofertas activas.",
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID de la oferta",
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
                    new OA\Property(property: "of_nombre", type: "string", example: "Oferta especial actualizada"),
                    new OA\Property(property: "descripcion", type: "string", example: "Nueva descripción"),
                    new OA\Property(property: "porcentaje", type: "number", format: "float", example: 25.0),
                    new OA\Property(property: "fecha_inc", type: "string", format: "date", example: "2026-07-22"),
                    new OA\Property(property: "fecha_fin", type: "string", format: "date", example: "2026-08-22"),
                    new OA\Property(property: "estado", type: "integer", example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Oferta actualizada correctamente"
            ),
            new OA\Response(
                response: 404,
                description: "Oferta no encontrada o inactiva"
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
    public function update(OfertaRequest $request, $id)
    {
        try {
            // Buscar oferta activa por ID usando la clave primaria correcta
            $oferta = Oferta::where('estado', 1)->find($id);

            if (!$oferta) {
                return response()->json([
                    'success' => false,
                    'message' => 'Oferta no encontrada o inactiva.'
                ], 404);
            }

            // Actualizar la oferta
            $oferta->update($request->validated());

            // Recargar la oferta con los datos actualizados
            $oferta->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Oferta actualizada correctamente.',
                'data' => $oferta
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la oferta.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Delete(
        path: "/api/ofertas/{id}",
        tags: ["Ofertas"],
        summary: "Eliminar oferta (lógico)",
        description: "Cambia el estado de la oferta a 0 (inactiva) en lugar de eliminarla físicamente.",
        parameters: [
            new OA\Parameter(
                name: "id",
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
                description: "Oferta eliminada lógicamente (estado = 0)"
            ),
            new OA\Response(
                response: 404,
                description: "Oferta no encontrada o ya inactiva"
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
            // Buscar oferta activa por ID usando la clave primaria correcta
            $oferta = Oferta::where('estado', 1)->find($id);

            if (!$oferta) {
                return response()->json([
                    'success' => false,
                    'message' => 'Oferta no encontrada o ya inactiva.'
                ], 404);
            }

            // Eliminación lógica: cambiar estado a 0
            $oferta->update(['estado' => 0]);

            return response()->json([
                'success' => true,
                'message' => 'Oferta eliminada correctamente (estado = 0).'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la oferta.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Método adicional para reactivar ofertas (opcional pero útil)
     */
    #[OA\Patch(
        path: "/api/ofertas/{id}/reactivar",
        tags: ["Ofertas"],
        summary: "Reactivar oferta",
        description: "Cambia el estado de la oferta a 1 (activa).",
        parameters: [
            new OA\Parameter(
                name: "id",
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
                description: "Oferta reactivada correctamente"
            ),
            new OA\Response(
                response: 404,
                description: "Oferta no encontrada"
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
            $oferta = Oferta::find($id);

            if (!$oferta) {
                return response()->json([
                    'success' => false,
                    'message' => 'Oferta no encontrada.'
                ], 404);
            }

            // Reactivar: cambiar estado a 1
            $oferta->update(['estado' => 1]);

            return response()->json([
                'success' => true,
                'message' => 'Oferta reactivada correctamente.',
                'data' => $oferta
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al reactivar la oferta.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}