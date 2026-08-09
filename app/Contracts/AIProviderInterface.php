<?php

namespace App\Contracts;

interface AIProviderInterface
{
    /**
     * Send a chat message to the AI provider.
     *
     * @param string $systemPrompt System instruction for the AI
     * @param array $messages Conversation history
     * @param array $tools Available tools/functions for the AI
     * @return array Response from AI provider
     */
    public function chat(string $systemPrompt, array $messages, array $tools = []): array;

    /**
     * Check if the provider is configured and ready.
     *
     * @return bool
     */
    public function isConfigured(): bool;

    /**
     * Get the provider name.
     *
     * @return string
     */
    public function getName(): string;
}
