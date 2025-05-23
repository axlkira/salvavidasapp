<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ChatController;
use App\Http\Controllers\API\RiskAssessmentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Rutas protegidas por autenticación
Route::middleware('auth:sanctum')->group(function () {
    // Rutas para el controlador de chat
    Route::prefix('chat')->group(function () {
        Route::get('/conversations', [ChatController::class, 'getConversations']);
        Route::get('/conversation/{id}', [ChatController::class, 'getConversation']);
        Route::post('/conversation', [ChatController::class, 'createConversation']);
        Route::post('/message', [ChatController::class, 'sendMessage']);
        Route::delete('/conversation/{id}', [ChatController::class, 'deleteConversation']);
        Route::get('/providers', [ChatController::class, 'getProviders']);
    });
    
    // Rutas para el controlador de evaluación de riesgo
    Route::prefix('risk')->group(function () {
        Route::get('/assessments', [RiskAssessmentController::class, 'index']);
        Route::get('/assessment/{id}', [RiskAssessmentController::class, 'show']);
        Route::post('/assessment', [RiskAssessmentController::class, 'store']);
        Route::put('/assessment/{id}/status', [RiskAssessmentController::class, 'updateStatus']);
        Route::post('/assessment/intervention-guide', [RiskAssessmentController::class, 'generateInterventionGuide']);
        Route::get('/high-risk-patients', [RiskAssessmentController::class, 'getHighRiskPatients']);
        Route::get('/stats', [RiskAssessmentController::class, 'getRiskStats']);
    });
});
