<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    | esta opcion controla el proveedor de IA por defecto que se utilizara en la aplicacion
    | puedes cambiarlo a openai, deepseek, grok, etc
    |
    */
    'default_provider' => 'ollama',

    /*
    |--------------------------------------------------------------------------
    | AI Providers Configuration
    |--------------------------------------------------------------------------
    | Aqui puede configurar los proveedores de IA que se utilizaran en la aplicacion
    | 
    | 
    | 
    |
    */
    'providers' => [
        'ollama' => [
            'base_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434/v1'),
            'model' => env('OLLAMA_MODEL', 'qwen3:8b'),
            'api_key' => env('OLLAMA_API_KEY', ''),
        ],
        
        'openai' => [
            'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
            'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'api_key' => env('OPENAI_API_KEY', ''),
        ],
        
        'deepseek' => [
            'base_url' => env('DEEPSEEK_BASE_URL', 'https://api.deepseek.com/v1'),
            'model' => env('DEEPSEEK_MODEL', 'deepseek-chat'),
            'api_key' => env('DEEPSEEK_API_KEY', ''),
        ],
        
        'grok' => [
            'base_url' => env('GROK_BASE_URL', 'https://api.grok.x/v1'),
            'model' => env('GROK_MODEL', 'grok-1'),
            'api_key' => env('GROK_API_KEY', ''),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Risk Assessment Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for risk assessment algorithms and thresholds.
    |
    */
    'risk_assessment' => [
        'high_risk_threshold' => 0.75,
        'medium_risk_threshold' => 0.5,
        'low_risk_threshold' => 0.25,
        'analyze_historical_data' => true,
        'max_context_length' => 4000,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Privacy Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for privacy protections when using external AI providers.
    |
    */
    'privacy' => [
        'anonymize_names' => true,
        'anonymize_identifiers' => true,
        'external_provider_warning' => true,
        'log_all_requests' => true,
    ],
];
