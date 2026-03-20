<?php

namespace App\Http\Controllers\Chat;

use App\Http\Requests\Chat\Chat as Request;
use App\Services\Chat\ChatService;
use Illuminate\Http\JsonResponse;

class Chat
{
    protected const POLICY_ERROR_MESSAGE = 'El mensaje debe centrarse en el currículum y no incluir instrucciones potencialmente riesgosas.';

    public function __construct(protected ChatService $chatService) {}

    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validated();
        $message = $data['message'];

        if (! $this->isAllowedMessage($message)) {
            return response()->json([
                'message' => 'Solo se permiten consultas relacionadas con el currículum.',
                'errors' => [
                    'message' => [
                        self::POLICY_ERROR_MESSAGE,
                    ],
                ],
            ], 422);
        }

        $result = $this->chatService->reply(
            $message,
            $data['session_id'] ?? null,
            $this->sanitizeMetadata($data['metadata'] ?? null),
        );

        return response()->json($result);
    }

    protected function isAllowedMessage(string $message): bool
    {
        return ! $this->containsRiskyInstruction($message);
    }

    protected function containsRiskyInstruction(string $message): bool
    {
        return preg_match(
            '/(ignore|ignora|system prompt|prompt del sistema|developer message|mensaje del desarrollador|jailbreak|bypass|api key|token|password|contraseña|secret|secreto)/iu',
            $message,
        ) === 1;
    }

    /**
     * @return array{language?: string, locale?: string}|null
     */
    protected function sanitizeMetadata(mixed $metadata): ?array
    {
        if (! is_array($metadata)) {
            return null;
        }

        $sanitized = array_filter([
            'language' => $this->sanitizeLanguage($metadata['language'] ?? null),
            'locale' => $this->sanitizeLocale($metadata['locale'] ?? null),
        ], static fn (?string $value): bool => $value !== null);

        return $sanitized === [] ? null : $sanitized;
    }

    protected function sanitizeLanguage(mixed $language): ?string
    {
        if (! is_string($language)) {
            return null;
        }

        $normalizedLanguage = mb_strtolower(trim($language));

        if (preg_match('/^[a-z]{2,3}$/', $normalizedLanguage) !== 1) {
            return null;
        }

        return $normalizedLanguage;
    }

    protected function sanitizeLocale(mixed $locale): ?string
    {
        if (! is_string($locale)) {
            return null;
        }

        $normalizedLocale = str_replace('_', '-', trim($locale));

        if (preg_match('/^[a-z]{2}(?:-[A-Z]{2})?$/', $normalizedLocale) !== 1) {
            $normalizedLocale = strtolower($normalizedLocale);
        }

        if (preg_match('/^[a-z]{2}(?:-[a-z]{2})?$/', $normalizedLocale) !== 1) {
            return null;
        }

        return preg_replace_callback(
            '/^[a-z]{2}(?:-[a-z]{2})?$/',
            static fn (array $matches): string => isset($matches[0][3])
                ? substr($matches[0], 0, 2).'-'.strtoupper(substr($matches[0], 3, 2))
                : $matches[0],
            $normalizedLocale,
        );
    }
}
