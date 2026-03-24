<?php

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Exceptions\AiException;
use Laravel\Ai\Exceptions\FailoverableException;
use Laravel\Ai\Promptable;
use Throwable;

class ResumeImportAgent implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return <<<'TXT'
You extract resume information from a document and return ONLY valid JSON (no markdown).
Output must follow JSON Resume structure keys: basics, work, volunteer, education, awards,
certificates, publications, skills, languages, interests, references, projects.

Rules:
- Never include commentary or code fences.
- Use arrays for highlights, courses, and keywords.
- Keep dates in ISO partial formats accepted by JSON Resume: YYYY, YYYY-MM, YYYY-MM-DD.
- If a section is unknown, omit it.
- Preserve original language when possible.
TXT;
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

        return (string) $this->prompt($prompt, attachments: $attachments, provider: $this->providerName(), model: $this->model());
    }

    public function model(): string
    {
        return (string) config('ai.providers.'.$this->providerName().'.deployment');
    }

    /**
     * @return array<int, string>
     */
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

    /**
     * @return array<int, string>
     */
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
