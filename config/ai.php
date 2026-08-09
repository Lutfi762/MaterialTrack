<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Provider
    |--------------------------------------------------------------------------
    |
    | The AI provider to use for MaterialTrack AI Assistant.
    | Currently supported: 'gemini'
    |
    */

    'provider' => env('AI_PROVIDER', 'gemini'),

    /*
    |--------------------------------------------------------------------------
    | Gemini Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Google Gemini AI provider.
    |
    */

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-1.5-flash'),
        'api_url' => 'https://generativelanguage.googleapis.com/v1beta/models',
        'timeout' => 30,
        'max_tokens' => 2048,
        'temperature' => 0.7,
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Assistant Settings
    |--------------------------------------------------------------------------
    */

    'assistant' => [
        'max_query_length' => 2000,
        'max_conversation_history' => 10,
        'query_result_limit' => 20,
        'rate_limit' => '20,1', // 20 requests per 1 minute
    ],

];
