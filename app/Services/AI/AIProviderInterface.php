<?php

namespace App\Services\AI;

interface AIProviderInterface
{
    /**
     * Send a chat message to the AI and get a response
     * 
     * @param array $messages Array of messages in the format [{role, content}]
     * @return array Response from the AI
     */
    public function chat(array $messages);
    
    /**
     * Analyze patient data to assess suicide risk
     * 
     * @param array $patientData Patient clinical data to analyze
     * @return array Analysis results including risk assessment
     */
    public function analyzeRisk(array $patientData);
    
    /**
     * Generate an intervention guide based on patient data and risk assessment
     * 
     * @param array $patientData Patient data
     * @param array $riskAssessment Risk assessment data
     * @return array Intervention guide and recommendations
     */
    public function generateInterventionGuide(array $patientData, array $riskAssessment);
    
    /**
     * Get information about the current AI provider
     * 
     * @return array Provider information
     */
    public function getProviderInfo();
}
