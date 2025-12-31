<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    /**
     * Obtener todas las transacciones del usuario autenticado
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $transactions = Transaction::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $transactions->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'type' => $transaction->type,
                    'amount' => $transaction->amount,
                    'category' => $transaction->category,
                    'description' => $transaction->description,
                    'date' => $transaction->date,
                    'savings_fund_id' => $transaction->savings_fund_id,
                    'created_at' => $transaction->created_at,
                ];
            })
        ], 200);
    }

    /**
     * Crear una nueva transacción
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:expense,income',
            'amount' => 'required|numeric|min:0.01',
            'category' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'savings_fund_id' => 'nullable|exists:savings_funds,id',
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

        // Verificar que si se envía savings_fund_id, el fondo pertenezca al usuario
        if ($request->savings_fund_id) {
            $savingsFund = $user->savingsFunds()->find($request->savings_fund_id);
            if (!$savingsFund) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El fondo de ahorro no existe o no pertenece al usuario'
                ], 404);
            }
        }

        // Crear la transacción
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'amount' => $request->amount,
            'category' => $request->category,
            'description' => $request->description,
            'date' => $request->date,
            'savings_fund_id' => $request->savings_fund_id,
        ]);

        // Retornar respuesta exitosa
        return response()->json([
            'status' => 'success',
            'message' => 'Transacción creada exitosamente',
            'data' => [
                'id' => $transaction->id,
                'type' => $transaction->type,
                'amount' => $transaction->amount,
                'category' => $transaction->category,
                'description' => $transaction->description,
                'date' => $transaction->date,
                'savings_fund_id' => $transaction->savings_fund_id,
                'created_at' => $transaction->created_at,
            ]
        ], 201);
    }

    /**
     * Eliminar una transacción
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request)
    {
        // Validar que se envíe el ID
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:transactions,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        
        // Buscar la transacción y verificar que pertenezca al usuario
        $transaction = Transaction::where('id', $request->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$transaction) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transacción no encontrada o no pertenece al usuario'
            ], 404);
        }

        // Eliminar la transacción
        $transaction->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Transacción eliminada exitosamente'
        ], 200);
    }

    /**
     * Actualizar una transacción
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        // Validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:transactions,id',
            'type' => 'sometimes|required|in:expense,income',
            'amount' => 'sometimes|required|numeric|min:0.01',
            'category' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'sometimes|required|date',
            'savings_fund_id' => 'nullable|exists:savings_funds,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        
        // Buscar la transacción y verificar que pertenezca al usuario
        $transaction = Transaction::where('id', $request->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$transaction) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transacción no encontrada o no pertenece al usuario'
            ], 404);
        }

        // Verificar que si se envía savings_fund_id, el fondo pertenezca al usuario
        if ($request->has('savings_fund_id') && $request->savings_fund_id) {
            $savingsFund = $user->savingsFunds()->find($request->savings_fund_id);
            if (!$savingsFund) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El fondo de ahorro no existe o no pertenece al usuario'
                ], 404);
            }
        }

        // Actualizar solo los campos que se envíen
        $transaction->fill($request->only([
            'type',
            'amount',
            'category',
            'description',
            'date',
            'savings_fund_id'
        ]));
        $transaction->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Transacción actualizada exitosamente',
            'data' => [
                'id' => $transaction->id,
                'type' => $transaction->type,
                'amount' => $transaction->amount,
                'category' => $transaction->category,
                'description' => $transaction->description,
                'date' => $transaction->date,
                'savings_fund_id' => $transaction->savings_fund_id,
                'updated_at' => $transaction->updated_at,
            ]
        ], 200);
    }
}
