<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SavingsFundController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\SavingsTransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Backend funcionando correctamente'
    ], 200);
});

// Rutas de autenticación
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas (requieren autenticación)
Route::middleware('auth:sanctum')->group(function () {
    // Rutas de cajas de ahorro
    Route::get('/savings-funds', [SavingsFundController::class, 'index']);
    Route::post('/savings-funds', [SavingsFundController::class, 'store']);
    
    // Rutas de transacciones
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    
    // Rutas de transacciones de ahorro
    Route::get('/savings-transactions', [SavingsTransactionController::class, 'index']);
    Route::post('/savings-transactions', [SavingsTransactionController::class, 'store']);
});
