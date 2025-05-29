<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\RiskAssessment;
use App\Models\RiskFactor;
use App\Models\Individual;
use App\Models\PrincipalIntegrante;
use Illuminate\Support\Facades\Log;

class RiskDetectionService
{
    /**
     * Factores de riesgo con su ponderación
     * Los valores más altos indican mayor riesgo
     * 
     * @var array
     */
    protected $riskFactors = [
        // Ideación suicida explícita
        'suicidio' => 10,
        'matarme' => 10,
        'quitarme la vida' => 10,
        'acabar con mi vida' => 10,
        'no quiero vivir' => 9,
        
        // Autolesión
        'autolesion' => 8,
        'hacerme daño' => 8,
        'cortarme' => 8,
        'lastimarme' => 7,
        
        // Desesperanza
        'desesperanza' => 7,
        'sin esperanza' => 7,
        'sin salida' => 7,
        'no tiene sentido' => 6,
        
        // Depresión
        'depresión' => 5,
        'depresion' => 5,
        'tristeza' => 4,
        'vacío' => 4,
        
        // Ansiedad
        'ansiedad' => 4,
        'angustia' => 4,
        'pánico' => 5,
        'miedo' => 3,
        
        // Psicosis
        'voces' => 6,
        'alucinaciones' => 6,
        'persiguen' => 5,
        
        // Impulsividad
        'impulsivo' => 5,
        'control' => 4,
        'explosivo' => 5,
        
        // Aislamiento
        'solo' => 4,
        'aislado' => 5,
        'nadie me entiende' => 5,
        'abandonado' => 5,
        
        // Consumo de sustancias
        'alcohol' => 4,
        'drogas' => 4,
        'adicción' => 5,
        
        // Trauma
        'abuso' => 6,
        'violación' => 7,
        'trauma' => 6,
        'maltrato' => 6,
    ];
    
    /**
     * Niveles de riesgo basados en la puntuación
     * 
     * @var array
     */
    protected $riskLevels = [
        'bajo' => [0, 30],     // 0-30 puntos
        'medio' => [31, 60],   // 31-60 puntos
        'alto' => [61, 100],   // 61-100 puntos
        'crítico' => [101, 999] // 101+ puntos
    ];
    
    /**
     * Analiza una conversación para detectar factores de riesgo y generar una evaluación
     * 
     * @param Conversation $conversation La conversación a analizar
     * @return RiskAssessment La evaluación de riesgo generada
     */
    public function analyzeConversation(Conversation $conversation)
    {
        Log::info('Iniciando análisis de riesgo para conversación', ['conversation_id' => $conversation->id]);
        
        // Obtener todos los mensajes de la conversación, excluyendo los del sistema
        $messages = $conversation->messages()
                                ->where('role', '!=', 'system')
                                ->orderBy('created_at', 'asc')
                                ->get();
        
        // Verificar si hay suficientes mensajes para analizar
        if ($messages->count() < 2) {
            Log::info('Conversación con pocos mensajes, no se realiza análisis', ['count' => $messages->count()]);
            return null;
        }
        
        // Variables para el análisis
        $riskScore = 0;
        $detectedFactors = [];
        $patientDocument = $conversation->patient_document;
        
        // Analizar cada mensaje
        foreach ($messages as $message) {
            // Solo analizamos los mensajes del usuario (patient), no del asistente
            if ($message->role === 'user') {
                $content = strtolower($message->content);
                
                // Buscar factores de riesgo en el contenido
                foreach ($this->riskFactors as $factor => $weight) {
                    if (strpos($content, $factor) !== false) {
                        // Calcular el contexto (texto alrededor del factor)
                        $position = strpos($content, $factor);
                        $startPos = max(0, $position - 30);
                        $length = strlen($factor) + 60; // 30 caracteres antes y 30 después
                        $context = substr($content, $startPos, $length);
                        
                        // Añadir a factores detectados
                        $detectedFactors[$factor] = [
                            'weight' => $weight,
                            'context' => $context,
                            'message_id' => $message->id,
                            'created_at' => $message->created_at
                        ];
                        
                        // Sumar al puntaje de riesgo
                        $riskScore += $weight;
                    }
                }
            }
        }
        
        // Determinar nivel de riesgo
        $riskLevel = $this->calculateRiskLevel($riskScore);
        
        // Obtener información adicional si hay documento de paciente
        $individualId = null;
        if ($patientDocument) {
            $individual = Individual::where('Documento', $patientDocument)
                            ->orderBy('FechaInicio', 'desc')
                            ->first();
            
            if ($individual) {
                $individualId = $individual->id;
                
                // Buscar factores de riesgo adicionales en la historia clínica
                $additionalFactors = $this->analyzeHistoriaClinica($individual);
                
                // Añadir factores de la historia clínica
                foreach ($additionalFactors as $factor => $details) {
                    if (!isset($detectedFactors[$factor])) {
                        $detectedFactors[$factor] = $details;
                        $riskScore += $details['weight'];
                    }
                }
                
                // Recalcular nivel de riesgo
                $riskLevel = $this->calculateRiskLevel($riskScore);
            }
        }
        
        // Crear o actualizar evaluación de riesgo
        $assessment = RiskAssessment::updateOrCreate(
            ['conversation_id' => $conversation->id],
            [
                'individual_id' => $individualId,
                'patient_document' => $patientDocument,
                'risk_score' => $riskScore,
                'risk_level' => $riskLevel,
                'provider' => 'sistema',
                'model' => 'risk-detection-v1',
                'status' => 'pending'
            ]
        );
        
        // Guardar factores de riesgo detectados
        $this->saveRiskFactors($assessment, $detectedFactors);
        
        Log::info('Análisis de riesgo completado', [
            'conversation_id' => $conversation->id,
            'risk_score' => $riskScore,
            'risk_level' => $riskLevel,
            'factors_count' => count($detectedFactors)
        ]);
        
        return $assessment;
    }
    
    /**
     * Calcula el nivel de riesgo basado en la puntuación
     * 
     * @param float $score Puntuación de riesgo
     * @return string Nivel de riesgo (bajo, medio, alto, crítico)
     */
    protected function calculateRiskLevel($score)
    {
        foreach ($this->riskLevels as $level => $range) {
            if ($score >= $range[0] && $score <= $range[1]) {
                return $level;
            }
        }
        
        return 'bajo'; // Valor por defecto
    }
    
    /**
     * Analiza la historia clínica para encontrar factores de riesgo adicionales
     * 
     * @param Individual $individual Historia clínica del paciente
     * @return array Factores de riesgo encontrados
     */
    protected function analyzeHistoriaClinica($individual)
    {
        $detectedFactors = [];
        
        // Campos relevantes para analizar
        $camposRelevantes = [
            'AntecedentesClinicosFisicosMentales',
            'PersonalesPsicosociales',
            'ProblematicaActual',
            'ImprecionDiagnostica',
            'Observaciones'
        ];
        
        // Analizar cada campo
        foreach ($camposRelevantes as $campo) {
            if (!empty($individual->$campo)) {
                $content = strtolower($individual->$campo);
                
                // Buscar factores de riesgo
                foreach ($this->riskFactors as $factor => $weight) {
                    if (strpos($content, $factor) !== false && !isset($detectedFactors[$factor])) {
                        // Calcular el contexto
                        $position = strpos($content, $factor);
                        $startPos = max(0, $position - 30);
                        $length = strlen($factor) + 60;
                        $context = substr($content, $startPos, $length);
                        
                        // Añadir factor con peso reducido (es histórico, no actual)
                        $detectedFactors[$factor] = [
                            'weight' => $weight * 0.7, // Reducir peso por ser histórico
                            'context' => $context,
                            'source' => 'historia_clinica',
                            'field' => $campo
                        ];
                    }
                }
            }
        }
        
        return $detectedFactors;
    }
    
    /**
     * Guarda los factores de riesgo detectados en la base de datos
     * 
     * @param RiskAssessment $assessment Evaluación de riesgo
     * @param array $detectedFactors Factores detectados
     */
    protected function saveRiskFactors($assessment, $detectedFactors)
    {
        // Eliminar factores anteriores
        $assessment->riskFactors()->delete();
        
        // Crear nuevos factores
        foreach ($detectedFactors as $factor => $details) {
            $description = sprintf(
                "Factor: %s | Peso: %.1f | Contexto: %s | Fuente: %s",
                $factor,
                $details['weight'],
                $details['context'],
                isset($details['source']) ? $details['source'] : 'conversación'
            );
            
            $assessment->riskFactors()->create([
                'description' => $description
            ]);
        }
    }
    
    /**
     * Genera una evaluación de riesgo para todas las conversaciones activas
     * que no han sido evaluadas recientemente
     * 
     * @param int $daysThreshold Número de días desde la última evaluación
     * @return array Resultados del análisis
     */
    public function analyzeRecentConversations($daysThreshold = 7)
    {
        $results = [
            'total' => 0,
            'analyzed' => 0,
            'high_risk' => 0,
            'medium_risk' => 0,
            'low_risk' => 0
        ];
        
        // Obtener conversaciones recientes con actividad
        $recentConversations = Conversation::whereHas('messages', function($query) use ($daysThreshold) {
            $query->where('created_at', '>=', now()->subDays($daysThreshold));
        })->get();
        
        $results['total'] = $recentConversations->count();
        
        // Analizar cada conversación
        foreach ($recentConversations as $conversation) {
            $assessment = $this->analyzeConversation($conversation);
            
            if ($assessment) {
                $results['analyzed']++;
                
                // Contar por nivel de riesgo
                if ($assessment->risk_level === 'alto' || $assessment->risk_level === 'crítico') {
                    $results['high_risk']++;
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
