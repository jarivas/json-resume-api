<?php

namespace App\Services\Chat;

use App\Ai\Agents\ResumeAgent;
use App\Ai\Tools\McpToolFactory;
use App\Models\AiRequest;
use App\Models\Basic;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Exceptions\AiException;
use Laravel\Ai\Exceptions\RateLimitedException;
use Throwable;

class ChatService
{
    protected const string RATE_LIMIT_REPLY = 'El servicio de chat no esta disponible en este momento. Intenta nuevamente en unos minutos.';

    protected const int MAX_CHAT_PRIMARY_ATTEMPTS = 3;

    public function __construct() {}

    public function reply(string $message, ?string $sessionId = null, ?array $metadata = null): array
    {
        Log::info('Chat service received message', [
            'session_id' => $sessionId,
            'message_preview' => $this->truncateForLog((string) $this->maskValue($message)),
            'provider' => config('ai.default'),
        ]);

        $agent = $this->createAgent();
        $context = $this->buildContext($message, $agent);

        if (! $this->isQueryAllowed($agent, $message)) {
            Log::warning('Chat query validation failed - disallowed query', [
                'session_id' => $sessionId,
                'message_preview' => $this->truncateForLog((string) $this->maskValue($message)),
                'provider' => config('ai.default'),
            ]);

            return $this->handleDisallowedQuery($message, $context, $sessionId, $metadata);
        }

        $userPrompt = $this->assembleUserPrompt($context, $message);

        try {
            Log::debug('Chat service sending prompt to MCP-enabled agent', [
                'session_id' => $sessionId,
                'provider' => config('ai.default'),
                'agent_has_tools' => count((array) $agent->tools()) > 0,
                'mcp_servers_available' => [
                    'award', 'basic', 'certificate', 'education', 'interest', 'language',
                    'project', 'publication', 'reference', 'skill', 'volunteer', 'work',
                ],
            ]);

            $response = $this->promptWithRecovery($agent, $userPrompt);
        } catch (RateLimitedException $exception) {
            return $this->handleRateLimitedFailure($agent, $context, $message, $sessionId, $metadata, $exception);
        } catch (AiException $exception) {
            return $this->handleProviderErrorFailure($agent, $context, $message, $sessionId, $metadata, $exception);
        }

        $replyText = (string) $response;

        $this->logAiRequest(
            $context,
            $message,
            $replyText,
            $response,
            $sessionId,
            $metadata
        );

        return [
            'reply' => $replyText,
            'sources' => [],
            'session_id' => $sessionId,
        ];
    }

    protected function promptWithRecovery(ResumeAgent $agent, string $userPrompt): mixed
    {
        $lastException = null;

        for ($attempt = 1; $attempt <= self::MAX_CHAT_PRIMARY_ATTEMPTS; $attempt++) {
            try {
                return $this->promptAttempt($agent, $userPrompt);
            } catch (Throwable $exception) {
                $lastException = $exception;

                Log::warning('Chat primary prompt attempt failed.', [
                    'provider' => (string) config('ai.default'),
                    'attempt' => $attempt,
                    'max_attempts' => self::MAX_CHAT_PRIMARY_ATTEMPTS,
                    'models' => $this->safeModelCandidates($agent),
                    'exception_class' => $exception::class,
                    'exception_code' => $exception->getCode(),
                    'exception_message' => $exception->getMessage(),
                ]);
            }
        }

        Log::warning('Chat primary prompt exhausted retries. Trying model fallback.', [
            'provider' => (string) config('ai.default'),
            'attempts' => self::MAX_CHAT_PRIMARY_ATTEMPTS,
            'models' => $this->safeModelCandidates($agent),
            'last_exception_class' => $lastException ? $lastException::class : null,
            'last_exception_message' => $lastException?->getMessage(),
        ]);

        return $agent->promptWithModelFallback($userPrompt);
    }

    protected function promptAttempt(ResumeAgent $agent, string $userPrompt): mixed
    {
        return $agent->prompt($userPrompt);
    }

    protected function handleRateLimitedFailure(ResumeAgent $agent, string $context, string $message, ?string $sessionId, ?array $metadata, RateLimitedException $exception): array
    {
        $reply = self::RATE_LIMIT_REPLY;
        $safeMetadata = array_merge($metadata ?? [], [
            'rate_limited' => true,
            'models' => $agent->textModelCandidates(),
        ]);

        Log::warning('Chat service rate limited.', [
            'session_id' => $sessionId,
            'provider' => (string) config('ai.default'),
            'models' => $this->safeModelCandidates($agent),
            'exception_class' => $exception::class,
            'exception_code' => $exception->getCode(),
            'exception_message' => $exception->getMessage(),
            'message_preview' => $this->truncateForLog((string) $this->maskValue($message)),
            'metadata' => $this->maskValue($safeMetadata),
        ]);

        $this->storeFailureRequest(
            $context,
            $message,
            $reply,
            $sessionId,
            $safeMetadata
        );

        return [
            'reply' => $reply,
            'sources' => [],
            'session_id' => $sessionId,
        ];
    }

    protected function handleProviderErrorFailure(ResumeAgent $agent, string $context, string $message, ?string $sessionId, ?array $metadata, AiException $exception): array
    {
        $reply = self::RATE_LIMIT_REPLY;
        $safeMetadata = array_merge($metadata ?? [], [
            'provider_error' => true,
            'provider_error_code' => $exception->getCode(),
            'provider_error_message' => $exception->getMessage(),
            'models' => $agent->textModelCandidates(),
        ]);

        Log::error('Chat service provider error.', [
            'session_id' => $sessionId,
            'provider' => (string) config('ai.default'),
            'models' => $this->safeModelCandidates($agent),
            'exception_class' => $exception::class,
            'exception_code' => $exception->getCode(),
            'exception_message' => $exception->getMessage(),
            'message_preview' => $this->truncateForLog((string) $this->maskValue($message)),
            'metadata' => $this->maskValue($safeMetadata),
        ]);

        $this->storeFailureRequest(
            $context,
            $message,
            $reply,
            $sessionId,
            $safeMetadata
        );

        return [
            'reply' => $reply,
            'sources' => [],
            'session_id' => $sessionId,
        ];
    }

    protected function storeFailureRequest(string $context, string $message, string $reply, ?string $sessionId, array $metadata): void
    {
        AiRequest::create([
            'session_id' => $sessionId,
            'provider' => (string) config('ai.default'),
            'prompt' => $this->maskValue($context),
            'message' => $this->maskValue($message),
            'reply' => $this->maskValue($reply),
            'metadata' => $this->maskValue($metadata),
        ]);
    }

    protected function buildContext(string $message, ResumeAgent $agent): string
    {
        $contextParts = [];

        // Get basic profile information
        $basic = Basic::query()->first();
        if ($basic) {
            if (! empty($basic->summary)) {
                $contextParts[] = 'Summary: '.$basic->summary;
            }
            if (! empty($basic->name)) {
                $contextParts[] = 'Name: '.$basic->name;
            }
        }

        // Get semantically relevant resume fragments using embeddings
        try {
            $semanticContext = $agent->semanticContext($message, limit: 5);
            if (! empty($semanticContext)) {
                $contextParts[] = $semanticContext;
                Log::debug('Embeddings context retrieved for user message', [
                    'embedding_limit' => 5,
                    'semantic_context_length' => mb_strlen($semanticContext),
                ]);
            }
        } catch (Throwable $exception) {
            Log::warning('Failed to retrieve semantic context using embeddings', [
                'exception_class' => $exception::class,
                'exception_message' => $exception->getMessage(),
            ]);
        }

        return implode("\n\n", $contextParts);
    }

    protected function createAgent(): ResumeAgent
    {
        $mcpTools = McpToolFactory::getAllTools();

        $agentInstructions = <<<'TEXT'
You are a helpful assistant that answers questions about a user's resume. You have access to semantic context from embeddings and direct tools to query resume data.

## Semantic Context (Embeddings-Based)
The resume context provided was retrieved using semantic embeddings. This gives you relevant resume fragments ranked by similarity to the user's question. Use this context when it directly answers the user's query.

## Available Tools (Direct Resume Data Access)
You have access to the following tools to query resume data:
- get_basic_info: Get profile name, summary, contact details, location
- get_work_experience: Get job history with companies, titles, dates, achievements
- get_education: Get degrees, universities, graduation dates
- get_skills: Get technical and professional skills with proficiency levels
- get_projects: Get portfolio projects with descriptions and technologies
- get_certificates: Get certifications and their dates
- get_publications: Get published works and research papers
- get_awards: Get awards and recognitions
- get_languages: Get language proficiencies
- get_references: Get professional references
- get_interests: Get personal interests
- get_volunteer: Get volunteer and community service experience

## Strategy
1. **Use semantic context first** - It's already provided and fast
2. **Call tools when needed** - For complete data, verification, or specific details
3. **Be concise** - Summarize information relevant to the user's query
4. **Cite sources** - Reference embeddings or the specific tool used to get information
TEXT;

        Log::debug('Creating ResumeAgent with MCP tools', [
            'provider' => config('ai.default'),
            'tools_count' => count($mcpTools),
            'tools' => array_map(fn ($tool) => class_basename($tool), $mcpTools),
        ]);

        return new ResumeAgent($agentInstructions, [], $mcpTools);
    }

    protected function isQueryAllowed(ResumeAgent $agent, string $message): bool
    {
        try {
            if (method_exists($agent, 'allowsQuery')) {
                return $agent->allowsQuery($message);
            }
        } catch (Throwable $exception) {
            Log::warning('Chat query validation failed. Allowing query by default.', [
                'provider' => (string) config('ai.default'),
                'exception_class' => $exception::class,
                'exception_code' => $exception->getCode(),
                'exception_message' => $exception->getMessage(),
                'message_preview' => $this->truncateForLog((string) $this->maskValue($message)),
            ]);
        }

        return true;
    }

    protected function handleDisallowedQuery(string $message, string $context, ?string $sessionId, ?array $metadata): array
    {
        $refusal = 'Lo siento, sólo puedo responder preguntas relacionadas con el CV.';

        AiRequest::create([
            'session_id' => $sessionId,
            'provider' => config('ai.default'),
            'prompt' => $this->maskValue($context),
            'message' => $this->maskValue($message),
            'reply' => $this->maskValue($refusal),
            'metadata' => $this->maskValue(array_merge($metadata ?? [], ['blocked' => true])),
        ]);

        return [
            'reply' => $refusal,
            'sources' => [],
            'session_id' => $sessionId,
        ];
    }

    protected function assembleUserPrompt(string $context, string $message): string
    {
        return ($context ? ($context."\n\n") : '').$message;
    }

    protected function extractUsage(mixed $response): ?array
    {
        try {
            $usageObj = $response->usage ?? null;
            if ($usageObj) {
                return [
                    'prompt_tokens' => $usageObj->prompt_tokens ?? null,
                    'completion_tokens' => $usageObj->completion_tokens ?? null,
                    'total_tokens' => $usageObj->total_tokens ?? null,
                ];
            }
        } catch (Throwable) {
            // If usage extraction fails, return null
        }

        return null;
    }

    protected function logAiRequest(string $context, string $message, string $reply, mixed $response, ?string $sessionId, ?array $metadata): void
    {
        $usage = $this->extractUsage($response);

        $maskedPrompt = $this->maskValue($context);
        $maskedMessage = $this->maskValue($message);
        $maskedReply = $this->maskValue($reply);
        $maskedMetadata = $this->maskValue(array_merge($metadata ?? [], ['usage' => $usage]));

        Log::info('Chat service successful response', [
            'session_id' => $sessionId,
            'provider' => config('ai.default'),
            'message_preview' => $this->truncateForLog($maskedMessage),
            'reply_preview' => $this->truncateForLog($maskedReply),
            'usage' => $usage,
        ]);

        $this->logMcpActivity($sessionId, $maskedMessage, $maskedReply);

        AiRequest::create([
            'session_id' => $sessionId,
            'provider' => config('ai.default'),
            'prompt' => $maskedPrompt,
            'message' => $maskedMessage,
            'reply' => $maskedReply,
            'metadata' => $maskedMetadata,
        ]);
    }

    /**
     * Mask sensitive values recursively.
     * Strings: mask emails, phones, urls.
     */
    protected function maskValue(mixed $value): mixed
    {
        if (is_null($value)) {
            return null;
        }

        if (is_string($value)) {
            return $this->maskString($value);
        }

        if (is_array($value)) {
            $out = [];
            foreach ($value as $k => $v) {
                $out[$k] = $this->maskValue($v);
            }

            return $out;
        }

        return $value;
    }

    protected function maskString(string $s): string
    {
        // emails
        $s = preg_replace('/[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}/', '[REDACTED_EMAIL]', $s);

        // URLs
        $s = preg_replace('/https?:\/\/[^\s]+/i', '[REDACTED_URL]', $s);
        $s = preg_replace('/www\.[^\s]+/i', '[REDACTED_URL]', $s);

        // simple phone numbers (digits, spaces, +, -, parentheses)
        $s = preg_replace('/\+?\d[\d\s\-\(\)]{5,}\d/', '[REDACTED_PHONE]', $s);

        return $s;
    }

    protected function logMcpActivity(?string $sessionId, string $maskedMessage, string $maskedReply): void
    {
        $mcpServers = [
            'award' => ['award', 'recognition', 'premio'],
            'basic' => ['name', 'summary', 'contact', 'email', 'phone', 'location', 'url', 'perfil'],
            'certificate' => ['certificate', 'certificado', 'certification'],
            'education' => ['education', 'educación', 'degree', 'university', 'school', 'estudio'],
            'interest' => ['interest', 'interés', 'hobby', 'hobbies', 'passion'],
            'language' => ['language', 'idioma', 'languages', 'idiomas', 'speak'],
            'project' => ['project', 'proyecto', 'portfolio', 'work', 'desenvolvimiento'],
            'publication' => ['publication', 'publicación', 'published', 'paper', 'artículo'],
            'reference' => ['reference', 'referencia', 'references', 'recommend'],
            'skill' => ['skill', 'habilidad', 'technical', 'proficiency', 'competencia'],
            'volunteer' => ['volunteer', 'voluntario', 'volunteer work', 'community'],
            'work' => ['work', 'experience', 'job', 'position', 'empresa', 'empleo', 'experiencia'],
        ];

        $messageLower = mb_strtolower($maskedMessage);
        $replyLower = mb_strtolower($maskedReply);
        $activeMcpServers = [];

        foreach ($mcpServers as $server => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($messageLower, $keyword) || str_contains($replyLower, $keyword)) {
                    $activeMcpServers[] = $server;
                    break;
                }
            }
        }

        if (! empty($activeMcpServers)) {
            Log::debug('MCP activity detected in chat', [
                'session_id' => $sessionId,
                'probable_mcp_servers_used' => array_unique($activeMcpServers),
                'total_mcp_servers_available' => 12,
                'server_list' => [
                    'award', 'basic', 'certificate', 'education', 'interest', 'language',
                    'project', 'publication', 'reference', 'skill', 'volunteer', 'work',
                ],
            ]);
        }
    }

    protected function safeModelCandidates(ResumeAgent $agent): array
    {
        try {
            return $agent->textModelCandidates();
        } catch (Throwable $exception) {
            Log::warning('Could not resolve chat model candidates for logging.', [
                'provider' => (string) config('ai.default'),
                'exception_class' => $exception::class,
                'exception_code' => $exception->getCode(),
                'exception_message' => $exception->getMessage(),
            ]);

            return [];
        }
    }

    protected function truncateForLog(string $text, int $limit = 250): string
    {
        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        return mb_substr($text, 0, $limit).'...';
    }
}
