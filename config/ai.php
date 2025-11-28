<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Provider Configuration
    |--------------------------------------------------------------------------
    |
    | The AI provider to use for all AI operations. Supported: 'openai', 'local'
    |
    */
    'provider' => env('AI_PROVIDER', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | OpenAI Configuration
    |--------------------------------------------------------------------------
    */
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'organization' => env('OPENAI_ORGANIZATION'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        'timeout' => env('OPENAI_TIMEOUT', 120),
        'max_retries' => env('OPENAI_MAX_RETRIES', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Model Configuration
    |--------------------------------------------------------------------------
    */
    'models' => [
        // Text generation model for chatbot
        'text' => env('AI_MODEL', 'gpt-4-turbo-preview'),
        
        // Vision model for image analysis
        'vision' => env('AI_VISION_MODEL', 'gpt-4-vision-preview'),
        
        // Embedding model for RAG
        'embedding' => env('AI_EMBEDDING_MODEL', 'text-embedding-3-large'),
        
        // Model parameters
        'temperature' => env('AI_TEMPERATURE', 0.7),
        'max_tokens' => env('AI_MAX_TOKENS', 4000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Local Provider Configuration (PyTorch/Custom)
    |--------------------------------------------------------------------------
    */
    'local' => [
        'anomaly_endpoint' => env('LOCAL_ANOMALY_ENDPOINT', 'http://localhost:5000/detect'),
        'classification_endpoint' => env('LOCAL_CLASSIFICATION_ENDPOINT', 'http://localhost:5000/classify'),
        'timeout' => env('LOCAL_TIMEOUT', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Plant Anomaly Detection Settings
    |--------------------------------------------------------------------------
    */
    'anomaly_detection' => [
        'enabled' => env('AI_ANOMALY_DETECTION_ENABLED', true),
        'confidence_threshold' => env('AI_ANOMALY_CONFIDENCE_THRESHOLD', 0.7),
        'auto_flag_batches' => env('AI_AUTO_FLAG_BATCHES', true),
        
        // Supported issue types
        'issue_types' => [
            'pest_infestation',
            'nutrient_deficiency',
            'nutrient_burn',
            'disease',
            'mold',
            'hermaphroditism',
            'light_stress',
            'heat_stress',
            'root_issues',
            'ph_imbalance',
            'overwatering',
            'underwatering',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Plant Classification Settings
    |--------------------------------------------------------------------------
    */
    'classification' => [
        'enabled' => env('AI_CLASSIFICATION_ENABLED', true),
        'confidence_threshold' => env('AI_CLASSIFICATION_CONFIDENCE_THRESHOLD', 0.6),
        
        // Classification categories
        'categories' => [
            'growth_stage' => ['seedling', 'vegetative', 'pre-flower', 'flowering', 'harvest-ready'],
            'health_status' => ['healthy', 'stressed', 'diseased', 'dying'],
            'leaf_issues' => ['healthy', 'yellowing', 'browning', 'spotting', 'curling', 'wilting'],
            'strain_type' => ['indica', 'sativa', 'hybrid'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | RAG (Retrieval Augmented Generation) Settings
    |--------------------------------------------------------------------------
    */
    'rag' => [
        'enabled' => env('AI_RAG_ENABLED', true),
        'chunk_size' => env('AI_RAG_CHUNK_SIZE', 1000),
        'chunk_overlap' => env('AI_RAG_CHUNK_OVERLAP', 200),
        'top_k_results' => env('AI_RAG_TOP_K', 10),
        'similarity_threshold' => env('AI_RAG_SIMILARITY_THRESHOLD', 0.7),
        
        // Data sources for RAG
        'data_sources' => [
            'batches' => true,
            'strains' => true,
            'batch_logs' => true,
            'environmental_readings' => true,
            'harvests' => true,
            'rooms' => true,
            'facilities' => true,
            'growth_cycles' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cultivation Chatbot Settings
    |--------------------------------------------------------------------------
    */
    'chatbot' => [
        'enabled' => env('AI_CHATBOT_ENABLED', true),
        'system_prompt' => 'You are an expert cannabis cultivation assistant with deep knowledge of growing techniques, plant health, environmental controls, and harvest optimization. You provide accurate, actionable advice based on the cultivation data provided. Always reference specific batch codes, dates, and measurements when available.',
        'max_conversation_history' => env('AI_CHATBOT_MAX_HISTORY', 10),
        'streaming' => env('AI_CHATBOT_STREAMING', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Processing Settings
    |--------------------------------------------------------------------------
    */
    'images' => [
        'max_size_mb' => env('AI_MAX_IMAGE_SIZE_MB', 10),
        'allowed_formats' => ['jpg', 'jpeg', 'png', 'webp'],
        'storage_disk' => env('AI_IMAGE_STORAGE_DISK', 'public'),
        'storage_path' => env('AI_IMAGE_STORAGE_PATH', 'ai/plant-images'),
        'auto_cleanup_days' => env('AI_IMAGE_CLEANUP_DAYS', 90),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging and Monitoring
    |--------------------------------------------------------------------------
    */
    'logging' => [
        'enabled' => env('AI_LOGGING_ENABLED', true),
        'log_prompts' => env('AI_LOG_PROMPTS', true),
        'log_responses' => env('AI_LOG_RESPONSES', true),
        'log_errors' => env('AI_LOG_ERRORS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
    'rate_limiting' => [
        'enabled' => env('AI_RATE_LIMITING_ENABLED', true),
        'max_requests_per_minute' => env('AI_MAX_REQUESTS_PER_MINUTE', 30),
        'max_requests_per_hour' => env('AI_MAX_REQUESTS_PER_HOUR', 500),
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => env('AI_CACHE_ENABLED', true),
        'ttl' => env('AI_CACHE_TTL', 3600), // 1 hour
        'driver' => env('AI_CACHE_DRIVER', 'redis'),
    ],
];
