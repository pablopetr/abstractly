<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Summarization Provider
    |--------------------------------------------------------------------------
    |
    | Which AI backend to use for research-digest summaries.
    | Supported: "gemini", "openai", "ollama"
    |
    */

    'provider' => env('DIGEST_AI_PROVIDER', 'gemini'),

    /*
    |--------------------------------------------------------------------------
    | Summary Cache TTL (seconds)
    |--------------------------------------------------------------------------
    |
    | How long to cache AI summaries per paper URL. Papers with cached
    | summaries skip the AI call on subsequent generations. Set to 0 to
    | disable summary caching entirely.
    |
    */

    'summary_cache_ttl' => (int) env('AI_SUMMARY_CACHE_TTL', 86400),

    /*
    |--------------------------------------------------------------------------
    | Batch Delay (milliseconds)
    |--------------------------------------------------------------------------
    |
    | Delay between consecutive AI summarization batches to avoid hitting
    | provider rate limits. Applied between batches, not before the first.
    |
    */

    'batch_delay_ms' => (int) env('AI_BATCH_DELAY_MS', 200),

    /*
    |--------------------------------------------------------------------------
    | Gemini (default)
    |--------------------------------------------------------------------------
    */

    'gemini' => [
        'api_key' => env('GOOGLE_API_KEY'),
        'model'   => env('DIGEST_AI_MODEL', 'gemini-2.0-flash'),
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenAI (optional)
    |--------------------------------------------------------------------------
    */

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model'   => env('DIGEST_AI_MODEL_OPENAI', 'gpt-4o-mini'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Ollama (optional, local)
    |--------------------------------------------------------------------------
    */

    'ollama' => [
        'host'  => env('OLLAMA_HOST', 'http://127.0.0.1:11434'),
        'model' => env('DIGEST_AI_MODEL_OLLAMA', 'llama3.1'),
    ],

];
