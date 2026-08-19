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
    protected array $fallbackModels;

    public function __construct()
    {
        $this->apiKey = config('ai.gemini.api_key', '');
        $this->model = config('ai.gemini.model', 'gemini-3.6-flash');
        $this->apiUrl = config('ai.gemini.api_url');
        $this->timeout = config('ai.gemini.timeout', 30);
        $this->maxTokens = config('ai.gemini.max_tokens', 2048);
        $this->temperature = config('ai.gemini.temperature', 0.7);
        $this->fallbackModels = config('ai.gemini.fallback_models', []);
    }

    public function chat(string $systemPrompt, array $messages, array $tools = []): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Gemini API key is not configured.');
        }

        $payload = [
            'contents' => $this->buildContents($messages),
            'generationConfig' => [
                'temperature' => $this->temperature,
                'maxOutputTokens' => $this->maxTokens,
            ],
        ];

        if (!empty($systemPrompt)) {
            $payload['systemInstruction'] = [
                'parts' => [
                    ['text' => $systemPrompt],
                ],
            ];
        }

        // Add tools if provided
        if (!empty($tools)) {
            $payload['tools'] = [
                ['function_declarations' => $tools]
            ];
        }

        try {
            $response = $this->sendWithFallback($payload);

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

    protected function sendWithFallback(array $payload)
    {
        $models = array_values(array_unique(array_filter(array_merge([$this->model], $this->fallbackModels))));
        $lastErrorMessage = 'Unknown error';

        foreach ($models as $model) {
            $url = "{$this->apiUrl}/{$model}:generateContent";

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'x-goog-api-key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($url, $payload);

            if ($response->successful()) {
                if ($model !== $this->model) {
                    Log::info('Gemini fallback model succeeded', [
                        'primary_model' => $this->model,
                        'fallback_model' => $model,
                    ]);
                }

                return $response;
            }

            $errorBody = $response->json();
            $errorMessage = $errorBody['error']['message'] ?? 'Unknown error';
            $errorStatus = $errorBody['error']['status'] ?? 'UNKNOWN';
            $lastErrorMessage = $errorMessage;

            Log::error('Gemini API error', [
                'http_status' => $response->status(),
                'error_status' => $errorStatus,
                'error_message' => $errorMessage,
                'model' => $model,
            ]);

            if (!$this->shouldTryFallback($response->status(), $errorStatus)) {
                break;
            }
        }

        throw new \Exception("Gemini API error: {$lastErrorMessage}");
    }

    protected function shouldTryFallback(int $httpStatus, string $errorStatus): bool
    {
        return in_array($httpStatus, [429, 500, 502, 503, 504], true)
            || in_array($errorStatus, ['RESOURCE_EXHAUSTED', 'UNAVAILABLE', 'INTERNAL', 'DEADLINE_EXCEEDED'], true);
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    public function getName(): string
    {
        return 'gemini';
    }

    protected function buildContents(array $messages): array
    {
        $contents = [];

        foreach ($messages as $message) {
            if (empty($message['content'])) {
                continue;
            }

            $role = $message['role'] === 'user' ? 'user' : 'model';
            $contents[] = [
                'role' => $role,
                'parts' => [['text' => $message['content']]],
            ];
        }

        if (empty($contents)) {
            throw new \Exception('No message content was provided to Gemini.');
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

        $finishReason = $candidate['finishReason'] ?? null;

        if ($finishReason && !in_array($finishReason, ['STOP', 'MAX_TOKENS'], true)) {
            Log::warning('Gemini response blocked', [
                'finish_reason' => $finishReason,
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

        // Check for function call
        $functionCall = null;
        foreach ($candidate['content']['parts'] as $part) {
            if (isset($part['functionCall'])) {
                $functionCall = $part['functionCall'];
                break;
            }
        }

        if (empty($text)) {
            throw new \Exception($functionCall ? 'Gemini returned a function call without text content.' : 'No text content in Gemini response.');
        }

        return [
            'text' => $text,
            'function_call' => $functionCall,
            'raw' => $data,
        ];
    }
}
