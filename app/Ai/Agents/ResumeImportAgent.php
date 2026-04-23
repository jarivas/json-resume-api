<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Exceptions\AiException;
use Laravel\Ai\Exceptions\FailoverableException;
use Laravel\Ai\Promptable;
use Throwable;

#[MaxTokens(16384)]
class ResumeImportAgent implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return <<<'PROMPT'
Convert the resume provided in the user message into a single JSON object that conforms to the JSON Resume schema.
Use ONLY the JSON Resume field names and structure. Do NOT add any other top-level fields.
Do NOT include explanations, markdown, or text outside the JSON object. The response must start with { and end with }.

Important: these imports are certificate-centric. If the document is (or contains) a certificate or certificate listing, prioritize extracting the certificate(s) and every skill or technology explicitly associated with each certificate. For each certificate include `name`, `issuer`, `date` and `url` when available. Also list all skills mentioned by the certificate in the top-level `skills` array (as objects with `name` and optionally `keywords`). If the certificate text links skills to the certificate, reflect that relation by including the skills in the certificate `summary` (short comma-separated list) and in the top-level `skills` array.

For the official schema reference visit: https://raw.githubusercontent.com/jsonresume/resume-schema/refs/heads/master/schema.json

--- OUTPUT EXAMPLE (JSON Resume v1.0.0) ---

{
    "basics": {
        "name": "Richard Hendriks",
        "label": "Programmer",
        "image": "",
        "email": "richard.hendriks@mail.com",
        "phone": "(912) 555-4321",
        "url": "http://richardhendricks.example.com",
        "summary": "Richard hails from Tulsa. He has earned degrees from the University of Oklahoma and Stanford. (Go Sooners and Cardinal!) Before starting Pied Piper, he worked for Hooli as a part time software developer. While his work focuses on applied information theory, mostly optimizing lossless compression schema of both the length-limited and adaptive variants, his non-work interests range widely, everything from quantum computing to chaos theory. He could tell you about it, but THAT would NOT be a “length-limited” conversation!",
        "location": {
            "address": "2712 Broadway St",
            "postalCode": "CA 94115",
            "city": "San Francisco",
            "countryCode": "US",
            "region": "California"
        },
        "profiles": [
            {
                "network": "Twitter",
                "username": "neutralthoughts",
                "url": "https://www.twitter.com"
            },
            {
                "network": "SoundCloud",
                "username": "dandymusicnl",
                "url": "https://soundcloud.example.com/dandymusicnl"
            }
        ]
    },
    "work": [
        {
            "name": "Pied Piper",
            "location": "Palo Alto, CA",
            "description": "Awesome compression company",
            "position": "CEO/President",
            "url": "http://piedpiper.example.com",
            "startDate": "2013-12-01",
            "endDate": "2014-12-01",
            "summary": "Pied Piper is a multi-platform technology based on a proprietary universal compression algorithm that has consistently fielded high Weisman Scores™ that are not merely competitive, but approach the theoretical limit of lossless compression.",
            "highlights": [
                "Build an algorithm for artist to detect if their music was violating copy right infringement laws",
                "Successfully won Techcrunch Disrupt",
                "Optimized an algorithm that holds the current world record for Weisman Scores"
            ]
        }
    ],
    "volunteer": [
        {
            "organization": "CoderDojo",
            "position": "Teacher",
            "url": "http://coderdojo.example.com/",
            "startDate": "2012-01-01",
            "endDate": "2013-01-01",
            "summary": "Global movement of free coding clubs for young people.",
            "highlights": [
                "Awarded 'Teacher of the Month'"
            ]
        }
    ],
    "education": [
        {
            "institution": "University of Oklahoma",
            "url": "https://www.ou.edu/",
            "area": "Information Technology",
            "studyType": "Bachelor",
            "startDate": "2011-06-01",
            "endDate": "2014-01-01",
            "score": "4.0",
            "courses": [
                "DB1101 - Basic SQL",
                "CS2011 - Java Introduction"
            ]
        }
    ],
    "awards": [
        {
            "title": "Digital Compression Pioneer Award",
            "date": "2014-11-01",
            "awarder": "Techcrunch",
            "summary": "There is no spoon."
        }
    ],
    "publications": [
        {
            "name": "Video compression for 3d media",
            "publisher": "Hooli",
            "releaseDate": "2014-10-01",
            "url": "http://en.wikipedia.org/wiki/Silicon_Valley_(TV_series)",
            "summary": "Innovative middle-out compression algorithm that changes the way we store data."
        }
    ],
    "skills": [
        {
            "name": "Web Development",
            "level": "Master",
            "keywords": [
                "HTML",
                "CSS",
                "Javascript"
            ]
        },
        {
            "name": "Compression",
            "level": "Master",
            "keywords": [
                "Mpeg",
                "MP4",
                "GIF"
            ]
        }
    ],
    "languages": [
        {
            "language": "English",
            "fluency": "Native speaker"
        }
    ],
    "interests": [
        {
            "name": "Wildlife",
            "keywords": [
                "Ferrets",
                "Unicorns"
            ]
        }
    ],
    "references": [
        {
            "name": "Erlich Bachman",
            "reference": "It is my pleasure to recommend Richard, his performance working as a consultant for Main St. Company proved that he will be a valuable addition to any company."
        }
    ],
    "projects": [
        {
            "name": "Miss Direction",
            "description": "A mapping engine that misguides you",
            "highlights": [
                "Won award at AIHacks 2016",
                "Built by all women team of newbie programmers",
                "Using modern technologies such as GoogleMaps, Chrome Extension and Javascript"
            ],
            "keywords": [
                "GoogleMaps", "Chrome Extension", "Javascript"
            ],
            "startDate": "2016-08-24",
            "endDate": "2016-08-24",
            "url": "http://missdirection.example.com",
            "roles": [
                "Team lead", "Designer"
            ],
            "entity": "Smoogle",
            "type": "application"
        }
    ],
    "meta": {
        "canonical": "https://raw.githubusercontent.com/jsonresume/resume-schema/v1.0.0/sample.resume.json",
        "version": "v1.0.0",
        "lastModified": "2017-12-24T15:53:00"
    }
}

--- RULES ---
- Output must conform to the JSON Resume schema linked above.
- Omit any field that has no data; do not output null, empty strings, empty arrays, or empty objects.
- Dates: prefer full ISO dates YYYY-MM-DD. If only year is present output YYYY-01-01; if month and year output YYYY-MM-01.
- For ongoing roles or studies use the appropriate fields (e.g. omit endDate when present).

--- RESUME TEXT ---
[The resume text or attachment is in the user message]
PROMPT;
    }

    /**
     * Define the expected structured JSON schema for resume imports.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'certificate' => $schema->object([
                'name' => $schema->string()->required(),
                'issuer' => $schema->string()->required(),
                'date' => $schema->string()->nullable(),
                'url' => $schema->string()->nullable(),
            ])->required(),

            'skills' => $schema->array()->items(
                $schema->object([
                    'name' => $schema->string()->required(),
                    'level' => $schema->string()->nullable(),
                    'keywords' => $schema->array()->items($schema->string()),
                ])
            ),

            'education' => $schema->array()->items(
                $schema->object([
                    'institution' => $schema->string()->required(),
                    'url' => $schema->string()->nullable(),
                    'area' => $schema->string()->nullable(),
                    'studyType' => $schema->string()->nullable(),
                    'startDate' => $schema->string()->nullable(),
                    'endDate' => $schema->string()->nullable(),
                ])
            ),
        ];
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
                    // Prepend a strict instruction forcing JSON-only output that
                    // matches the structured schema to improve adherence.
                    $strictHeader = 'OUTPUT ONLY a single valid JSON object matching the schema. Do NOT include any explanation or extra text.';
                    $fullPrompt = $strictHeader."\n\n".$prompt;

                    $this->logRequestAttempt($provider, $candidateModel, $fullPrompt, $attachments);
                    $response = $this->prompt(
                        $fullPrompt,
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

        return (string) $this->prompt($prompt, attachments: $attachments, provider: $this->providerName(), model: $this->modelForProvider($this->providerName()), timeout: null);
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

        $attachmentsSummary = array_map(function ($a) {
            if (is_string($a)) {
                return ['type' => 'string', 'length' => strlen($a)];
            }

            if (is_array($a)) {
                return ['type' => 'array', 'count' => count($a)];
            }

            if (is_object($a)) {
                $class = get_class($a);

                if (isset($a->path) && is_string($a->path)) {
                    return ['type' => $class, 'path' => $a->path];
                }

                if (isset($a->localPath) && is_string($a->localPath)) {
                    return ['type' => $class, 'path' => $a->localPath];
                }

                if (method_exists($a, 'path')) {
                    try {
                        $p = $a->path();
                        if (is_string($p)) {
                            return ['type' => $class, 'path' => $p];
                        }
                    } catch (Throwable $e) {
                        // ignore
                    }
                }

                if (method_exists($a, 'getPath')) {
                    try {
                        $p = $a->getPath();
                        if (is_string($p)) {
                            return ['type' => $class, 'path' => $p];
                        }
                    } catch (Throwable $e) {
                        // ignore
                    }
                }

                return ['type' => $class];
            }

            return ['type' => gettype($a)];
        }, $attachments);

        $promptClean = preg_replace('/\s+/', ' ', trim($prompt));
        if (preg_match('/^%PDF-/', $promptClean) || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $promptClean)) {
            $promptForLog = '[binary document omitted]';
        } else {
            $promptForLog = Str::limit($promptClean, 1200);
        }

        Log::debug('AI Request Attempt: '."Provider={$provider}, Model={$model}, Time={$time}, Prompt=".$promptForLog.', Attachments='.json_encode($attachmentsSummary));
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

        $attachmentsSummary = array_map(function ($a) {
            if (is_string($a)) {
                return ['type' => 'string', 'length' => strlen($a)];
            }

            if (is_array($a)) {
                return ['type' => 'array', 'count' => count($a)];
            }

            if (is_object($a)) {
                $class = get_class($a);
                if (isset($a->path) && is_string($a->path)) {
                    return ['type' => $class, 'path' => $a->path];
                }

                if (isset($a->localPath) && is_string($a->localPath)) {
                    return ['type' => $class, 'path' => $a->localPath];
                }

                return ['type' => $class];
            }

            return ['type' => gettype($a)];
        }, $attachments);

        $promptClean = preg_replace('/\s+/', ' ', trim($prompt));
        if (preg_match('/^%PDF-/', $promptClean) || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $promptClean)) {
            $promptForLog = '[binary document omitted]';
        } else {
            $promptForLog = Str::limit($promptClean, 2000);
        }

        Log::error('AI Request Failed: '."Provider={$provider}, Model={$model}, Time={$time}, Message={$message}, Trace={$trace}, Prompt=".$promptForLog.', Attachments='.json_encode($attachmentsSummary));
    }
}
