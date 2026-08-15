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
        $this->model = config('ai.gemini.model', 'gemini-3.6-flash');
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
            $url = "{$this->apiUrl}/{$this->model}:generateContent";

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'x-goog-api-key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($url, $payload);

            if (!$response->successful()) {
                $errorBody = $response->json();
                $errorMessage = $errorBody['error']['message'] ?? 'Unknown error';
                $errorStatus = $errorBody['error']['status'] ?? 'UNKNOWN';

                Log::error('Gemini API error', [
                    'http_status' => $response->status(),
                    'error_status' => $errorStatus,
                    'error_message' => $errorMessage,
                    'model' => $this->model,
                ]);
                throw new \Exception("Gemini API error: {$errorMessage}");
            }

            $data = $response->json();

            return $this->parseResponse($data);

        } catch (\Exception $e) {
            Log::error('Gemini provider exception', [
                'message' => $e->getMessage(),
                'model' => $this->model,
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
        // Handle blocked or empty response
        if (empty($data['candidates'])) {
            Log::warning('Gemini response has no candidates', [
                'model' => $this->model,
                'prompt_feedback' => $data['promptFeedback'] ?? null,
            ]);
            throw new \Exception('Response was blocked or empty.');
        }

        $candidate = $data['candidates'][0];

        // Check if response was blocked
        if (isset($candidate['finishReason']) && $candidate['finishReason'] !== 'STOP') {
            Log::warning('Gemini response blocked', [
                'finish_reason' => $candidate['finishReason'],
                'safety_ratings' => $candidate['safetyRatings'] ?? null,
            ]);
        }

        // Extract text from parts
        if (empty($candidate['content']['parts'])) {
            throw new \Exception('Invalid response structure from Gemini API.');
        }

        $text = '';
        foreach ($candidate['content']['parts'] as $part) {
            if (isset($part['text'])) {
                $text .= $part['text'];
            }
        }

        if (empty($text)) {
            throw new \Exception('No text content in Gemini response.');
        }

        // Check for function call
        $functionCall = null;
        foreach ($candidate['content']['parts'] as $part) {
            if (isset($part['functionCall'])) {
                $functionCall = $part['functionCall'];
                break;
            }
        }

        return [
            'text' => $text,
            'function_call' => $functionCall,
            'raw' => $data,
        ];
    }
}
