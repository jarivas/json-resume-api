<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default AI Provider Names
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the AI providers below should be the
    | default for AI operations when no explicit provider is provided
    | for the operation. This should be any provider defined below.
    |
    */

    'default' => env('AI_DEFAULT_PROVIDER', 'openai'),
    'default_for_images' => env('AI_DEFAULT_PROVIDER', 'gemini'),
    'default_for_audio' => env('AI_DEFAULT_PROVIDER', 'openai'),
    'default_for_transcription' => env('AI_DEFAULT_PROVIDER', 'openai'),
    'default_for_embeddings' => env('AI_DEFAULT_PROVIDER', 'openai'),
    'default_for_reranking' => env('AI_DEFAULT_PROVIDER', 'cohere'),

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | Below you may configure caching strategies for AI related operations
    | such as embedding generation. You are free to adjust these values
    | based on your application's available caching stores and needs.
    |
    */

    'caching' => [
        'embeddings' => [
            'cache' => false,
            'store' => env('CACHE_STORE', 'database'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Providers
    |--------------------------------------------------------------------------
    |
    | Below are each of your AI providers defined for this application. Each
    | represents an AI provider and API key combination which can be used
    | to perform tasks like text, image, and audio creation via agents.
    |
    */

    'providers' => [
        'anthropic' => [
            'driver' => 'anthropic',
            'key' => env('ANTHROPIC_API_KEY'),
            'alternative_deployment' => [
                'anthropic-alt-1',
                'anthropic-alt-2',
            ],
        ],

        'azure' => [
            'driver' => 'azure',
            'key' => env('AZURE_OPENAI_API_KEY'),
            'url' => env('AZURE_OPENAI_URL'),
            'api_version' => env('AZURE_OPENAI_API_VERSION', '2024-10-21'),
            'deployment' => env('AZURE_OPENAI_DEPLOYMENT', 'gpt-4o'),
            'embedding_deployment' => env('AZURE_OPENAI_EMBEDDING_DEPLOYMENT', 'database'),
            'alternative_deployment' => explode(',', env('AZURE_OPENAI_ALTERNATIVE_DEPLOYMENTS', 'azure-alt-1,azure-alt-2')),
        ],

        'cohere' => [
            'driver' => 'cohere',
            'key' => env('COHERE_API_KEY'),
            'deployment' => env('COHERE_DEPLOYMENT', 'cohere-4o'),
            'embedding_deployment' => env('COHERE_EMBEDDING_DEPLOYMENT', 'database'),
            'alternative_deployment' => explode(',', env('COHERE_ALTERNATIVE_DEPLOYMENTS', 'cohere-alt-1,cohere-alt-2')),
        ],

        'deepseek' => [
            'driver' => 'deepseek',
            'key' => env('DEEPSEEK_API_KEY'),
            'deployment' => env('DEEPSEEK_DEPLOYMENT', 'deepseek-4o'),
            'embedding_deployment' => env('DEEPSEEK_EMBEDDING_DEPLOYMENT', 'database'),
            'alternative_deployment' => explode(',', env('DEEPSEEK_ALTERNATIVE_DEPLOYMENTS', 'deepseek-alt-1,deepseek-alt-2')),
        ],

        'eleven' => [
            'driver' => 'eleven',
            'key' => env('ELEVENLABS_API_KEY'),
            'deployment' => env('ELEVENLABS_DEPLOYMENT', 'eleven-4o'),
            'embedding_deployment' => env('ELEVENLABS_EMBEDDING_DEPLOYMENT', 'database'),
            'alternative_deployment' => explode(',', env('ELEVENLABS_ALTERNATIVE_DEPLOYMENTS', 'eleven-alt-1,eleven-alt-2')),
        ],

        'gemini' => [
            'driver' => 'gemini',
            'key' => env('GEMINI_API_KEY'),
            'deployment' => env('GEMINI_DEPLOYMENT', 'gemini-2.5-flash'),
            'embedding_deployment' => env('GEMINI_EMBEDDING_DEPLOYMENT', 'database'),
            'alternative_deployment' => explode(',', env('GEMINI_ALTERNATIVE_DEPLOYMENTS', '')),
            'fallback_providers' => explode(',', env('GEMINI_FALLBACK_PROVIDERS', '')),
        ],

        'groq' => [
            'driver' => 'groq',
            'key' => env('GROQ_API_KEY'),
            'deployment' => env('GROQ_DEPLOYMENT', 'groq-4o'),
            'embedding_deployment' => env('GROQ_EMBEDDING_DEPLOYMENT', 'database'),
            'alternative_deployment' => explode(',', env('GROQ_EMBEDDING_ALTERNATIVE_DEPLOYMENTS', 'groq-alt-1,groq-alt-2')),
        ],

        'jina' => [
            'driver' => 'jina',
            'key' => env('JINA_API_KEY'),
            'deployment' => env('JINA_DEPLOYMENT', 'jina-4o'),
            'embedding_deployment' => env('JINA_EMBEDDING_DEPLOYMENT', 'database'),
            'alternative_deployment' => explode(',', env('JINA_EMBEDDING_ALTERNATIVE_DEPLOYMENTS', 'jina-alt-1,jina-alt-2')),
        ],

        'mistral' => [
            'driver' => 'mistral',
            'key' => env('MISTRAL_API_KEY'),
            'deployment' => env('MISTRAL_DEPLOYMENT', 'mistral-4o'),
            'embedding_deployment' => env('MISTRAL_EMBEDDING_DEPLOYMENT', 'database'),
            'alternative_deployment' => explode(',', env('MISTRAL_ALTERNATIVE_DEPLOYMENTS', 'mistral-alt-1,mistral-alt-2')),
        ],

        'ollama' => [
            'driver' => 'ollama',
            'key' => env('OLLAMA_API_KEY', ''),
            'url' => env('OLLAMA_URL', 'http://localhost:11434'),
            'deployment' => env('OLLAMA_DEPLOYMENT', 'llama3.1'),
            'embedding_deployment' => env('OLLAMA_EMBEDDING_DEPLOYMENT', 'database'),
            'timeout' => env('OLLAMA_TIMEOUT', 120),
        ],

        'openai' => [
            'driver' => 'openai',
            'key' => env('OPENAI_API_KEY'),
            'deployment' => env('OPENAI_DEPLOYMENT', 'openai-4o'),
            'embedding_deployment' => env('OPENAI_EMBEDDING_DEPLOYMENT', 'database'),
            'alternative_deployment' => explode(',', env('OPENAI_ALTERNATIVE_DEPLOYMENTS', 'openai-alt-1,openai-alt-2')),
        ],

        'openrouter' => [
            'driver' => 'openrouter',
            'key' => env('OPENROUTER_API_KEY'),
            'deployment' => env('OPENROUTER_DEPLOYMENT', 'openrouter-4o'),
            'embedding_deployment' => env('OPENROUTER_EMBEDDING_DEPLOYMENT', 'database'),
            'alternative_deployment' => explode(',', env('OPENROUTER_ALTERNATIVE_DEPLOYMENTS', 'openrouter-alt-1,openrouter-alt-2')),
        ],

        'voyageai' => [
            'driver' => 'voyageai',
            'key' => env('VOYAGEAI_API_KEY'),
            'deployment' => env('VOYAGEAI_DEPLOYMENT', 'voyageai-4o'),
            'embedding_deployment' => env('VOYAGEAI_EMBEDDING_DEPLOYMENT', 'database'),
            'alternative_deployment' => explode(',', env('VOYAGEAI_ALTERNATIVE_DEPLOYMENTS', 'voyageai-alt-1,voyageai-alt-2')),
        ],

        'xai' => [
            'driver' => 'xai',
            'key' => env('XAI_API_KEY'),
            'deployment' => env('XAI_DEPLOYMENT', 'xai-4o'),
            'embedding_deployment' => env('XAI_EMBEDDING_DEPLOYMENT', 'database'),
            'alternative_deployment' => explode(',', env('XAI_ALTERNATIVE_DEPLOYMENTS', 'xai-alt-1,xai-alt-2')),
        ],
    ],

];
