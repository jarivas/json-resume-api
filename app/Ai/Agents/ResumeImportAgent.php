<?php

namespace App\Ai\Agents;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Exceptions\AiException;
use Laravel\Ai\Exceptions\FailoverableException;
use Laravel\Ai\Promptable;
use Throwable;

#[MaxTokens(8192)]
class ResumeImportAgent implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return <<<'PROMPT'
Extract information from the resume text below and output it as a single JSON object.
Use ONLY the exact field names defined here. Do NOT add any other fields.
Do NOT include explanations, markdown, or text outside the JSON object.
The response must start with { and end with }.

--- OUTPUT FORMAT ---

{
  "name": "Full legal name",
  "headline": "Professional title or headline",
  "email": "email address",
  "phone": "phone number",
  "website": "personal website URL (must start with http:// or https://)",
  "summary": "short professional bio",
  "city": "city of residence",
  "country_code": "2-letter ISO country code e.g. ES, US",
  "profiles": [
    { "network": "GitHub", "url": "https://...", "username": "handle (optional)" }
  ],
  "jobs": [
    {
      "company": "Company name",
      "role": "Job title",
      "start": "YYYY-MM-DD",
      "end": "YYYY-MM-DD",
      "current": false,
      "description": "Summary of responsibilities",
      "highlights": ["Achievement 1", "Achievement 2"],
      "tech": ["PHP", "Docker", "MySQL"]
    }
  ],
  "schools": [
    {
      "school": "Institution name",
      "degree": "Bachelor / Master / Técnico Superior / etc.",
      "field": "Field of study",
      "start": "YYYY-MM-DD",
      "end": "YYYY-MM-DD",
      "current": false
    }
  ],
  "certs": [
    { "name": "Certificate name", "issuer": "Issuing organization", "date": "YYYY-MM-DD" }
  ],
  "languages": [
    { "language": "Spanish", "level": "Native" }
  ],
  "skills": ["Skill 1", "Skill 2"]
}

--- RULES ---
- Omit any field you have no data for. Do NOT use null, "", [], or {}.
- Dates: use YYYY-MM-DD. Year only → YYYY-01-01. Month+year → YYYY-MM-01.
- If a job or school is still ongoing (present / actual / en la actualidad / actualmente), set "current": true and omit "end".
- "website" must be a full URL starting with http:// or https://. If no URL is available, omit the field.
- "country_code" must be exactly 2 uppercase letters (ISO 3166-1 alpha-2). If unknown, omit it.
- "tech" inside "jobs": list every technology mentioned for that job.
- "skills": top-level general skills not tied to a specific job.
- "profiles": social/professional network profiles (GitHub, LinkedIn, etc.).

--- EXAMPLE ---

INPUT:
Ana García | Software Engineer | ana@example.com | +34 600 111 222 | https://anagarcia.dev
GitHub: https://github.com/anagarcia
TechCorp — Backend Developer (March 2021 – Present). Designed REST APIs. Tech: Node.js, PostgreSQL, Docker.
StartupXYZ — Junior Developer (Jan 2019 – Feb 2021). Front-end maintenance.
Education: Universidad Complutense de Madrid, Computer Science Bachelor, 2014–2018.
Certs: AWS Developer Associate, Amazon Web Services, 2022.
Languages: Spanish (native), English (professional).

OUTPUT:
{"name":"Ana García","headline":"Software Engineer","email":"ana@example.com","phone":"+34 600 111 222","website":"https://anagarcia.dev","profiles":[{"network":"GitHub","url":"https://github.com/anagarcia","username":"anagarcia"}],"jobs":[{"company":"TechCorp","role":"Backend Developer","start":"2021-03-01","current":true,"description":"Designed REST APIs.","tech":["Node.js","PostgreSQL","Docker"]},{"company":"StartupXYZ","role":"Junior Developer","start":"2019-01-01","end":"2021-02-01","current":false,"description":"Front-end maintenance."}],"schools":[{"school":"Universidad Complutense de Madrid","degree":"Bachelor","field":"Computer Science","start":"2014-01-01","end":"2018-01-01","current":false}],"certs":[{"name":"AWS Developer Associate","issuer":"Amazon Web Services","date":"2022-01-01"}],"languages":[{"language":"Spanish","level":"Native"},{"language":"English","level":"Professional"}]}

--- RESUME TEXT ---
[INSERT_THE_TEXT_HERE]
PROMPT;
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
                    $this->logRequestAttempt($provider, $candidateModel, $prompt, $attachments);
                    $response = $this->prompt(
                        $prompt,
                        attachments: $attachments,
                        provider: $provider,
                        model: $candidateModel,
                        timeout: config('ai.providers.'.$provider.'.timeout', null),
                    );

                    return (string) $response;
                } catch (FailoverableException $exception) {
                    $this->logRequestException($provider, $candidateModel, $prompt, $attachments, $exception);
                    $lastException = $exception;

                    continue 2;
                } catch (AiException $exception) {
                    if ($this->isModelNotFoundException($exception)) {
                        $this->logRequestException($provider, $candidateModel, $prompt, $attachments, $exception);
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

        return (string) $this->prompt($prompt, attachments: $attachments, provider: $this->providerName(), model: $this->model(), timeout: null);
    }

    /**
     * @return array<int, string>
     */
    public function textModelCandidates(?string $provider = null): array
    {
        $provider ??= $this->providerName();
        $primaryModel = $this->modelForProvider($provider);
        $alternatives = config('ai.providers.'.$provider.'.alternative_deployment', []);

        if (is_string($alternatives) && trim($alternatives) !== '') {
            $alternatives = [$alternatives];
        }

        if (! is_array($alternatives)) {
            $alternatives = [];
        }

        $candidates = [$primaryModel];
        foreach ($alternatives as $model) {
            if (is_string($model) && trim($model) !== '') {
                $candidates[] = $model;
            }
        }

        return array_values(array_unique($candidates));
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

    /**
     * Log an attempted AI request for debugging.
     *
     * @param  array<int,mixed>  $attachments
     */
    protected function logRequestAttempt(string $provider, string $model, string $prompt, array $attachments = []): void
    {
        $time = now()->toIsoString();

        Log::debug('AI Request Attempt: '."Provider={$provider}, Model={$model}, Time={$time}, Prompt=".Str::limit(preg_replace('/\\s+/', ' ', trim($prompt)), 1200).', Attachments='.json_encode($attachments));
    }

    /**
     * Log a failed AI request.
     *
     * @param  array<int,mixed>  $attachments
     */
    protected function logRequestException(string $provider, string $model, string $prompt, array $attachments, Throwable $exception): void
    {
        $time = now()->toIsoString();
        $message = $exception->getMessage();
        $trace = Str::limit($exception->getTraceAsString(), 4000);

        Log::error('AI Request Failed: '."Provider={$provider}, Model={$model}, Time={$time}, Message={$message}, Trace={$trace}, Prompt=".Str::limit(preg_replace('/\\s+/', ' ', trim($prompt)), 2000).', Attachments='.json_encode($attachments));
    }
}
