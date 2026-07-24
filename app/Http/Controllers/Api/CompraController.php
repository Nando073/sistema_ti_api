<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Compra;
use App\Http\Requests\CompraRequest;
use Illuminate\Support\Facades\DB;
use App\Models\DetalleCompra;
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
    summary: "Crear una compra con sus detalles",
    description: "Registra una nueva compra junto con sus detalles en una sola transacción. Todos los detalles deben enviarse en el campo 'detalles'.",
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                // Cabecera
                new OA\Property(property: "n_documento", type: "string", example: "FACT-001"),
                new OA\Property(property: "id_proveedor", type: "integer", example: 1),
                new OA\Property(property: "id_usuario", type: "integer", example: 1),
                new OA\Property(property: "total_compra", type: "number", format: "float", example: 1500.50),
                new OA\Property(property: "forma_pago", type: "string", example: "Efectivo"),
                new OA\Property(property: "observacion", type: "string", example: "Compra urgente"),
                new OA\Property(property: "fecha", type: "string", format: "date", example: "2026-07-21"),
                new OA\Property(property: "estado", type: "integer", example: 1, default: 1),
                // Detalles (array)
                new OA\Property(
                    property: "detalles",
                    type: "array",
                    description: "Lista de detalles de la compra",
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: "id_repuesto", type: "integer", example: 1),
                            new OA\Property(property: "cantidad", type: "integer", example: 3),
                            new OA\Property(property: "precio", type: "number", format: "float", example: 150.50),
                            new OA\Property(property: "sub_total", type: "number", format: "float", example: 451.50)
                        ]
                    )
                )
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: "Compra y detalles creados correctamente"
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
    public function store(CompraRequest $request)
{
    try {
        $data = $request->validated();
        $data['estado'] = 1; // Forzar estado activo

        $compra = DB::transaction(function () use ($data) {
            // 1. Crear la compra
            $compra = Compra::create($data);

            // 2. Crear los detalles
            foreach ($data['detalles'] as $detalleData) {
                $detalleData['id_compra'] = $compra->id_compra;
                $detalleData['estado'] = 1; // Activo por defecto
                DetalleCompra::create($detalleData);
            }

            return $compra;
        });

        // Cargar relaciones para la respuesta
        $compra->load(['proveedor', 'detalles']);

        return response()->json([
            'success' => true,
            'message' => 'Compra y detalles registrados correctamente.',
            'data' => $compra
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al registrar la compra y detalles.',
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
        summary: "Actualizar compra y sus detalles",
        description: "Actualiza la cabecera y reemplaza todos los detalles existentes por los nuevos enviados.",
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID de la compra",
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
                    new OA\Property(property: "n_documento", type: "string", example: "FACT-001-A"),
                    new OA\Property(property: "id_proveedor", type: "integer", example: 1),
                    new OA\Property(property: "id_usuario", type: "integer", example: 1),
                    new OA\Property(property: "total_compra", type: "number", format: "float", example: 1800.75),
                    new OA\Property(property: "forma_pago", type: "string", example: "Transferencia"),
                    new OA\Property(property: "observacion", type: "string", example: "Compra actualizada"),
                    new OA\Property(property: "fecha", type: "string", format: "date", example: "2026-07-22"),
                    new OA\Property(property: "estado", type: "integer", example: 1),
                    // Detalles (array)
                    new OA\Property(
                        property: "detalles",
                        type: "array",
                        description: "Lista de nuevos detalles. Los detalles antiguos serán desactivados.",
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: "id_repuesto", type: "integer", example: 1),
                                new OA\Property(property: "cantidad", type: "integer", example: 3),
                                new OA\Property(property: "precio", type: "number", format: "float", example: 150.50),
                                new OA\Property(property: "sub_total", type: "number", format: "float", example: 451.50)
                            ]
                        )
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Compra y detalles actualizados correctamente"
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
        // Buscar compra activa
        $compra = Compra::where('estado', 1)->find($id);

        if (!$compra) {
            return response()->json([
                'success' => false,
                'message' => 'Compra no encontrada o inactiva.'
            ], 404);
        }

        $data = $request->validated();

        DB::transaction(function () use ($compra, $data) {
            // 1. Actualizar la compra
            $compra->update($data);

            // 2. Eliminar los detalles antiguos (lógico o físico)
            // Si usas eliminación lógica en detalles, pon estado=0
            DetalleCompra::where('id_compra', $compra->id_compra)->update(['estado' => 0]);

            // 3. Crear los nuevos detalles
            foreach ($data['detalles'] as $detalleData) {
                $detalleData['id_compra'] = $compra->id_compra;
                $detalleData['estado'] = 1;
                DetalleCompra::create($detalleData);
            }
        });

        // Recargar la compra con relaciones actualizadas
        $compra->refresh();
        $compra->load(['proveedor', 'detalles']);

        return response()->json([
            'success' => true,
            'message' => 'Compra y detalles actualizados correctamente.',
            'data' => $compra
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al actualizar la compra y detalles.',
            'error' => $e->getMessage()
        ], 500);
    }
}

    #[OA\Delete(
        path: "/api/compras/{id}",
        tags: ["Compras"],
        summary: "Eliminar compra (lógico)",
        description: "Cambia el estado de la compra a 0 (inactiva) y también desactiva todos sus detalles asociados.",
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID de la compra",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Compra y detalles eliminados lógicamente (estado = 0)"
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
        $compra = Compra::where('estado', 1)->find($id);

        if (!$compra) {
            return response()->json([
                'success' => false,
                'message' => 'Compra no encontrada o ya inactiva.'
            ], 404);
        }

        DB::transaction(function () use ($compra) {
            // Desactivar la compra
            $compra->update(['estado' => 0]);

            // Desactivar los detalles
            DetalleCompra::where('id_compra', $compra->id_compra)->update(['estado' => 0]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Compra y detalles eliminados correctamente (estado = 0).'
        ], 200);

    } catch (\Exception $e) {
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
    summary: "Reactivar compra y sus detalles",
    description: "Cambia el estado de la compra a 1 (activa) y también reactiva todos sus detalles asociados.",
    parameters: [
        new OA\Parameter(
            name: "id",
            description: "ID de la compra",
            in: "path",
            required: true,
            schema: new OA\Schema(type: "integer")
        )
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: "Compra y detalles reactivados correctamente"
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

        DB::transaction(function () use ($compra) {
            $compra->update(['estado' => 1]);
            DetalleCompra::where('id_compra', $compra->id_compra)->update(['estado' => 1]);
        });

        $compra->load(['proveedor', 'detalles']);

        return response()->json([
            'success' => true,
            'message' => 'Compra y detalles reactivados correctamente.',
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