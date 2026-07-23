<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Proveedor;
use App\Http\Requests\ProveedorRequest;
use Exception;
use OpenApi\Attributes as OA;

class ProveedorController extends Controller
{
    #[OA\Get(
        path: "/api/proveedores",
        tags: ["Proveedores"],
        summary: "Obtener todos los proveedores activos",
        description: "Devuelve una lista completa de proveedores activos (estado = 1).",
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de proveedores obtenida correctamente"
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
            // Solo mostrar proveedores activos (estado = 1)
            $proveedores = Proveedor::where('estado', 1)->get();

            return response()->json([
                'success' => true,
                'data' => $proveedores
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los proveedores.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Post(
        path: "/api/proveedores",
        tags: ["Proveedores"],
        summary: "Crear un proveedor",
        description: "Registra un nuevo proveedor en la base de datos. Por defecto, estado = 1 (activo).",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "nombre", type: "string", example: "Proveedor S.A."),
                    new OA\Property(property: "nit", type: "string", example: "1234567890"),
                    new OA\Property(property: "departamento", type: "string", example: "La Paz"),
                    new OA\Property(property: "direccion", type: "string", example: "Av. Principal #123"),
                    new OA\Property(property: "correo", type: "string", format: "email", example: "contacto@proveedor.com"),
                    new OA\Property(property: "celular", type: "string", example: "78945612"),
                    new OA\Property(property: "fecha_registro", type: "string", format: "date", example: "2026-07-21"),
                    new OA\Property(property: "estado", type: "integer", example: 1, default: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Proveedor creado correctamente"
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
    public function store(ProveedorRequest $request)
    {
        try {
            // Asegurar que el proveedor se crea con estado = 1 (activo)
            $data = $request->validated();
            $data['estado'] = 1; // Forzar estado activo al crear
            
            $proveedor = Proveedor::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Proveedor creado correctamente.',
                'data' => $proveedor
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el proveedor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Get(
        path: "/api/proveedores/{id}",
        tags: ["Proveedores"],
        summary: "Obtener proveedor por ID",
        description: "Busca un proveedor específico mediante su identificador. Solo muestra proveedores activos.",
        parameters: [
            new OA\Parameter(
                name: "id",
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
                description: "Proveedor encontrado"
            ),
            new OA\Response(
                response: 404,
                description: "Proveedor no encontrado o inactivo"
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
            // Buscar proveedor activo por ID
            $proveedor = Proveedor::where('estado', 1)->find($id);

            if (!$proveedor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Proveedor no encontrado o inactivo.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $proveedor
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar el proveedor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Put(
        path: "/api/proveedores/{id}",
        tags: ["Proveedores"],
        summary: "Actualizar proveedor",
        description: "Actualiza la información de un proveedor existente. Solo permite actualizar proveedores activos.",
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID del proveedor",
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
                    new OA\Property(property: "nombre", type: "string", example: "Proveedor S.A. Actualizado"),
                    new OA\Property(property: "nit", type: "string", example: "1234567891"),
                    new OA\Property(property: "departamento", type: "string", example: "Santa Cruz"),
                    new OA\Property(property: "direccion", type: "string", example: "Av. Secundaria #456"),
                    new OA\Property(property: "correo", type: "string", format: "email", example: "nuevo@proveedor.com"),
                    new OA\Property(property: "celular", type: "string", example: "78945613"),
                    new OA\Property(property: "fecha_registro", type: "string", format: "date", example: "2026-07-21"),
                    new OA\Property(property: "estado", type: "integer", example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Proveedor actualizado correctamente"
            ),
            new OA\Response(
                response: 404,
                description: "Proveedor no encontrado o inactivo"
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
    public function update(ProveedorRequest $request, $id)
    {
        try {
            // Buscar proveedor activo por ID
            $proveedor = Proveedor::where('estado', 1)->find($id);

            if (!$proveedor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Proveedor no encontrado o inactivo.'
                ], 404);
            }

            // Actualizar el proveedor
            $proveedor->update($request->validated());

            // Recargar el proveedor con los datos actualizados
            $proveedor->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Proveedor actualizado correctamente.',
                'data' => $proveedor
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el proveedor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Delete(
        path: "/api/proveedores/{id}",
        tags: ["Proveedores"],
        summary: "Eliminar proveedor (lógico)",
        description: "Cambia el estado del proveedor a 0 (inactivo) en lugar de eliminarlo físicamente.",
        parameters: [
            new OA\Parameter(
                name: "id",
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
                description: "Proveedor eliminado lógicamente (estado = 0)"
            ),
            new OA\Response(
                response: 404,
                description: "Proveedor no encontrado o ya inactivo"
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
            // Buscar proveedor activo por ID
            $proveedor = Proveedor::where('estado', 1)->find($id);

            if (!$proveedor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Proveedor no encontrado o ya inactivo.'
                ], 404);
            }

            // Eliminación lógica: cambiar estado a 0
            $proveedor->update(['estado' => 0]);

            return response()->json([
                'success' => true,
                'message' => 'Proveedor eliminado correctamente (estado = 0).'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el proveedor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Método adicional para reactivar proveedores (opcional pero útil)
     */
    #[OA\Patch(
        path: "/api/proveedores/{id}/reactivar",
        tags: ["Proveedores"],
        summary: "Reactivar proveedor",
        description: "Cambia el estado del proveedor a 1 (activo).",
        parameters: [
            new OA\Parameter(
                name: "id",
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
                description: "Proveedor reactivado correctamente"
            ),
            new OA\Response(
                response: 404,
                description: "Proveedor no encontrado"
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
            $proveedor = Proveedor::find($id);

            if (!$proveedor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Proveedor no encontrado.'
                ], 404);
            }

            // Reactivar: cambiar estado a 1
            $proveedor->update(['estado' => 1]);

            return response()->json([
                'success' => true,
                'message' => 'Proveedor reactivado correctamente.',
                'data' => $proveedor
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al reactivar el proveedor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}