<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class OllamaProvider implements AIProviderInterface
{
    protected $baseUrl;
    protected $model;
    protected $apiKey;

    /**
     * Create a new Ollama provider instance.
     * 
     * @param string|null $baseUrl Base URL del servicio Ollama (ej: http://localhost:11434)
     * @param string|null $model Modelo a utilizar (ej: llama3)
     * @param string|null $apiKey API Key si es necesaria
     */
    public function __construct($baseUrl = null, $model = null, $apiKey = null)
    {
        $this->baseUrl = $baseUrl ?? config('ai.providers.ollama.base_url', 'http://localhost:11434');
        $this->model = $model ?? config('ai.providers.ollama.model', 'llama3');
        $this->apiKey = $apiKey ?? config('ai.providers.ollama.api_key', '');
    }

    /**
     * Send a chat message to Ollama and get a response
     * 
     * @param array $messages Array of messages in the format [{role, content}]
     * @return array Response from Ollama
     */
    public function chat(array $messages)
    {
        try {
            // Ajustamos el formato para la API de Ollama
            $requestBody = [
                'model' => $this->model,
                'messages' => $messages,
                'stream' => false,
                'options' => [
                    'temperature' => 0.7,
                    'top_p' => 0.9
                ]
            ];

            // Para debugging
            Log::info('Enviando solicitud a Ollama', ['url' => "{$this->baseUrl}/chat/completions", 'body' => $requestBody]);

            // Hacemos la petición a la API de Ollama
            $response = Http::timeout(60)->withHeaders([
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/chat/completions", $requestBody);

            // Para debugging
            Log::info('Respuesta de Ollama recibida', ['status' => $response->status(), 'body' => $response->body()]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                // Ajustamos según el formato real de la respuesta de Ollama
                return [
                    'success' => true,
                    'content' => $responseData['choices'][0]['message']['content'] ?? '',
                    'provider' => 'ollama',
                    'model' => $this->model,
                    'raw_response' => $responseData
                ];
            }

            Log::error('Ollama API error: ' . $response->body());
            return [
                'success' => false,
                'error' => 'Error al comunicarse con Ollama: ' . $response->status(),
                'provider' => 'ollama'
            ];
        } catch (Exception $e) {
            Log::error('Ollama exception: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error en el servicio de Ollama: ' . $e->getMessage(),
                'provider' => 'ollama'
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
            ['role' => 'system', 'content' => 'Eres un profesional experto en salud mental especializado en la evaluación de riesgos de suicidio. Tu tarea es analizar la información del paciente proporcionada y evaluar el nivel de riesgo suicida basado en indicadores clínicos. Proporciona una evaluación estructurada con una puntuación numérica de 0 a 1 (donde 1 es riesgo máximo), factores de riesgo identificados, y señales de alerta. Responde ÚNICAMENTE en formato JSON válido.'],
            ['role' => 'user', 'content' => $prompt]
        ];

        $response = $this->chat($messages);
        
        if (!$response['success']) {
            return [
                'success' => false,
                'error' => $response['error'] ?? 'Error en el análisis de riesgo',
                'provider' => 'ollama'
            ];
        }

        // Intentar extraer el JSON de la respuesta
        try {
            $content = $response['content'];
            // Intentar limpiar el contenido para extraer solo el JSON
            if (strpos($content, '```json') !== false) {
                preg_match('/```json\s*([\s\S]*?)\s*```/', $content, $matches);
                $content = $matches[1] ?? $content;
            }
            
            $analysisResult = json_decode($content, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Error al decodificar JSON de respuesta de riesgo: ' . json_last_error_msg());
                Log::error('Contenido: ' . $content);
                
                // Fallback: intentar extraer los datos clave
                return [
                    'success' => true,
                    'risk_score' => $this->extractRiskScore($content),
                    'risk_level' => $this->extractRiskLevel($content),
                    'risk_factors' => $this->extractRiskFactors($content),
                    'warning_signs' => $this->extractWarningSigns($content),
                    'raw_content' => $content,
                    'provider' => 'ollama'
                ];
            }
            
            return array_merge(
                ['success' => true, 'provider' => 'ollama'],
                $analysisResult
            );
        } catch (Exception $e) {
            Log::error('Error al procesar resultado de análisis: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al procesar el análisis de riesgo: ' . $e->getMessage(),
                'raw_content' => $response['content'] ?? null,
                'provider' => 'ollama'
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
            ['role' => 'system', 'content' => 'Eres un profesional experto en salud mental especializado en intervención en crisis y prevención del suicidio. Tu tarea es generar una guía de intervención paso a paso basada en los datos del paciente y la evaluación de riesgo proporcionada. Incluye técnicas específicas, recomendaciones, recursos a considerar y un plan de seguimiento. Responde en formato JSON válido.'],
            ['role' => 'user', 'content' => $prompt]
        ];

        $response = $this->chat($messages);
        
        if (!$response['success']) {
            return [
                'success' => false,
                'error' => $response['error'] ?? 'Error al generar guía de intervención',
                'provider' => 'ollama'
            ];
        }

        // Procesar la respuesta
        try {
            $content = $response['content'];
            // Limpiar para extraer JSON
            if (strpos($content, '```json') !== false) {
                preg_match('/```json\s*([\s\S]*?)\s*```/', $content, $matches);
                $content = $matches[1] ?? $content;
            }
            
            $guideResult = json_decode($content, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Error al decodificar JSON de guía: ' . json_last_error_msg());
                
                // Fallback: usar el contenido en bruto
                return [
                    'success' => true,
                    'guide' => $content,
                    'provider' => 'ollama'
                ];
            }
            
            return array_merge(
                ['success' => true, 'provider' => 'ollama'],
                $guideResult
            );
        } catch (Exception $e) {
            Log::error('Error al procesar guía de intervención: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al procesar la guía de intervención: ' . $e->getMessage(),
                'raw_content' => $response['content'] ?? null,
                'provider' => 'ollama'
            ];
        }
    }

    /**
     * Get information about the Ollama provider
     * 
     * @return array Provider information
     */
    public function getProviderInfo()
    {
        return [
            'name' => 'Ollama',
            'model' => $this->model,
            'base_url' => $this->baseUrl,
            'description' => 'Modelo de IA local ejecutándose con Ollama',
            'privacy_level' => 'Alto - Los datos permanecen en el servidor local',
        ];
    }

    /**
     * Build a prompt for risk analysis from patient data
     */
    protected function buildRiskAnalysisPrompt(array $patientData)
    {
        // Extraer y formatear datos clave del paciente
        $nombre = $patientData['Nombre_Usuario'] ?? 'No disponible';
        $problemasActuales = $patientData['ProblematicaActual'] ?? 'No disponible';
        $impresionDiagnostica = $patientData['ImprecionDiagnostica'] ?? 'No disponible';
        $observaciones = $patientData['Observaciones'] ?? 'No disponible';
        $antecedentesPersonales = $patientData['PersonalesPsicosociales'] ?? 'No disponible';
        $antecedentesFamiliares = $patientData['Familiares'] ?? 'No disponible';
        $antecedentesClinicosF = $patientData['AntecedentesClinicosFisicosMentales'] ?? 'No disponible';

        return <<<EOT
Por favor, analiza los siguientes datos clínicos de un paciente para evaluar el riesgo de suicidio.

DATOS DEL PACIENTE:
- Información básica: $nombre
- Problemática actual: $problemasActuales
- Impresión diagnóstica: $impresionDiagnostica
- Observaciones clínicas: $observaciones
- Antecedentes personales psicosociales: $antecedentesPersonales
- Antecedentes familiares: $antecedentesFamiliares
- Antecedentes clínicos, físicos y mentales: $antecedentesClinicosF

Basado en estos datos, realiza un análisis exhaustivo del riesgo de suicidio.

Proporciona los siguientes elementos en tu respuesta en formato JSON:
1. risk_score: Puntuación numérica de 0 a 1 que indique el nivel de riesgo (donde 1 es el riesgo máximo)
2. risk_level: Categoría de riesgo ("bajo", "moderado", "alto", "crítico")
3. risk_factors: Lista de factores de riesgo identificados
4. warning_signs: Lista de señales de alerta específicas
5. critical_indicators: Indicadores críticos que requieren atención inmediata
6. reasoning: Explicación del razonamiento detrás de la evaluación
EOT;
    }

    /**
     * Build a prompt for intervention guide from patient data and risk assessment
     */
    protected function buildInterventionGuidePrompt(array $patientData, array $riskAssessment)
    {
        // Datos del paciente
        $nombre = $patientData['Nombre_Usuario'] ?? 'No disponible';
        $problemasActuales = $patientData['ProblematicaActual'] ?? 'No disponible';
        $enfoque = $patientData['EnfoqueOrientadorProceso'] ?? 'No disponible';
        $tecnicasUsadas = $patientData['TecnicasUsadas'] ?? 'No disponible';
        
        // Datos de la evaluación de riesgo
        $nivelRiesgo = $riskAssessment['risk_level'] ?? 'No disponible';
        $puntuacionRiesgo = $riskAssessment['risk_score'] ?? 'No disponible';
        $factoresRiesgo = is_array($riskAssessment['risk_factors'] ?? null) 
            ? implode(", ", $riskAssessment['risk_factors']) 
            : ($riskAssessment['risk_factors'] ?? 'No disponible');
        $senalesAlerta = is_array($riskAssessment['warning_signs'] ?? null) 
            ? implode(", ", $riskAssessment['warning_signs']) 
            : ($riskAssessment['warning_signs'] ?? 'No disponible');

        return <<<EOT
Por favor, genera una guía de intervención detallada para un paciente con riesgo de suicidio. 

DATOS DEL PACIENTE:
- Información básica: $nombre
- Problemática actual: $problemasActuales
- Enfoque terapéutico previo: $enfoque
- Técnicas utilizadas previamente: $tecnicasUsadas

EVALUACIÓN DE RIESGO:
- Nivel de riesgo: $nivelRiesgo
- Puntuación de riesgo: $puntuacionRiesgo
- Factores de riesgo identificados: $factoresRiesgo
- Señales de alerta: $senalesAlerta

Basado en estos datos, genera una guía de intervención comprensiva que incluya:

1. steps: Pasos inmediatos que debe seguir el profesional
2. techniques: Técnicas específicas recomendadas (basadas en evidencia)
3. resources: Recursos a considerar (hospitalizaciones, medicamentos, derivaciones)
4. follow_up_plan: Plan de seguimiento recomendado
5. communication_strategies: Estrategias de comunicación efectivas con el paciente
6. safety_plan: Elementos clave para un plan de seguridad
7. supporting_evidence: Base de evidencia para las recomendaciones

Proporciona tu respuesta en formato JSON.
EOT;
    }

    /**
     * Extract risk score from non-JSON content
     */
    protected function extractRiskScore($content)
    {
        // Intenta encontrar un valor numérico entre 0 y 1
        preg_match('/riesgo.*?(\d+\.\d+)|\b(\d+\.\d+).*?riesgo/i', $content, $matches);
        return $matches[1] ?? $matches[2] ?? 0.5; // valor predeterminado si no se encuentra
    }

    /**
     * Extract risk level from non-JSON content
     */
    protected function extractRiskLevel($content)
    {
        if (preg_match('/riesgo.*?(alto|crítico|critico)/i', $content)) {
            return 'alto';
        } elseif (preg_match('/riesgo.*?(moderado|medio)/i', $content)) {
            return 'moderado';
        } elseif (preg_match('/riesgo.*?(bajo)/i', $content)) {
            return 'bajo';
        }
        return 'indeterminado';
    }

    /**
     * Extract risk factors from non-JSON content
     */
    protected function extractRiskFactors($content)
    {
        // Busca listas o enumeraciones
        if (preg_match('/factores de riesgo.*?:(.*?)(?:\n\n|\n[A-Z]|$)/is', $content, $matches)) {
            $factorsText = $matches[1];
            // Divide por viñetas o números
            preg_match_all('/[-*•].*?(?=[-*•]|$)|[0-9]+\..*?(?=[0-9]+\.|$)/s', $factorsText, $factors);
            return array_map('trim', $factors[0] ?? []);
        }
        return ['No se pudieron extraer factores de riesgo automáticamente'];
    }

    /**
     * Extract warning signs from non-JSON content
     */
    protected function extractWarningSigns($content)
    {
        if (preg_match('/señales de (alerta|advertencia).*?:(.*?)(?:\n\n|\n[A-Z]|$)/is', $content, $matches)) {
            $signsText = $matches[2];
            preg_match_all('/[-*•].*?(?=[-*•]|$)|[0-9]+\..*?(?=[0-9]+\.|$)/s', $signsText, $signs);
            return array_map('trim', $signs[0] ?? []);
        }
        return ['No se pudieron extraer señales de alerta automáticamente'];
    }
}
