<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class OpenAIProvider implements AIProviderInterface
{
    protected $baseUrl;
    protected $model;
    protected $apiKey;

    /**
     * Create a new OpenAI provider instance.
     * 
     * @param string|null $baseUrl Base URL del servicio OpenAI
     * @param string|null $model Modelo a utilizar (ej: gpt-4o-mini)
     * @param string|null $apiKey API Key de OpenAI
     */
    public function __construct($baseUrl = null, $model = null, $apiKey = null)
    {
        $this->baseUrl = $baseUrl ?? config('ai.providers.openai.base_url', 'https://api.openai.com/v1');
        $this->model = $model ?? config('ai.providers.openai.model', 'gpt-4o-mini');
        $this->apiKey = $apiKey ?? config('ai.providers.openai.api_key', '');
    }

    /**
     * Send a chat message to OpenAI and get a response
     * 
     * @param array $messages Array of messages in the format [{role, content}]
     * @return array Response from OpenAI
     */
    public function chat(array $messages)
    {
        try {
            // Validar API key
            if (empty($this->apiKey)) {
                Log::error('OpenAI API key no configurada');
                return [
                    'success' => false,
                    'error' => 'API key de OpenAI no configurada',
                    'provider' => 'openai'
                ];
            }

            // Preparar el cuerpo de la petición
            $requestBody = [
                'model' => $this->model,
                'messages' => $messages,
                'temperature' => 0.7,
                'max_tokens' => 2000
            ];

            // Para debugging
            Log::info('Enviando solicitud a OpenAI', [
                'url' => "{$this->baseUrl}/chat/completions", 
                'model' => $this->model
            ]);

            // Hacemos la petición a la API de OpenAI
            $response = Http::timeout(60)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => "Bearer {$this->apiKey}"
                ])
                ->post("{$this->baseUrl}/chat/completions", $requestBody);

            if ($response->successful()) {
                $responseData = $response->json();
                
                return [
                    'success' => true,
                    'content' => $responseData['choices'][0]['message']['content'] ?? '',
                    'provider' => 'openai',
                    'model' => $this->model,
                    'raw_response' => $responseData
                ];
            }

            Log::error('OpenAI API error: ' . $response->body());
            return [
                'success' => false,
                'error' => 'Error al comunicarse con OpenAI: ' . $response->status() . ' - ' . $response->body(),
                'provider' => 'openai'
            ];
        } catch (Exception $e) {
            Log::error('OpenAI exception: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error en el servicio de OpenAI: ' . $e->getMessage(),
                'provider' => 'openai'
            ];
        }
    }

    /**
     * Analyze patient data to assess suicide risk
     * 
     * @param array $patientData Patient clinical data to analyze
     * @return array Analysis results including risk assessment
     */
    public function analyzeRisk(array $patientData)
    {
        $prompt = $this->buildRiskAnalysisPrompt($patientData);
        
        $messages = [
            [
                'role' => 'system', 
                'content' => 'Eres un profesional experto en salud mental especializado en la evaluación de riesgos de suicidio. Tu tarea es analizar la información del paciente proporcionada y evaluar el nivel de riesgo suicida basado en indicadores clínicos. Proporciona una evaluación estructurada con una puntuación numérica de 0 a 1 (donde 1 es riesgo máximo), factores de riesgo identificados, y señales de alerta. Responde ÚNICAMENTE en formato JSON válido.'
            ],
            ['role' => 'user', 'content' => $prompt]
        ];

        $response = $this->chat($messages);
        
        if (!$response['success']) {
            return $response;
        }

        try {
            // Intentar parsear el JSON de la respuesta
            $content = $response['content'];
            $jsonStartPos = strpos($content, '{');
            $jsonEndPos = strrpos($content, '}');
            
            if ($jsonStartPos !== false && $jsonEndPos !== false) {
                $jsonContent = substr($content, $jsonStartPos, $jsonEndPos - $jsonStartPos + 1);
                $analysisData = json_decode($jsonContent, true);
                
                if (json_last_error() === JSON_ERROR_NONE) {
                    return [
                        'success' => true,
                        'risk_score' => $analysisData['risk_score'] ?? 0,
                        'risk_level' => $this->determineRiskLevel($analysisData['risk_score'] ?? 0),
                        'risk_factors' => $analysisData['risk_factors'] ?? [],
                        'warning_signs' => $analysisData['warning_signs'] ?? [],
                        'recommendations' => $analysisData['recommendations'] ?? [],
                        'provider' => 'openai',
                        'model' => $this->model
                    ];
                }
            }
            
            // Si llegamos aquí, hubo un problema con el formato JSON
            Log::warning('OpenAI response not in expected JSON format', ['content' => $content]);
            return [
                'success' => false,
                'error' => 'La respuesta de OpenAI no está en el formato JSON esperado',
                'provider' => 'openai',
                'raw_content' => $content
            ];
        } catch (Exception $e) {
            Log::error('Error parsing OpenAI risk analysis: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al analizar la respuesta de OpenAI: ' . $e->getMessage(),
                'provider' => 'openai'
            ];
        }
    }

    /**
     * Generate an intervention guide based on patient data and risk assessment
     * 
     * @param array $patientData Patient data
     * @param array $riskAssessment Risk assessment data
     * @return array Intervention guide and recommendations
     */
    public function generateInterventionGuide(array $patientData, array $riskAssessment)
    {
        $prompt = $this->buildInterventionGuidePrompt($patientData, $riskAssessment);
        
        $messages = [
            [
                'role' => 'system', 
                'content' => 'Eres un experto en intervención en crisis suicidas. Basándote en la evaluación de riesgo proporcionada, genera una guía de intervención detallada para profesionales de salud mental. La guía debe ser estructurada, práctica y específica para el nivel de riesgo identificado. Responde ÚNICAMENTE en formato JSON válido.'
            ],
            ['role' => 'user', 'content' => $prompt]
        ];

        $response = $this->chat($messages);
        
        if (!$response['success']) {
            return $response;
        }

        try {
            // Intentar parsear el JSON de la respuesta
            $content = $response['content'];
            $jsonStartPos = strpos($content, '{');
            $jsonEndPos = strrpos($content, '}');
            
            if ($jsonStartPos !== false && $jsonEndPos !== false) {
                $jsonContent = substr($content, $jsonStartPos, $jsonEndPos - $jsonStartPos + 1);
                $guideData = json_decode($jsonContent, true);
                
                if (json_last_error() === JSON_ERROR_NONE) {
                    return [
                        'success' => true,
                        'title' => $guideData['title'] ?? 'Guía de Intervención',
                        'summary' => $guideData['summary'] ?? '',
                        'immediate_actions' => $guideData['immediate_actions'] ?? [],
                        'safety_plan' => $guideData['safety_plan'] ?? [],
                        'resources' => $guideData['resources'] ?? [],
                        'follow_up' => $guideData['follow_up'] ?? [],
                        'provider' => 'openai',
                        'model' => $this->model
                    ];
                }
            }
            
            // Si llegamos aquí, hubo un problema con el formato JSON
            Log::warning('OpenAI intervention guide not in expected JSON format', ['content' => $content]);
            return [
                'success' => false,
                'error' => 'La guía de intervención de OpenAI no está en el formato JSON esperado',
                'provider' => 'openai',
                'raw_content' => $content
            ];
        } catch (Exception $e) {
            Log::error('Error parsing OpenAI intervention guide: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al analizar la guía de intervención de OpenAI: ' . $e->getMessage(),
                'provider' => 'openai'
            ];
        }
    }

    /**
     * Get information about the OpenAI provider
     * 
     * @return array Provider information
     */
    public function getProviderInfo()
    {
        return [
            'name' => 'OpenAI',
            'model' => $this->model,
            'status' => !empty($this->apiKey) ? 'configurado' : 'no configurado',
            'features' => [
                'chat' => true,
                'risk_analysis' => true,
                'intervention_guide' => true
            ]
        ];
    }

    /**
     * Build a prompt for risk analysis based on patient data
     * 
     * @param array $patientData Patient data
     * @return string Formatted prompt
     */
    protected function buildRiskAnalysisPrompt(array $patientData)
    {
        $conversationText = $patientData['conversation_text'] ?? 'No hay texto de conversación disponible';
        $patientInfo = $patientData['patient_info'] ?? [];
        $clinicalHistory = $patientData['clinical_history'] ?? [];
        
        // Construir un prompt estructurado
        $prompt = "EVALUACIÓN DE RIESGO SUICIDA\n\n";
        
        // Información del paciente
        $prompt .= "INFORMACIÓN DEL PACIENTE:\n";
        $prompt .= "Nombre: " . ($patientInfo['name'] ?? 'No disponible') . "\n";
        $prompt .= "Edad: " . ($patientInfo['age'] ?? 'No disponible') . "\n";
        $prompt .= "Género: " . ($patientInfo['gender'] ?? 'No disponible') . "\n\n";
        
        // Historia clínica si está disponible
        if (!empty($clinicalHistory)) {
            $prompt .= "HISTORIA CLÍNICA:\n";
            foreach ($clinicalHistory as $key => $value) {
                $prompt .= "- $key: $value\n";
            }
            $prompt .= "\n";
        }
        
        // Texto de la conversación
        $prompt .= "TEXTO DE LA CONVERSACIÓN:\n$conversationText\n\n";
        
        // Instrucciones para el análisis
        $prompt .= "INSTRUCCIONES:\n";
        $prompt .= "1. Analiza el texto de la conversación e identifica indicadores de riesgo suicida.\n";
        $prompt .= "2. Evalúa el nivel de riesgo en una escala de 0 a 1, donde 1 representa el máximo riesgo.\n";
        $prompt .= "3. Identifica factores de riesgo específicos y señales de advertencia.\n";
        $prompt .= "4. Proporciona recomendaciones de intervención apropiadas para el nivel de riesgo.\n\n";
        
        $prompt .= "Responde ÚNICAMENTE en formato JSON con la siguiente estructura:\n";
        $prompt .= "{\n";
        $prompt .= '  "risk_score": 0.X, // Valor numérico entre 0 y 1' . "\n";
        $prompt .= '  "risk_level": "bajo|medio|alto|crítico", // Nivel de riesgo categorizado' . "\n";
        $prompt .= '  "risk_factors": ["factor1", "factor2", ...], // Lista de factores de riesgo identificados' . "\n";
        $prompt .= '  "warning_signs": ["señal1", "señal2", ...], // Lista de señales de advertencia' . "\n";
        $prompt .= '  "recommendations": ["recomendación1", "recomendación2", ...] // Recomendaciones de intervención' . "\n";
        $prompt .= "}\n";
        
        return $prompt;
    }

    /**
     * Build a prompt for intervention guide based on patient data and risk assessment
     * 
     * @param array $patientData Patient data
     * @param array $riskAssessment Risk assessment data
     * @return string Formatted prompt
     */
    protected function buildInterventionGuidePrompt(array $patientData, array $riskAssessment)
    {
        $patientInfo = $patientData['patient_info'] ?? [];
        $riskLevel = $riskAssessment['risk_level'] ?? 'no especificado';
        $riskScore = $riskAssessment['risk_score'] ?? 0;
        $riskFactors = $riskAssessment['risk_factors'] ?? [];
        $warningSigns = $riskAssessment['warning_signs'] ?? [];
        
        // Construir un prompt estructurado
        $prompt = "GUÍA DE INTERVENCIÓN PARA PACIENTE CON RIESGO SUICIDA\n\n";
        
        // Información del paciente
        $prompt .= "INFORMACIÓN DEL PACIENTE:\n";
        $prompt .= "Nombre: " . ($patientInfo['name'] ?? 'No disponible') . "\n";
        $prompt .= "Edad: " . ($patientInfo['age'] ?? 'No disponible') . "\n";
        $prompt .= "Género: " . ($patientInfo['gender'] ?? 'No disponible') . "\n\n";
        
        // Información de la evaluación de riesgo
        $prompt .= "EVALUACIÓN DE RIESGO:\n";
        $prompt .= "Nivel de riesgo: $riskLevel\n";
        $prompt .= "Puntuación de riesgo: $riskScore\n\n";
        
        // Factores de riesgo
        $prompt .= "FACTORES DE RIESGO IDENTIFICADOS:\n";
        if (!empty($riskFactors)) {
            foreach ($riskFactors as $factor) {
                $prompt .= "- $factor\n";
            }
        } else {
            $prompt .= "- No se identificaron factores de riesgo específicos\n";
        }
        $prompt .= "\n";
        
        // Señales de advertencia
        $prompt .= "SEÑALES DE ADVERTENCIA:\n";
        if (!empty($warningSigns)) {
            foreach ($warningSigns as $sign) {
                $prompt .= "- $sign\n";
            }
        } else {
            $prompt .= "- No se identificaron señales de advertencia específicas\n";
        }
        $prompt .= "\n";
        
        // Instrucciones para la guía
        $prompt .= "INSTRUCCIONES:\n";
        $prompt .= "Genera una guía de intervención detallada para un profesional de salud mental que atiende a este paciente con riesgo suicida. La guía debe ser específica para el nivel de riesgo identificado y proporcionar recomendaciones prácticas.\n\n";
        
        $prompt .= "Responde ÚNICAMENTE en formato JSON con la siguiente estructura:\n";
        $prompt .= "{\n";
        $prompt .= '  "title": "Título de la guía de intervención",' . "\n";
        $prompt .= '  "summary": "Resumen del caso y nivel de riesgo",' . "\n";
        $prompt .= '  "immediate_actions": ["acción1", "acción2", ...], // Acciones inmediatas recomendadas' . "\n";
        $prompt .= '  "safety_plan": ["paso1", "paso2", ...], // Elementos del plan de seguridad' . "\n";
        $prompt .= '  "resources": ["recurso1", "recurso2", ...], // Recursos para el paciente y familiares' . "\n";
        $prompt .= '  "follow_up": ["recomendación1", "recomendación2", ...] // Plan de seguimiento' . "\n";
        $prompt .= "}\n";
        
        return $prompt;
    }

    /**
     * Determine risk level category based on numerical score
     * 
     * @param float $riskScore Risk score (0-1)
     * @return string Risk level category
     */
    protected function determineRiskLevel($riskScore)
    {
        if ($riskScore >= 0.8) {
            return 'crítico';
        } elseif ($riskScore >= 0.6) {
            return 'alto';
        } elseif ($riskScore >= 0.3) {
            return 'medio';
        } else {
            return 'bajo';
        }
    }
}
