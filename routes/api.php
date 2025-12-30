<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SavingsFundController;
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
    Route::post('/savings-funds', [SavingsFundController::class, 'store']);
});
