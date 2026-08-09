<?php

namespace App\Services\AI;

use App\Contracts\AIProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiProvider implements AIProviderInterface
{
    protected string $apiKey;
    protected string $model;
    protected string $apiUrl;
    protected int $timeout;
    protected int $maxTokens;
    protected float $temperature;

    public function __construct()
    {
        $this->apiKey = config('ai.gemini.api_key', '');
        $this->model = config('ai.gemini.model', 'gemini-1.5-flash');
        $this->apiUrl = config('ai.gemini.api_url');
        $this->timeout = config('ai.gemini.timeout', 30);
        $this->maxTokens = config('ai.gemini.max_tokens', 2048);
        $this->temperature = config('ai.gemini.temperature', 0.7);
    }

    public function chat(string $systemPrompt, array $messages, array $tools = []): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Gemini API key is not configured.');
        }

        // Build Gemini request format
        $contents = $this->buildContents($systemPrompt, $messages);

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => $this->temperature,
                'maxOutputTokens' => $this->maxTokens,
            ],
        ];

        // Add tools if provided
        if (!empty($tools)) {
            $payload['tools'] = [
                ['function_declarations' => $tools]
            ];
        }

        try {
            $url = "{$this->apiUrl}/{$this->model}:generateContent?key={$this->apiKey}";

            $response = Http::timeout($this->timeout)
                ->post($url, $payload);

            if (!$response->successful()) {
                Log::error('Gemini API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \Exception('Gemini API request failed.');
            }

            $data = $response->json();

            return $this->parseResponse($data);

        } catch (\Exception $e) {
            Log::error('Gemini provider exception', [
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    public function getName(): string
    {
        return 'gemini';
    }

    protected function buildContents(string $systemPrompt, array $messages): array
    {
        $contents = [];

        // Add system prompt as first user message
        if (!empty($systemPrompt)) {
            $contents[] = [
                'role' => 'user',
                'parts' => [['text' => $systemPrompt]],
            ];
            $contents[] = [
                'role' => 'model',
                'parts' => [['text' => 'Understood. I will follow these instructions.']],
            ];
        }

        // Add conversation history
        foreach ($messages as $message) {
            $role = $message['role'] === 'user' ? 'user' : 'model';
            $contents[] = [
                'role' => $role,
                'parts' => [['text' => $message['content']]],
            ];
        }

        return $contents;
    }

    protected function parseResponse(array $data): array
    {
        if (empty($data['candidates'][0]['content']['parts'][0]['text'])) {
            throw new \Exception('Invalid response from Gemini API.');
        }

        $text = $data['candidates'][0]['content']['parts'][0]['text'];

        // Check for function call
        $functionCall = $data['candidates'][0]['content']['parts'][0]['functionCall'] ?? null;

        return [
            'text' => $text,
            'function_call' => $functionCall,
            'raw' => $data,
        ];
    }
}
