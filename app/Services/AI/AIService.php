<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;
use Exception;

class AIService
{
    /**
     * Get the appropriate AI provider based on configuration or specific request
     *
     * @param string|null $providerName Optional specific provider to use
     * @return AIProviderInterface
     */
    public function getProvider(?string $providerName = null): AIProviderInterface
    {
        // Use the requested provider or fall back to the default from config
        $provider = $providerName ?? config('ai.default_provider', 'ollama');
        
        try {
            switch ($provider) {
                case 'ollama':
                    return new OllamaProvider();
                case 'openai':
                    return new OpenAIProvider();
                case 'deepseek':
                    return new DeepSeekProvider();
                case 'grok':
                    return new GrokProvider();
                default:
                    Log::warning("Proveedor de IA desconocido: $provider, usando Ollama como fallback");
                    return new OllamaProvider();
            }
        } catch (Exception $e) {
            Log::error("Error al inicializar proveedor de IA $provider: " . $e->getMessage());
            // Si falla, intentamos con Ollama como fallback
            return new OllamaProvider();
        }
    }
    
    /**
     * Get all available AI providers with their information
     *
     * @return array
     */
    public function getAvailableProviders(): array
    {
        $providers = [];
        
        try {
            // Ollama siempre está disponible ya que es local
            $providers['ollama'] = (new OllamaProvider())->getProviderInfo();
            
            // Verificar otros proveedores solo si tienen una API key configurada
            if (!empty(config('ai.providers.openai.api_key'))) {
                $providers['openai'] = (new OpenAIProvider())->getProviderInfo();
            }
            
            if (!empty(config('ai.providers.deepseek.api_key'))) {
                $providers['deepseek'] = (new DeepSeekProvider())->getProviderInfo();
            }
            
            if (!empty(config('ai.providers.grok.api_key'))) {
                $providers['grok'] = (new GrokProvider())->getProviderInfo();
            }
        } catch (Exception $e) {
            Log::error("Error al obtener proveedores de IA disponibles: " . $e->getMessage());
        }
        
        return $providers;
    }
    
    /**
     * Analyze patient data with the specified or default AI provider
     *
     * @param array $patientData Patient data to analyze
     * @param string|null $providerName Optional specific provider to use
     * @return array Analysis results
     */
    public function analyzePatientRisk(array $patientData, ?string $providerName = null): array
    {
        try {
            $provider = $this->getProvider($providerName);
            return $provider->analyzeRisk($patientData);
        } catch (Exception $e) {
            Log::error("Error en análisis de riesgo: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al realizar análisis de riesgo: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Generate intervention guide with the specified or default AI provider
     *
     * @param array $patientData Patient data
     * @param array $riskAssessment Risk assessment data
     * @param string|null $providerName Optional specific provider to use
     * @return array Intervention guide
     */
    public function generateInterventionGuide(array $patientData, array $riskAssessment, ?string $providerName = null): array
    {
        try {
            $provider = $this->getProvider($providerName);
            return $provider->generateInterventionGuide($patientData, $riskAssessment);
        } catch (Exception $e) {
            Log::error("Error al generar guía de intervención: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al generar guía de intervención: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Process a chat message with the specified or default AI provider
     *
     * @param array $messages Chat messages
     * @param string|null $providerName Optional specific provider to use
     * @return array Chat response
     */
    public function chat(array $messages, ?string $providerName = null): array
    {
        try {
            $provider = $this->getProvider($providerName);
            return $provider->chat($messages);
        } catch (Exception $e) {
            Log::error("Error en chat de IA: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error en el servicio de chat: ' . $e->getMessage()
            ];
        }
    }
}
