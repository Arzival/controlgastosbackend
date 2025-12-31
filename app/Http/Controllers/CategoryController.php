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
}
