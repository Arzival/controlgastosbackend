<?php

namespace App\Http\Controllers;

use App\Models\SavingsFund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SavingsFundController extends Controller
{
    /**
     * Obtener todas las cajas de ahorro del usuario autenticado
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $savingsFunds = SavingsFund::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $savingsFunds->map(function ($fund) {
                return [
                    'id' => $fund->id,
                    'name' => $fund->name,
                    'description' => $fund->description,
                    'color' => $fund->color,
                    'balance' => $fund->balance,
                    'created_at' => $fund->created_at,
                ];
            })
        ], 200);
    }

    /**
     * Crear una nueva caja de ahorro
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
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

        // Crear la caja de ahorro
        $savingsFund = SavingsFund::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'description' => $request->description,
            'color' => $request->color,
            'balance' => 0,
        ]);

        // Retornar respuesta exitosa
        return response()->json([
            'status' => 'success',
            'message' => 'Caja de ahorro creada exitosamente',
            'data' => [
                'id' => $savingsFund->id,
                'name' => $savingsFund->name,
                'description' => $savingsFund->description,
                'color' => $savingsFund->color,
                'balance' => $savingsFund->balance,
                'created_at' => $savingsFund->created_at,
            ]
        ], 201);
    }

    /**
     * Eliminar una caja de ahorro
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request)
    {
        // Validar que se envíe el ID
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:savings_funds,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        
        // Buscar el fondo y verificar que pertenezca al usuario
        $savingsFund = SavingsFund::where('id', $request->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$savingsFund) {
            return response()->json([
                'status' => 'error',
                'message' => 'Fondo de ahorro no encontrado o no pertenece al usuario'
            ], 404);
        }

        // Validar que el balance sea 0 antes de eliminar
        if ($savingsFund->balance > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se puede eliminar un fondo de ahorro con saldo. Primero debes retirar todo el dinero.'
            ], 422);
        }

        // Eliminar el fondo (las transacciones se eliminan en cascade)
        $savingsFund->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Fondo de ahorro eliminado exitosamente'
        ], 200);
    }

    /**
     * Actualizar una caja de ahorro
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        // Validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:savings_funds,id',
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
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
        
        // Buscar el fondo y verificar que pertenezca al usuario
        $savingsFund = SavingsFund::where('id', $request->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$savingsFund) {
            return response()->json([
                'status' => 'error',
                'message' => 'Fondo de ahorro no encontrado o no pertenece al usuario'
            ], 404);
        }

        // Actualizar solo los campos que se envíen
        $savingsFund->fill($request->only(['name', 'description', 'color']));
        $savingsFund->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Fondo de ahorro actualizado exitosamente',
            'data' => [
                'id' => $savingsFund->id,
                'name' => $savingsFund->name,
                'description' => $savingsFund->description,
                'color' => $savingsFund->color,
                'balance' => $savingsFund->balance,
                'updated_at' => $savingsFund->updated_at,
            ]
        ], 200);
    }
}
