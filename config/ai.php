<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default AI service provider that will be used by
    | the application. By default, we'll use Ollama as the provider running locally.
    | You are free to modify this value to use a different provider.
    |
    */
    'default_provider' => 'ollama',

    /*
    |--------------------------------------------------------------------------
    | AI Providers Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the AI providers that will be available in the application.
    | Ollama is configured by default to run locally. Additional providers can be
    | added and configured as needed.
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
