<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cotizacion;
use App\Models\DetalleCotizacion;
use App\Http\Requests\CotizacionRequest;
use Illuminate\Support\Facades\DB;
use Exception;
use OpenApi\Attributes as OA;

class CotizacionController extends Controller
{
    #[OA\Get(
        path: "/api/cotizaciones",
        tags: ["Cotizaciones"],
        summary: "Obtener todas las cotizaciones activas",
        description: "Devuelve una lista completa de cotizaciones activas (estado = 1) con sus relaciones.",
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
        summary: "Crear una cotización con sus detalles",
        description: "Registra una nueva cotización junto con sus detalles en una sola transacción. Todos los detalles deben enviarse en el campo 'detalles'.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    // Cabecera
                    new OA\Property(property: "id_orden", type: "integer", example: 1),
                    new OA\Property(property: "id_oferta", type: "integer", example: 1),
                    new OA\Property(property: "id_cliente", type: "integer", example: 1),
                    new OA\Property(property: "id_usuario", type: "integer", example: 1),
                    new OA\Property(property: "fecha_cad", type: "string", format: "date", example: "2026-08-21"),
                    new OA\Property(property: "monto_total", type: "number", format: "float", example: 1500.00),
                    new OA\Property(property: "descuento", type: "number", format: "float", example: 150.00),
                    new OA\Property(property: "estado", type: "integer", example: 1, default: 1),
                    // Detalles (array)
                    new OA\Property(
                        property: "detalles",
                        type: "array",
                        description: "Lista de detalles de la cotización",
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: "id_equipo", type: "integer", example: 1),
                                new OA\Property(property: "id_repuesto", type: "integer", example: 1),
                                new OA\Property(property: "precio", type: "number", format: "float", example: 250.50),
                                new OA\Property(property: "cantidad", type: "integer", example: 2),
                                new OA\Property(property: "descuento", type: "number", format: "float", example: 10.00)
                            ]
                        )
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Cotización y detalles creados correctamente"
            ),
            new OA\Response(
                response: 422,
                description: "Error de validación (cabecera o detalles)"
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
            $data = $request->validated();
            $data['estado'] = 1;

            $cotizacion = DB::transaction(function () use ($data) {
                // 1. Crear la cotización
                $cotizacion = Cotizacion::create($data);

                // 2. Crear los detalles
                foreach ($data['detalles'] as $detalleData) {
                    $detalleData['id_cotizacion'] = $cotizacion->id_cotizacion;
                    $detalleData['estado'] = 1;
                    DetalleCotizacion::create($detalleData);
                }

                return $cotizacion;
            });

            $cotizacion->load(['oferta', 'detalles']);

            return response()->json([
                'success' => true,
                'message' => 'Cotización y detalles registrados correctamente.',
                'data' => $cotizacion
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la cotización y detalles.',
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
                schema: new OA\Schema(type: "integer")
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
        summary: "Actualizar cotización y sus detalles",
        description: "Actualiza la cabecera y reemplaza todos los detalles existentes por los nuevos enviados.",
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID de la cotización",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    // Cabecera
                    new OA\Property(property: "id_orden", type: "integer", example: 1),
                    new OA\Property(property: "id_oferta", type: "integer", example: 1),
                    new OA\Property(property: "id_cliente", type: "integer", example: 1),
                    new OA\Property(property: "id_usuario", type: "integer", example: 1),
                    new OA\Property(property: "fecha_cad", type: "string", format: "date", example: "2026-09-21"),
                    new OA\Property(property: "monto_total", type: "number", format: "float", example: 1800.00),
                    new OA\Property(property: "descuento", type: "number", format: "float", example: 200.00),
                    new OA\Property(property: "estado", type: "integer", example: 1),
                    // Detalles (array)
                    new OA\Property(
                        property: "detalles",
                        type: "array",
                        description: "Lista de nuevos detalles. Los detalles antiguos serán desactivados.",
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: "id_equipo", type: "integer", example: 1),
                                new OA\Property(property: "id_repuesto", type: "integer", example: 1),
                                new OA\Property(property: "precio", type: "number", format: "float", example: 275.75),
                                new OA\Property(property: "cantidad", type: "integer", example: 3),
                                new OA\Property(property: "descuento", type: "number", format: "float", example: 15.00)
                            ]
                        )
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Cotización y detalles actualizados correctamente"
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
            $cotizacion = Cotizacion::where('estado', 1)->find($id);

            if (!$cotizacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cotización no encontrada o inactiva.'
                ], 404);
            }

            $data = $request->validated();

            DB::transaction(function () use ($cotizacion, $data) {
                // Actualizar cabecera
                $cotizacion->update($data);

                // Desactivar detalles antiguos
                DetalleCotizacion::where('id_cotizacion', $cotizacion->id_cotizacion)
                    ->update(['estado' => 0]);

                // Crear nuevos detalles
                foreach ($data['detalles'] as $detalleData) {
                    $detalleData['id_cotizacion'] = $cotizacion->id_cotizacion;
                    $detalleData['estado'] = 1;
                    DetalleCotizacion::create($detalleData);
                }
            });

            $cotizacion->refresh();
            $cotizacion->load(['oferta', 'detalles']);

            return response()->json([
                'success' => true,
                'message' => 'Cotización y detalles actualizados correctamente.',
                'data' => $cotizacion
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la cotización y detalles.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Delete(
        path: "/api/cotizaciones/{id}",
        tags: ["Cotizaciones"],
        summary: "Eliminar cotización y sus detalles (lógico)",
        description: "Cambia el estado de la cotización a 0 (inactiva) y también desactiva todos sus detalles asociados.",
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID de la cotización",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Cotización y detalles eliminados lógicamente (estado = 0)"
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
            $cotizacion = Cotizacion::where('estado', 1)->find($id);

            if (!$cotizacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cotización no encontrada o ya inactiva.'
                ], 404);
            }

            DB::transaction(function () use ($cotizacion) {
                // Desactivar cotización
                $cotizacion->update(['estado' => 0]);
                // Desactivar detalles
                DetalleCotizacion::where('id_cotizacion', $cotizacion->id_cotizacion)
                    ->update(['estado' => 0]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Cotización y detalles eliminados correctamente (estado = 0).'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la cotización.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Patch(
        path: "/api/cotizaciones/{id}/reactivar",
        tags: ["Cotizaciones"],
        summary: "Reactivar cotización y sus detalles",
        description: "Cambia el estado de la cotización a 1 (activa) y también reactiva todos sus detalles asociados.",
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID de la cotización",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Cotización y detalles reactivados correctamente"
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

            DB::transaction(function () use ($cotizacion) {
                $cotizacion->update(['estado' => 1]);
                DetalleCotizacion::where('id_cotizacion', $cotizacion->id_cotizacion)
                    ->update(['estado' => 1]);
            });

            $cotizacion->load(['oferta', 'detalles']);

            return response()->json([
                'success' => true,
                'message' => 'Cotización y detalles reactivados correctamente.',
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