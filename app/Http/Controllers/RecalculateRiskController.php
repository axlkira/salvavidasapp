<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RiskAssessment;
use App\Models\Conversation;
use App\Services\RiskDetectionService;
use Illuminate\Support\Facades\Log;

class RecalculateRiskController extends Controller
{
    /**
     * Recalcular todas las evaluaciones de riesgo
     */
    public function recalculateAll(Request $request)
    {
        // Crear instancia del servicio de detección
        $riskService = new RiskDetectionService();
        
        // Obtener todas las evaluaciones
        $assessments = RiskAssessment::with('conversation')->get();
        $recalculated = 0;
        $errors = 0;
        
        foreach ($assessments as $assessment) {
            try {
                // Obtener la conversación asociada
                $conversation = $assessment->conversation;
                
                if ($conversation) {
                    // Guardar valores anteriores para comparar
                    $oldLevel = $assessment->risk_level;
                    $oldScore = $assessment->risk_score;
                    
                    // Recalcular la evaluación
                    $updatedAssessment = $riskService->analyzeConversation($conversation);
                    
                    // Recargar los datos actualizados
                    $assessment->refresh();
                    
                    // Registrar el cambio
                    Log::info('Evaluación recalculada', [
                        'id' => $assessment->id,
                        'old_level' => $oldLevel,
                        'new_level' => $assessment->risk_level,
                        'old_score' => $oldScore,
                        'new_score' => $assessment->risk_score
                    ]);
                    
                    $recalculated++;
                }
            } catch (\Exception $e) {
                Log::error('Error al recalcular evaluación: ' . $e->getMessage(), [
                    'assessment_id' => $assessment->id
                ]);
                $errors++;
            }
        }
        
        // Redirigir a la página de evaluaciones con mensaje de éxito
        return redirect()->route('risk-assessment.index')
            ->with('success', "Se recalcularon {$recalculated} evaluaciones de riesgo con el nuevo algoritmo. Errores: {$errors}");
    }
    
    /**
     * Recalcular una evaluación específica
     */
    public function recalculateSingle($id)
    {
        // Obtener la evaluación
        $assessment = RiskAssessment::with('conversation')->findOrFail($id);
        $riskService = new RiskDetectionService();
        
        try {
            // Obtener la conversación
            $conversation = $assessment->conversation;
            
            if ($conversation) {
                // Guardar valores anteriores
                $oldLevel = $assessment->risk_level;
                $oldScore = $assessment->risk_score;
                
                // Recalcular
                $updatedAssessment = $riskService->analyzeConversation($conversation);
                
                // Recargar los datos actualizados
                $assessment->refresh();
                
                // Registrar el cambio
                Log::info('Evaluación individual recalculada', [
                    'id' => $assessment->id,
                    'old_level' => $oldLevel,
                    'new_level' => $assessment->risk_level,
                    'old_score' => $oldScore,
                    'new_score' => $assessment->risk_score
                ]);
                
                return redirect()->route('risk-assessment.show', $id)
                    ->with('success', 'Evaluación recalculada correctamente con el nuevo algoritmo.');
            } else {
                return redirect()->route('risk-assessment.show', $id)
                    ->with('error', 'No se pudo recalcular la evaluación: Conversación no encontrada.');
            }
        } catch (\Exception $e) {
            Log::error('Error al recalcular evaluación individual: ' . $e->getMessage(), [
                'assessment_id' => $assessment->id
            ]);
            
            return redirect()->route('risk-assessment.show', $id)
                ->with('error', 'Error al recalcular la evaluación: ' . $e->getMessage());
        }
    }
}
