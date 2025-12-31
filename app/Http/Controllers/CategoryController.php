<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Obtener todas las categorías del usuario autenticado
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $categories = Category::where('user_id', $user->id)
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'color' => $category->color,
                    'created_at' => $category->created_at,
                ];
            })
        ], 200);
    }

    /**
     * Crear una nueva categoría
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        // Si la validación falla, retornar errores
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Obtener el usuario autenticado
        $user = $request->user();

        // Verificar si la categoría ya existe para este usuario
        $existingCategory = Category::where('user_id', $user->id)
            ->where('name', $request->name)
            ->first();

        if ($existingCategory) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ya existe una categoría con ese nombre'
            ], 422);
        }

        // Crear la categoría
        $category = Category::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'color' => $request->color,
        ]);

        // Retornar respuesta exitosa
        return response()->json([
            'status' => 'success',
            'message' => 'Categoría creada exitosamente',
            'data' => [
                'id' => $category->id,
                'name' => $category->name,
                'color' => $category->color,
                'created_at' => $category->created_at,
            ]
        ], 201);
    }

    /**
     * Eliminar una categoría
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request)
    {
        // Validar que se envíe el ID
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        
        // Buscar la categoría y verificar que pertenezca al usuario
        $category = Category::where('id', $request->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Categoría no encontrada o no pertenece al usuario'
            ], 404);
        }

        // Verificar si la categoría está en uso en alguna transacción
        $inUse = \App\Models\Transaction::where('user_id', $user->id)
            ->where('category', $category->name)
            ->exists();

        if ($inUse) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se puede eliminar la categoría porque está en uso en algunas transacciones'
            ], 422);
        }

        // Eliminar la categoría
        $category->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Categoría eliminada exitosamente'
        ], 200);
    }

    /**
     * Actualizar una categoría
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        // Validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:categories,id',
            'name' => 'sometimes|required|string|max:255',
            'color' => 'sometimes|required|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        
        // Buscar la categoría y verificar que pertenezca al usuario
        $category = Category::where('id', $request->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Categoría no encontrada o no pertenece al usuario'
            ], 404);
        }

        // Si se está cambiando el nombre, verificar que no exista otra categoría con ese nombre
        if ($request->has('name') && $request->name !== $category->name) {
            $existingCategory = Category::where('user_id', $user->id)
                ->where('name', $request->name)
                ->where('id', '!=', $category->id)
                ->first();

            if ($existingCategory) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ya existe una categoría con ese nombre'
                ], 422);
            }
        }

        // Actualizar solo los campos que se envíen
        $category->fill($request->only(['name', 'color']));
        $category->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Categoría actualizada exitosamente',
            'data' => [
                'id' => $category->id,
                'name' => $category->name,
                'color' => $category->color,
                'updated_at' => $category->updated_at,
            ]
        ], 200);
    }
}
