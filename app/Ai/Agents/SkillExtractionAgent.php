<?php

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Exceptions\AiException;
use Laravel\Ai\Exceptions\FailoverableException;
use Laravel\Ai\Promptable;
use Throwable;

class SkillExtractionAgent implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return 'You are a CV/resume enrichment assistant. Analyze professional education and certification documents to extract technical skills, domain competencies, technologies, and tools. Return ONLY a valid JSON array of skill objects — no markdown, no code fences, no explanatory text. Each object must have a "name" field with a descriptive, self-contained skill name. Optional fields: "level" (beginner|intermediate|advanced|expert) and "keywords" (array of related tools or subtopics). Never include vague soft skills.';
    }

    /**
     * @param  array<int, mixed>  $attachments
     */
    public function promptWithModelFallback(string $prompt, array $attachments = []): string
    {
        $lastException = null;

        foreach ($this->providerCandidates() as $provider) {
            foreach ($this->textModelCandidates($provider) as $candidateModel) {
                try {
                    $response = $this->prompt(
                        $prompt,
                        attachments: $attachments,
                        provider: $provider,
                        model: $candidateModel,
                        timeout: config('ai.providers.'.$provider.'.timeout', null),
                    );

                    return (string) $response;
                } catch (FailoverableException $exception) {
                    $lastException = $exception;

                    continue 2;
                } catch (AiException $exception) {
                    if ($this->isModelNotFoundException($exception)) {
                        $lastException = $exception;

                        continue;
                    }

                    throw $exception;
                }
            }
        }

        if ($lastException instanceof Throwable) {
            throw $lastException;
        }

        return (string) $this->prompt($prompt, attachments: $attachments, provider: $this->providerName(), model: $this->model(), timeout: config('ai.providers.'.$this->providerName().'.timeout', null));
    }

    public function model(): string
    {
        return (string) config('ai.providers.'.$this->providerName().'.deployment');
    }

    public function textModelCandidates(?string $provider = null): array
    {
        $provider ??= $this->providerName();
        $alternatives = config('ai.providers.'.$provider.'.alternative_deployment', []);

        if (is_string($alternatives) && trim($alternatives) !== '') {
            $alternatives = [$alternatives];
        }

        if (! is_array($alternatives)) {
            $alternatives = [];
        }

        $models = [$this->modelForProvider($provider)];

        foreach ($alternatives as $alternative) {
            if (is_string($alternative) && trim($alternative) !== '') {
                $models[] = $alternative;
            }
        }

        return array_values(array_unique($models));
    }

    public function providerCandidates(): array
    {
        $provider = $this->providerName();
        $fallbackProviders = config('ai.providers.'.$provider.'.fallback_providers', []);

        if (is_string($fallbackProviders) && trim($fallbackProviders) !== '') {
            $fallbackProviders = [$fallbackProviders];
        }

        if (! is_array($fallbackProviders)) {
            $fallbackProviders = [];
        }

        $providers = [$provider];

        foreach ($fallbackProviders as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                $providers[] = $candidate;
            }
        }

        return array_values(array_unique($providers));
    }

    protected function isModelNotFoundException(AiException $exception): bool
    {
        if ($exception->getCode() === 404) {
            return true;
        }

        $message = mb_strtolower($exception->getMessage());

        return str_contains($message, 'not_found')
            || (str_contains($message, 'model') && str_contains($message, 'not found'));
    }

    protected function modelForProvider(string $provider): string
    {
        return (string) config('ai.providers.'.$provider.'.deployment');
    }

    protected function providerName(): string
    {
        return (string) config('ai.default', 'openai');
    }
}
