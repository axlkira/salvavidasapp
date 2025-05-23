<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\RiskAssessment;
use App\Models\RiskFactor;
use App\Models\WarningSign;
use App\Models\User;
use App\Services\AI\AIService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Exception;

class AIRiskAnalysisService
{
    /**
     * Servicio de IA para analizar el texto
     */
    protected $aiService;
    
    /**
     * Servicio tradicional de detección de riesgo
     */
    protected $riskDetectionService;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->aiService = new AIService();
        $this->riskDetectionService = new RiskDetectionService();
    }
    
    /**
     * Analiza una conversación usando tanto palabras clave como IA para detectar riesgo suicida
     * 
     * @param Conversation $conversation La conversación a analizar
     * @param string|null $aiProvider Proveedor de IA a utilizar (null para usar el predeterminado)
     * @return RiskAssessment La evaluación de riesgo generada o actualizada
     */
    public function analyzeConversation(Conversation $conversation, $aiProvider = null)
    {
        try {
            Log::info('Iniciando análisis de riesgo con IA para conversación', [
                'conversation_id' => $conversation->id, 
                'ai_provider' => $aiProvider ?? config('ai.default_provider')
            ]);
            
            // Primero obtenemos el análisis basado en palabras clave
            $keywordAssessment = $this->riskDetectionService->analyzeConversation($conversation);
            
            // Si no hay suficientes mensajes, terminamos aquí
            if (!$keywordAssessment) {
                Log::info('Análisis con palabras clave no produjo resultados, terminando');
                return null;
            }
            
            // Preparamos los datos para el análisis con IA
            $patientData = $this->preparePatientDataForAI($conversation);
            
            // Realizamos el análisis con IA
            $aiResult = $this->aiService->analyzePatientRisk($patientData, $aiProvider);
            
            if (!isset($aiResult['success']) || !$aiResult['success']) {
                Log::warning('El análisis con IA falló, usando solo análisis por palabras clave', [
                    'error' => $aiResult['error'] ?? 'Error desconocido'
                ]);
                
                // Si falla el análisis con IA, usamos solo el análisis por palabras clave
                return $keywordAssessment;
            }
            
            // Combinar los resultados del análisis por palabras clave con el análisis de IA
            $combinedAssessment = $this->combineAssessments($keywordAssessment, $aiResult);
            
            // Guardar factores de riesgo y señales de advertencia del análisis de IA
            $this->saveAIDetectedFactors($combinedAssessment, $aiResult);
            
            // Verificar si es un caso crítico y generar alertas si es necesario
            if ($this->isCriticalCase($combinedAssessment)) {
                $this->generateCriticalAlerts($combinedAssessment, $conversation);
            }
            
            Log::info('Análisis de riesgo con IA completado', [
                'conversation_id' => $conversation->id,
                'risk_score' => $combinedAssessment->risk_score,
                'risk_level' => $combinedAssessment->risk_level,
                'provider' => $aiResult['provider'] ?? 'default'
            ]);
            
            return $combinedAssessment;
        } catch (Exception $e) {
            Log::error('Error en análisis de riesgo con IA: ' . $e->getMessage(), [
                'conversation_id' => $conversation->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            // En caso de error, intentamos devolver al menos el análisis por palabras clave
            return $keywordAssessment ?? null;
        }
    }
    
    /**
     * Prepara los datos del paciente para el análisis con IA
     * 
     * @param Conversation $conversation La conversación a analizar
     * @return array Datos estructurados para el análisis con IA
     */
    protected function preparePatientDataForAI(Conversation $conversation)
    {
        // Obtenemos los mensajes de la conversación (excluyendo mensajes del sistema)
        $messages = $conversation->messages()
                                ->where('role', '!=', 'system')
                                ->orderBy('created_at', 'asc')
                                ->get();
        
        // Construimos el texto de la conversación para el análisis
        $conversationText = '';
        foreach ($messages as $message) {
            $role = $message->role === 'user' ? 'Paciente' : 'Asistente';
            $conversationText .= "$role: {$message->content}\n\n";
        }
        
        // Obtenemos información del paciente si está disponible
        $patientInfo = [];
        if ($conversation->patient_document) {
            $patient = \App\Models\PrincipalIntegrante::where('documento', $conversation->patient_document)->first();
            if ($patient) {
                $patientInfo = [
                    'name' => trim($patient->nombre1 . ' ' . $patient->nombre2 . ' ' . $patient->apellido1 . ' ' . $patient->apellido2),
                    'age' => $patient->edad ?? 'No disponible',
                    'gender' => $patient->sexo ?? 'No disponible',
                    'document' => $patient->documento
                ];
            }
        }
        
        // Obtenemos historia clínica si está disponible
        $clinicalHistory = [];
        if ($conversation->patient_document) {
            $individual = \App\Models\Individual::where('Documento', $conversation->patient_document)
                            ->orderBy('FechaInicio', 'desc')
                            ->first();
            
            if ($individual) {
                $clinicalHistory = [
                    'psychiatric_history' => $individual->AntecedentesClinicosFisicosMentales ?? 'No disponible',
                    'psychosocial_factors' => $individual->PersonalesPsicosociales ?? 'No disponible',
                    'current_problem' => $individual->ProblematicaActual ?? 'No disponible',
                    'diagnostic_impression' => $individual->ImprecionDiagnostica ?? 'No disponible'
                ];
            }
        }
        
        // Construimos la estructura final de datos
        return [
            'conversation_text' => $conversationText,
            'patient_info' => $patientInfo,
            'clinical_history' => $clinicalHistory,
            'conversation_id' => $conversation->id,
            'message_count' => $messages->count(),
            'conversation_start' => $conversation->created_at->format('Y-m-d H:i:s'),
            'last_message' => $messages->last() ? $messages->last()->created_at->format('Y-m-d H:i:s') : null
        ];
    }
    
    /**
     * Combina los resultados del análisis por palabras clave con el análisis de IA
     * 
     * @param RiskAssessment $keywordAssessment Evaluación basada en palabras clave
     * @param array $aiResult Resultado del análisis con IA
     * @return RiskAssessment Evaluación combinada
     */
    protected function combineAssessments(RiskAssessment $keywordAssessment, array $aiResult)
    {
        // Obtenemos el nivel de riesgo del análisis de IA
        $aiRiskLevel = strtolower($aiResult['risk_level'] ?? 'bajo');
        
        // Normalizamos el nivel de riesgo de IA para asegurar compatibilidad
        if ($aiRiskLevel === 'critical' || $aiRiskLevel === 'critical') {
            $aiRiskLevel = 'crítico';
        }
        
        // Obtenemos el puntaje de riesgo del análisis de IA (normalizado a nuestra escala)
        $aiRiskScore = isset($aiResult['risk_score']) ? ($aiResult['risk_score'] * 100) : 0;
        
        // Calculamos un puntaje combinado (dando más peso al análisis de IA)
        $combinedScore = ($keywordAssessment->risk_score * 0.4) + ($aiRiskScore * 0.6);
        
        // Asegurarnos de que el puntaje combinado sea al menos 75 para casos de alto riesgo
        if ($aiRiskLevel === 'alto' || $aiRiskLevel === 'crítico') {
            $combinedScore = max($combinedScore, 75);
        }
        
        // Determinamos el nivel de riesgo final
        // Seleccionamos el nivel más alto entre ambos análisis para mayor seguridad
        $keywordRiskLevel = $keywordAssessment->risk_level;
        $combinedRiskLevel = $this->getHigherRiskLevel($keywordRiskLevel, $aiRiskLevel);
        
        // Actualizamos la evaluación con los valores combinados
        $keywordAssessment->risk_score = $combinedScore;
        $keywordAssessment->risk_level = $combinedRiskLevel;
        $keywordAssessment->provider = 'sistema+ia';
        $keywordAssessment->model = $aiResult['model'] ?? 'combined-analysis-v1';
        
        // Actualizar estado a 'urgent' para casos de alto riesgo o críticos
        if (in_array(strtolower($combinedRiskLevel), ['alto', 'crítico', 'critico'])) {
            $keywordAssessment->status = 'urgent';
        }
        
        $keywordAssessment->save();
        
        return $keywordAssessment;
    }
    
    /**
     * Devuelve el nivel de riesgo más alto entre dos niveles
     * 
     * @param string $level1 Primer nivel de riesgo
     * @param string $level2 Segundo nivel de riesgo
     * @return string El nivel de riesgo más alto
     */
    protected function getHigherRiskLevel($level1, $level2)
    {
        $levels = [
            'bajo' => 1,
            'medio' => 2,
            'alto' => 3,
            'crítico' => 4,
            'critico' => 4 // Variante sin tilde
        ];
        
        $level1Value = $levels[strtolower($level1)] ?? 1;
        $level2Value = $levels[strtolower($level2)] ?? 1;
        
        if ($level1Value >= $level2Value) {
            return $level1;
        } else {
            return $level2;
        }
    }
    
    /**
     * Guarda los factores de riesgo y señales de advertencia detectados por la IA
     * 
     * @param RiskAssessment $assessment Evaluación de riesgo
     * @param array $aiResult Resultado del análisis con IA
     */
    protected function saveAIDetectedFactors(RiskAssessment $assessment, array $aiResult)
    {
        // Guardar factores de riesgo detectados por la IA
        if (!empty($aiResult['risk_factors'])) {
            foreach ($aiResult['risk_factors'] as $factor) {
                // Si es un array, extraemos la descripción
                $description = is_array($factor) ? ($factor['description'] ?? $factor[0] ?? '') : $factor;
                
                if (!empty($description)) {
                    RiskFactor::create([
                        'risk_assessment_id' => $assessment->id,
                        'description' => $description,
                        'source' => 'ia',
                        'weight' => is_array($factor) ? ($factor['weight'] ?? 1) : 1
                    ]);
                }
            }
        }
        
        // Guardar señales de advertencia detectadas por la IA
        if (!empty($aiResult['warning_signs'])) {
            foreach ($aiResult['warning_signs'] as $sign) {
                // Si es un array, extraemos la descripción
                $description = is_array($sign) ? ($sign['description'] ?? $sign[0] ?? '') : $sign;
                
                if (!empty($description)) {
                    WarningSign::create([
                        'risk_assessment_id' => $assessment->id,
                        'description' => $description,
                        'source' => 'ia',
                        'severity' => is_array($sign) ? ($sign['severity'] ?? 'medium') : 'medium'
                    ]);
                }
            }
        }
    }
    
    /**
     * Determina si un caso es crítico basado en la evaluación de riesgo
     * 
     * @param RiskAssessment $assessment Evaluación de riesgo
     * @return bool True si es un caso crítico
     */
    protected function isCriticalCase(RiskAssessment $assessment)
    {
        // Consideramos críticos los casos de riesgo alto y crítico
        return in_array(strtolower($assessment->risk_level), ['alto', 'crítico', 'critico']);
    }
    
    /**
     * Genera alertas para casos críticos
     * 
     * @param RiskAssessment $assessment Evaluación de riesgo
     * @param Conversation $conversation Conversación analizada
     */
    protected function generateCriticalAlerts(RiskAssessment $assessment, Conversation $conversation)
    {
        // Registrar alerta en el log
        Log::alert('ALERTA CRÍTICA: Paciente con alto riesgo de suicidio detectado', [
            'conversation_id' => $conversation->id,
            'patient_document' => $conversation->patient_document,
            'risk_level' => $assessment->risk_level,
            'risk_score' => $assessment->risk_score,
            'assessment_id' => $assessment->id,
            'date' => now()->format('Y-m-d H:i:s')
        ]);
        
        // Actualizar el estado de la evaluación para marcarla como crítica
        $assessment->status = 'urgent';
        $assessment->save();
        
        // TODO: Implementar notificaciones por email o SMS a profesionales
        // Esta funcionalidad requeriría configuración adicional y se implementaría en una fase posterior
        
        // TODO: Implementar alertas en el dashboard para profesionales
        // Esta funcionalidad requeriría modificar las vistas del dashboard
    }
    
    /**
     * Analiza todas las conversaciones recientes para detectar riesgo
     * 
     * @param int $daysThreshold Número de días desde la última actividad
     * @param string|null $aiProvider Proveedor de IA a utilizar
     * @return array Resultados del análisis
     */
    public function analyzeRecentConversations($daysThreshold = 7, $aiProvider = null)
    {
        $results = [
            'total' => 0,
            'analyzed' => 0,
            'high_risk' => 0,
            'medium_risk' => 0,
            'low_risk' => 0,
            'critical_cases' => []
        ];
        
        // Obtener conversaciones recientes con actividad
        $recentConversations = Conversation::whereHas('messages', function($query) use ($daysThreshold) {
            $query->where('created_at', '>=', now()->subDays($daysThreshold));
        })->get();
        
        $results['total'] = $recentConversations->count();
        
        // Analizar cada conversación
        foreach ($recentConversations as $conversation) {
            $assessment = $this->analyzeConversation($conversation, $aiProvider);
            
            if ($assessment) {
                $results['analyzed']++;
                
                // Contar por nivel de riesgo
                if (in_array(strtolower($assessment->risk_level), ['alto', 'crítico', 'critico'])) {
                    $results['high_risk']++;
                    
                    // Guardar datos de los casos críticos para el reporte
                    $patientName = 'Paciente anónimo';
                    if ($conversation->patient_document) {
                        $patient = \App\Models\PrincipalIntegrante::where('documento', $conversation->patient_document)->first();
                        if ($patient) {
                            $patientName = trim($patient->nombre1 . ' ' . $patient->nombre2 . ' ' . $patient->apellido1 . ' ' . $patient->apellido2);
                        }
                    }
                    
                    $results['critical_cases'][] = [
                        'assessment_id' => $assessment->id,
                        'conversation_id' => $conversation->id,
                        'patient_name' => $patientName,
                        'patient_document' => $conversation->patient_document,
                        'risk_level' => $assessment->risk_level,
                        'risk_score' => $assessment->risk_score,
                        'created_at' => $assessment->created_at->format('Y-m-d H:i:s')
                    ];
                } elseif ($assessment->risk_level === 'medio') {
                    $results['medium_risk']++;
                } else {
                    $results['low_risk']++;
                }
            }
        }
        
        return $results;
    }
}
