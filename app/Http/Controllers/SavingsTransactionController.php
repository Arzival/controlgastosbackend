<?php

namespace App\Http\Controllers;

use App\Models\SavingsFund;
use App\Models\SavingsTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class SavingsTransactionController extends Controller
{
    /**
     * Obtener todas las transacciones de ahorro del usuario autenticado
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $savingsTransactions = SavingsTransaction::where('user_id', $user->id)
            ->with('savingsFund:id,name,color')
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $savingsTransactions->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'savings_fund_id' => $transaction->savings_fund_id,
                    'fund_name' => $transaction->savingsFund->name ?? null,
                    'fund_color' => $transaction->savingsFund->color ?? null,
                    'type' => $transaction->type,
                    'amount' => $transaction->amount,
                    'description' => $transaction->description,
                    'date' => $transaction->date,
                    'created_at' => $transaction->created_at,
                ];
            })
        ], 200);
    }

    /**
     * Crear una nueva transacción de ahorro
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'savings_fund_id' => 'required|exists:savings_funds,id',
            'type' => 'required|in:deposit,withdrawal',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
            'date' => 'required|date',
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

        // Verificar que el fondo de ahorro pertenezca al usuario
        $savingsFund = $user->savingsFunds()->find($request->savings_fund_id);
        if (!$savingsFund) {
            return response()->json([
                'status' => 'error',
                'message' => 'El fondo de ahorro no existe o no pertenece al usuario'
            ], 404);
        }

        // Validar que si es un retiro, haya suficiente saldo
        if ($request->type === 'withdrawal' && $savingsFund->balance < $request->amount) {
            return response()->json([
                'status' => 'error',
                'message' => 'No hay suficiente saldo en el fondo de ahorro'
            ], 422);
        }

        // Usar transacción de base de datos para asegurar consistencia
        DB::beginTransaction();
        try {
            // Crear la transacción de ahorro
            $savingsTransaction = SavingsTransaction::create([
                'savings_fund_id' => $request->savings_fund_id,
                'user_id' => $user->id,
                'type' => $request->type,
                'amount' => $request->amount,
                'description' => $request->description,
                'date' => $request->date,
            ]);

            // Actualizar el balance del fondo de ahorro
            if ($request->type === 'deposit') {
                $savingsFund->balance += $request->amount;
            } else {
                $savingsFund->balance -= $request->amount;
                $savingsFund->balance = max(0, $savingsFund->balance); // Asegurar que no sea negativo
            }
            $savingsFund->save();

            DB::commit();

            // Retornar respuesta exitosa
            return response()->json([
                'status' => 'success',
                'message' => 'Transacción de ahorro creada exitosamente',
                'data' => [
                    'id' => $savingsTransaction->id,
                    'savings_fund_id' => $savingsTransaction->savings_fund_id,
                    'type' => $savingsTransaction->type,
                    'amount' => $savingsTransaction->amount,
                    'description' => $savingsTransaction->description,
                    'date' => $savingsTransaction->date,
                    'created_at' => $savingsTransaction->created_at,
                    'fund_balance' => $savingsFund->balance,
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear la transacción de ahorro'
            ], 500);
        }
    }
}
