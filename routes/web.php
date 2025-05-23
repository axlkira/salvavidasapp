<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\welcome;
use App\Http\Controllers\FormController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\Bloque1NuevoController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\RiskController;
use App\Http\Controllers\RiskAssessmentController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


// Ruta original
Route::get('/', [welcome::class, 'welcome'])->name('welcome');

// Ruta de demostración directa para SalvaVidas
Route::get('/demo', [App\Http\Controllers\DashboardController::class, 'index'])->name('demo');

Route::get('/bloque1nuevo', [Bloque1NuevoController::class, 'create'])->name('bloque1nuevo.create');
Route::post('/bloque1nuevo', [Bloque1NuevoController::class, 'store'])->name('bloque1nuevo.store');

// Rutas para SalvaVidas App
Route::prefix('salvavidasapp')->group(function () {
    // Rutas de demostración (sin autenticación por ahora)
    Route::group([], function () {
        // Dashboard
        Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
        
        // Rutas para el chat
        Route::prefix('chat')->group(function () {
            Route::get('/', [ChatController::class, 'index'])->name('chat.index');
            Route::get('/conversation/{id}', [ChatController::class, 'show'])->name('chat.show');
            Route::get('/patients', [ChatController::class, 'patients'])->name('chat.patients');
            Route::post('/search-patients', [ChatController::class, 'searchPatients'])->name('chat.search-patients');
            
            // Nuevas rutas para la funcionalidad real de chat
            Route::post('/create', [ChatController::class, 'create'])->name('chat.create');
            Route::post('/conversation/{id}/send', [ChatController::class, 'sendMessage'])->name('chat.send-message');
        });
        
        // Rutas para evaluación de riesgo general
        Route::prefix('risk')->group(function () {
            Route::get('/', [RiskController::class, 'index'])->name('risk.index');
            Route::get('/assessment/{id}', [RiskController::class, 'show'])->name('risk.show');
            Route::get('/create', [RiskController::class, 'create'])->name('risk.create');
            Route::get('/dashboard', [RiskController::class, 'dashboard'])->name('risk.dashboard');
            Route::get('/patient/{document}', [RiskController::class, 'patient'])->name('risk.patient');
        });
        
        // Rutas para la gestión detallada de evaluaciones de riesgo
        Route::prefix('risk-assessment')->group(function () {
            Route::get('/', [RiskAssessmentController::class, 'index'])->name('risk-assessment.index');
            Route::get('/alerts', [RiskAssessmentController::class, 'alerts'])->name('risk-assessment.alerts');
            Route::get('/{id}', [RiskAssessmentController::class, 'show'])->name('risk-assessment.show');
            Route::put('/{id}/update-status', [RiskAssessmentController::class, 'updateStatus'])->name('risk-assessment.update-status');
            Route::put('/{id}/mark-critical', [RiskAssessmentController::class, 'markAsCritical'])->name('risk-assessment.mark-critical');
        });
    });
});
