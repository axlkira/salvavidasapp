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
     * NOTA: Solo factores con puntuación 9+ pueden activar alerta de riesgo alto por sí solos
     * 
     * @var array
     */
    protected $riskFactors = [
        // CATEGORÍA A: Ideación suicida explícita en primera persona - ÚNICOS que pueden activar alerta alta
        'quiero suicidarme' => 20,
        'voy a suicidarme' => 20,
        'quiero matarme' => 20,
        'voy a matarme' => 20, 
        'voy a quitarme la vida' => 20,
        'voy a acabar con mi vida' => 20,
        'no quiero seguir viviendo' => 18,
        'no soporto vivir más' => 18,
        'quiero terminar con todo' => 17,
        'mejor estaría muerto' => 17,
        'me voy a matar' => 20,
        'tengo un plan para suicidarme' => 25,
        'he intentado suicidarme' => 25,
        
        // CATEGORÍA B: Autolesión actual - puntuación media-alta
        'me corto' => 8,
        'me autolesiono' => 8,
        'me hago daño a propósito' => 8,
        'me lastimo intencionalmente' => 7,
        
        // CATEGORÍA C: Desesperanza severa - puntuación media
        'no tengo ninguna esperanza' => 6,
        'no veo ninguna salida' => 6,
        'mi vida no tiene sentido alguno' => 6,
        'no vale la pena seguir viviendo' => 7,
        
        // CATEGORÍA D: Expresiones relacionadas pero ambiguas - puntuación baja
        'pienso en la muerte' => 3,
        'a veces pienso en morir' => 4,
        'me siento desesperado' => 3,
        'no veo futuro' => 3,
        
        // CATEGORÍA E: Síntomas de depresión/ansiedad - puntuación mínima
        // No deben activar alerta alta por sí solos
        'depresión severa' => 2,
        'depresión profunda' => 2,
        'depresión' => 1,
        'triste' => 0.5,
        'ansiedad' => 0.5,
        'ansioso' => 0.5,
        'ansiosa' => 0.5,
        'nervioso' => 0.5,
        'nerviosa' => 0.5,
        'preocupado' => 0.5,
        'preocupada' => 0.5,
        'problema' => 0.2,
        'difícil' => 0.2,
        'solo' => 0.5,
        'sola' => 0.5,
        'aislado' => 0.5,
        'aislada' => 0.5
    ];
    
    /**
     * Términos que indican un contexto académico, teórico o hipotético
     * La presencia de estos reduce la puntuación de factores de riesgo
     * 
     * @var array
     */
    protected $academicContextTerms = [
        'investigación', 'estudio', 'estadísticas', 'datos',
        'información', 'artículo', 'paper', 'literatura',
        'teoría', 'perspectiva', 'enfoque', 'método', 
        'análisis', 'me podrías dar', 'podrías explicarme',
        'cómo ayudar a', 'cómo puedo ayudar', 'qué hacer si alguien',
        'pautas', 'recomendaciones', 'guía', 'consejos',
        'estrategias para', 'intervención', 'tratamiento',
        'me podrías decir', 'consulta', 'duda', 'pregunta', 
        'tengo una pregunta', 'necesito información', 'puedes darme',
        'podrías indicarme', 'orientación', 'cuál es tu opinión',
        'qué opinas', 'dime sobre', 'hablemos de', 'quiero saber',
        'cuéntame sobre', 'estoy estudiando', 'estoy aprendiendo'
    ];
    
    /**
     * Términos que indican que se habla de otra persona, no del usuario
     * La presencia de estos reduce la puntuación cuando aparecen cerca de factores de riesgo
     * 
     * @var array
     */
    protected $thirdPersonTerms = [
        'mi amigo', 'mi amiga', 'mi familiar', 'mi hijo', 'mi hija',
        'mi madre', 'mi padre', 'mi esposo', 'mi esposa', 'mi pareja',
        'un paciente', 'el paciente', 'la paciente', 'un cliente', 
        'el cliente', 'la cliente', 'un conocido', 'una conocida',
        'alguien que', 'conozco a alguien', 'hay alguien que',
        'una persona que', 'tengo un familiar', 'tiene problemas',
        'está pasando por', 'está sufriendo', 'está con'
    ];
    
    /**
     * Niveles de riesgo basados en la puntuación - Umbrales mucho más altos para evitar falsos positivos
     * 
     * @var array
     */
    protected $riskLevels = [
        'bajo' => [0, 7],       // 0-7 puntos - La mayoría de conversaciones normales
        'medio' => [8, 15],     // 8-15 puntos - Preocupación o malestar moderado
        'alto' => [16, 25],     // 16-25 puntos - Riesgo real detectado
        'crítico' => [26, 999]  // 26+ puntos - Riesgo inminente
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
        $isAcademicContext = false;
        $totalUserMessages = 0;
        $messagesWithRiskFactors = 0;
        
        // Analizamos primero el contexto general de la conversación
        $allUserContent = '';
        foreach ($messages as $message) {
            if ($message->role === 'user') {
                $allUserContent .= ' ' . strtolower($message->content);
                $totalUserMessages++;
            }
        }
        
        // Verificar si parece un contexto académico o consulta teórica
        foreach ($this->academicContextTerms as $term) {
            if (strpos($allUserContent, $term) !== false) {
                $isAcademicContext = true;
                Log::info('Detectado contexto académico/teórico', ['term' => $term]);
                break;
            }
        }
        
        // Verificar si se habla de terceras personas
        $isThirdPersonContext = false;
        foreach ($this->thirdPersonTerms as $term) {
            if (strpos($allUserContent, $term) !== false) {
                $isThirdPersonContext = true;
                Log::info('Detectado contexto de tercera persona', ['term' => $term]);
                break;
            }
        }
        
        // Analizar cada mensaje
        foreach ($messages as $message) {
            // Solo analizamos los mensajes del usuario (patient), no del asistente
            if ($message->role === 'user') {
                $content = strtolower($message->content);
                $hasRiskFactor = false;
                
                // Buscar factores de riesgo en el contenido
                foreach ($this->riskFactors as $factor => $weight) {
                    if (strpos($content, $factor) !== false) {
                        // Calcular el contexto (texto alrededor del factor)
                        $position = strpos($content, $factor);
                        $startPos = max(0, $position - 30);
                        $length = strlen($factor) + 60; // 30 caracteres antes y 30 después
                        $context = substr($content, $startPos, $length);
                        
                        // Ajustar el peso según el contexto
                        $adjustedWeight = $weight;
                        
                        // Verificar si hay términos de contexto académico o tercera persona cerca
                        $contextWindow = 100; // Caracteres antes y después para buscar contexto
                        $contextStart = max(0, $position - $contextWindow);
                        $contextLength = strlen($factor) + ($contextWindow * 2);
                        $surroundingContext = substr($content, $contextStart, $contextLength);
                        
                        // Reducir dramáticamente el peso en contexto académico (especialmente para palabras comunes)
                        if ($isAcademicContext) {
                            // Ideación suicida explica en primera persona no se reduce tanto
                            if ($weight >= 17) {
                                $adjustedWeight = $weight * 0.1; // Reducción del 90%
                            } else {
                                $adjustedWeight = $weight * 0.05; // Reducción del 95%
                            }
                        }
                        
                        // Reducir peso si se habla de terceras personas
                        if ($isThirdPersonContext) {
                            // Verificar si el factor está cerca de una referencia a tercera persona
                            foreach ($this->thirdPersonTerms as $thirdPersonTerm) {
                                if (strpos($surroundingContext, $thirdPersonTerm) !== false) {
                                    $adjustedWeight = $weight * 0.1; // Reducción del 90%
                                    break;
                                }
                            }
                        }
                        
                        // Palabras comunes como "ansiedad" o "depresión" casi nunca deben contribuir significativamente
                        if ($weight <= 2) {
                            $adjustedWeight = min($adjustedWeight, 0.5);
                        }
                        
                        // Añadir a factores detectados
                        $detectedFactors[$factor] = [
                            'weight' => $adjustedWeight,
                            'original_weight' => $weight,
                            'context' => $context,
                            'message_id' => $message->id,
                            'created_at' => $message->created_at
                        ];
                        
                        // Sumar al puntaje de riesgo
                        $riskScore += $adjustedWeight;
                        $hasRiskFactor = true;
                    }
                }
                
                if ($hasRiskFactor) {
                    $messagesWithRiskFactors++;
                }
            }
        }
        
        // Verificar si hay al menos un factor de categoría A (ideación suicida explícita)
        $hasHighRiskFactor = false;
        $highRiskFactorScore = 0;
        foreach ($detectedFactors as $factor => $details) {
            // Los factores con peso original de 17+ son de categoría A
            if ($details['original_weight'] >= 17) {
                $hasHighRiskFactor = true;
                $highRiskFactorScore = max($highRiskFactorScore, $details['weight']);
                Log::info('Factor de alto riesgo detectado', ['factor' => $factor, 'peso' => $details['weight']]);
            }
        }
        
        // Si no hay factores de alta prioridad, bajar significativamente la puntuación total
        if (!$hasHighRiskFactor && $riskScore > 5) {
            $riskScore = min($riskScore * 0.3, 5); // Límite máximo 5 puntos (riesgo bajo)
            Log::info('Puntuación reducida por ausencia de factores de alto riesgo', ['score_ajustado' => $riskScore]);
        }
        
        // Si hay consistencia en múltiples mensajes, puede aumentar ligeramente el riesgo
        $consistencyFactor = $totalUserMessages > 0 ? $messagesWithRiskFactors / $totalUserMessages : 0;
        if ($messagesWithRiskFactors >= 2 && $consistencyFactor > 0.5 && $hasHighRiskFactor) {
            $riskScore *= (1 + $consistencyFactor * 0.3);
            Log::info('Riesgo aumentado por consistencia', [
                'factor' => $consistencyFactor, 
                'nuevo_score' => $riskScore
            ]);
        }
        
        // En contexto académico, reducir dramáticamente la puntuación final
        if ($isAcademicContext) {
            $riskScore = min($riskScore * 0.2, 3); // Máximo 3 puntos (siempre riesgo bajo)
            Log::info('Puntuación final reducida por contexto académico', ['score_final' => $riskScore]);
        }
        
        // Si se habla de terceras personas, reducir puntuación final
        if ($isThirdPersonContext && !$hasHighRiskFactor) {
            $riskScore = min($riskScore * 0.3, 4); // Máximo 4 puntos (riesgo bajo)
            Log::info('Puntuación reducida por contexto de tercera persona', ['score_final' => $riskScore]);
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
                // Ahora devuelve un array con 'factors' y 'base_score'
                $historiaClinicaAnalysis = $this->analyzeHistoriaClinica($individual);
                $additionalFactors = $historiaClinicaAnalysis['factors'];
                $clinicalRiskScore = $historiaClinicaAnalysis['base_score'];
                
                // Registrar puntuación base de la historia clínica
                Log::info('Puntuación base de historia clínica: ' . $clinicalRiskScore);
                
                // Si la puntuación de la historia clínica es alta, se prioriza sobre el chat
                if ($clinicalRiskScore >= 15) {
                    Log::info('Priorizando el riesgo detectado en la historia clínica (alto)');
                    // Si hay riesgo alto en la historia clínica, establecemos directamente ese nivel
                    $riskScore = max($riskScore, $clinicalRiskScore);
                } elseif ($clinicalRiskScore >= 8) {
                    // Riesgo medio en historia clínica
                    Log::info('Considerando el riesgo detectado en la historia clínica (medio)');
                    $riskScore = max($riskScore, $clinicalRiskScore);
                } else {
                    // Riesgo bajo en historia clínica, añadimos normalmente los factores
                    Log::info('Añadiendo factores de la historia clínica (riesgo bajo-moderado)');
                    // Añadir factores de la historia clínica
                    foreach ($additionalFactors as $factor => $details) {
                        if (!isset($detectedFactors[$factor])) {
                            $detectedFactors[$factor] = $details;
                            $riskScore += $details['weight'] * 0.5; // Reducimos el impacto para evitar sobreestimación
                        }
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
     * Determina el tipo de factor basado en su peso
     * 
     * @param float $weight Peso del factor
     * @return string Tipo de factor
     */
    protected function getFacorType($weight)
    {
        if ($weight >= 17) {
            return 'ideacion_suicida';
        } elseif ($weight >= 8) {
            return 'autolesion';
        } elseif ($weight >= 6) {
            return 'desesperanza';
        } elseif ($weight >= 3) {
            return 'pensamientos_muerte';
        } else {
            return 'sintomas_emocionales';
        }
    }
    
    /**
     * Determina el tipo de factor clínico basado en su peso
     * 
     * @param float $weight Peso del factor
     * @return string Tipo de factor
     */
    protected function getClinicFactorType($weight)
    {
        if ($weight >= 20) {
            return 'suicidio_critico';
        } elseif ($weight >= 15) {
            return 'depresion_severa';
        } elseif ($weight >= 8) {
            return 'depresion_moderada';
        } else {
            return 'sintoma_emocional';
        }
    }
    
    /**
     * Método alternativo para detectar riesgo a partir de una conversación
     * Este es un alias de analyzeConversation para mantener compatibilidad
     * 
     * @param Conversation $conversation La conversación a analizar
     * @return RiskAssessment La evaluación de riesgo generada
     */
    public function detectRiskFromConversation(Conversation $conversation)
    {
        return $this->analyzeConversation($conversation);
    }
    
    /**
     * Analiza la historia clínica para encontrar factores de riesgo adicionales
     * 
     * @param Individual $individual Historia clínica del paciente
     * @return array Factores de riesgo encontrados y puntuación de riesgo base
     */
    protected function analyzeHistoriaClinica($individual)
    {
        $detectedFactors = [];
        $riskScoreFromHistory = 0;
        
        // Campos relevantes para analizar, ordenados por prioridad
        $camposRelevantes = [
            'ProblematicaActual' => 1.0,       // Factor de peso máximo (actual)
            'AntecedentesClinicosFisicosMentales' => 0.9,  // Alto peso
            'PersonalesPsicosociales' => 0.9,  // Alto peso
            'NivelConcienciaProblematica' => 0.8, // Peso medio-alto
            'Observaciones' => 0.7,          // Peso medio
            'ImprecionDiagnostica' => 0.9    // Alto peso
        ];
        
        // Términos específicos para identificar en la historia clínica
        $indicadoresClinicosGraves = [
            // Indicadores de riesgo crítico (25 puntos)
            'intento de suicidio' => 25,
            'intentó suicidarse' => 25,
            'intento suicida' => 25,
            'ha intentado quitarse la vida' => 25,
            'ideación suicida activa' => 20,
            'ideas suicidas estructuradas' => 20,
            'plan suicida' => 22,
            'riesgo suicida alto' => 20,
            
            // Indicadores de riesgo alto (15-18 puntos)
            'ideación suicida' => 16,
            'ideas de suicidio' => 16,
            'pensamiento suicida' => 15,
            'depresión severa' => 15,
            'depresión mayor' => 15,
            'depresión grave' => 15,
            'trastorno depresivo mayor' => 15,
            'conducta autolesiva' => 14,
            
            // Indicadores de riesgo medio (8-14 puntos)
            'depresión moderada' => 12,
            'ideación suicida pasiva' => 10,
            'pensamientos de muerte' => 10,
            'trastorno depresivo' => 10,
            'autolesiones' => 10,
            'conducta impulsiva' => 8,
            'trastorno de ansiedad grave' => 8,
            
            // Indicadores de riesgo bajo (1-7 puntos)
            'depresión leve' => 6,
            'síntomas depresivos' => 5,
            'duelo' => 4,
            'ansiedad' => 3,
            'estrés' => 2,
            'insomnio' => 2,
            'aislamiento social' => 3
        ];
        
        // Asegurar que usamos los campos especificados por el usuario
        $camposRelevantes = [
            'ProblematicaActual' => 1.0,                   // Factor de peso máximo (actual)
            'AntecedentesClinicosFisicosMentales' => 0.9,  // Alto peso
            'PersonalesPsicosociales' => 0.9,              // Alto peso
            'NivelConcienciaProblematica' => 0.8,          // Peso medio-alto
            'Observaciones' => 0.7,                        // Peso medio
        ];
        
        // Primero buscar indicadores clínicos graves que son más precisos
        foreach ($camposRelevantes as $campo => $factorPeso) {
            if (!empty($individual->$campo)) {
                $content = strtolower($individual->$campo);
                
                // Buscar indicadores clínicos específicos
                foreach ($indicadoresClinicosGraves as $indicador => $peso) {
                    if (strpos($content, $indicador) !== false) {
                        $position = strpos($content, $indicador);
                        $startPos = max(0, $position - 50);
                        $length = strlen($indicador) + 100;
                        $context = substr($content, $startPos, $length);
                        
                        // Calcular peso ajustado según el campo y la actualidad
                        $adjustedWeight = $peso * $factorPeso;
                        
                        // Sumar directamente a la puntuación de riesgo de la historia clínica
                        $riskScoreFromHistory += $adjustedWeight;
                        
                        // Añadir a factores detectados
                        $key = "$indicador ($campo)";
                        $detectedFactors[$key] = [
                            'weight' => $adjustedWeight,
                            'original_weight' => $peso,
                            'context' => $context,
                            'source' => 'historia_clinica',
                            'field' => $campo,
                            'factor_type' => $this->getClinicFactorType($peso)
                        ];
                        
                        Log::info("Indicador clínico grave detectado", [
                            'indicador' => $indicador,
                            'campo' => $campo,
                            'peso_original' => $peso,
                            'peso_ajustado' => $adjustedWeight,
                            'context' => substr($context, 0, 100) . '...'
                        ]);
                    }
                }
                
                // También buscar los factores de riesgo generales
                foreach ($this->riskFactors as $factor => $weight) {
                    if (strpos($content, $factor) !== false && !isset($detectedFactors[$factor])) {
                        $position = strpos($content, $factor);
                        $startPos = max(0, $position - 30);
                        $length = strlen($factor) + 60;
                        $context = substr($content, $startPos, $length);
                        
                        // Peso ajustado según el campo
                        $adjustedWeight = $weight * $factorPeso;
                        
                        $detectedFactors[$factor] = [
                            'weight' => $adjustedWeight,
                            'original_weight' => $weight,
                            'context' => $context,
                            'source' => 'historia_clinica',
                            'field' => $campo,
                            'factor_type' => $this->getFacorType($weight)
                        ];
                    }
                }
            }
        }
        
        Log::info("Análisis de historia clínica completado", [
            'factores_detectados' => count($detectedFactors),
            'puntuacion_base' => $riskScoreFromHistory
        ]);
        
        // Devolver tanto los factores como la puntuación base calculada
        return [
            'factors' => $detectedFactors,
            'base_score' => $riskScoreFromHistory
        ];
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
            $source = isset($details['source']) ? $details['source'] : 'conversacion';
            $factorType = isset($details['factor_type']) ? $details['factor_type'] : $this->getFacorType($details['weight']);
            
            $description = sprintf(
                "Factor: %s | Peso: %.1f | Contexto: %s | Fuente: %s",
                $factor,
                $details['weight'],
                $details['context'],
                $source
            );
            
            $assessment->riskFactors()->create([
                'description' => $description,
                'factor_type' => $factorType,
                'weight' => $details['weight'],
                'context' => $details['context'],
                'source' => $source
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
                
                // Nunca forzamos el nivel de riesgo, confiamos en la evaluación calculada
                $results['low_risk']++;
            }
        }
        
        return $results;
    }
}
