<?php

namespace App\Services\ResumeImport;

use App\Ai\Agents\ResumeImportAgent;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Ai\Files\Document;
use RuntimeException;

class ResumeDocumentImportService
{
    public function __construct(protected ResumeSourceResolver $sourceResolver) {}

    /**
     * @return array{import_output: string}
     */
    public function import(string $source): array
    {
        $resolvedSource = $this->sourceResolver->resolve($source);
        $temporaryJsonPath = sprintf(
            '%s/app/imports/tmp-import-%s.json',
            storage_path(),
            Str::uuid()
        );

        try {
            $payload = $this->mapSourceToJsonResume($resolvedSource);
            $json = $this->encodePayload($payload);

            File::ensureDirectoryExists(dirname($temporaryJsonPath));
            File::put($temporaryJsonPath, $json);

            $exitCode = Artisan::call('data:import', [
                'source' => $temporaryJsonPath,
            ]);

            $output = trim(Artisan::output());

            if ($exitCode !== 0) {
                throw new RuntimeException($output !== '' ? $output : 'data:import falló al procesar el JSON generado.');
            }

            return [
                'import_output' => $output,
            ];
        } finally {
            File::delete($temporaryJsonPath);

            if ($resolvedSource->deleteLocalPathOnCleanup && is_string($resolvedSource->localPath) && $resolvedSource->localPath !== '') {
                File::delete($resolvedSource->localPath);
            }
        }
    }

    /**
     * @return array<mixed>
     */
    protected function mapSourceToJsonResume(ResolvedResumeSource $source): array
    {
        $agent = new ResumeImportAgent;
        $attachments = $this->buildAttachments($source);
        $userMessage = 'Convert this resume to JSON.';

        if ($source->type === 'google-doc') {
            $userMessage = trim((string) $source->textContent);
            $attachments = [];
        } elseif (count($attachments) > 0 && is_string($source->localPath) && $source->localPath !== '') {
            try {
                $extractor = new DocumentTextExtractor;
                $extracted = $extractor->extract($source->localPath);
            } catch (\Throwable $e) {
                $extracted = '';
            }

            if ($extracted !== '') {
                $userMessage = trim(mb_substr($extracted, 0, 20000));
                $attachments = [];
            } else {
                // If we couldn't extract readable text, avoid attaching the
                // raw binary document to the agent prompt — some providers
                // may end up including the binary content in the prompt.
                Log::warning('DocumentTextExtractor returned empty text; not attaching raw document to AI prompt.', ['path' => $source->localPath]);
                $attachments = [];
            }
        }

        $responseText = $agent->promptWithModelFallback($userMessage, $attachments);
        Log::debug("Response Text: {$responseText}");

        try {
            $decoded = json_decode($this->extractJsonPayload($responseText), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new RuntimeException('El agente devolvió JSON malformado o incompleto (posible respuesta truncada): '.$e->getMessage());
        }

        if (! is_array($decoded)) {
            throw new RuntimeException('El agente no devolvió un objeto JSON válido para JSON Resume.');
        }

        $normalizer = new IntermediateToJsonResumeNormalizer;
        $normalized = $normalizer->normalize($decoded);

        Log::debug('Decoded JSON Resume (decoded):', $normalized);

        return $normalized;
    }

    /**
     * @return array<int, mixed>
     */
    protected function buildAttachments(ResolvedResumeSource $source): array
    {
        if (! is_string($source->localPath) || $source->localPath === '') {
            return [];
        }

        return [Document::fromPath($source->localPath)];
    }

    /**
     * @param  array<mixed>  $payload
     */
    protected function encodePayload(array $payload): string
    {
        return json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    protected function extractJsonPayload(string $responseText): string
    {
        $trimmed = trim($responseText);

        // 1) If the model returned a fenced JSON block, use that.
        if (preg_match('/```(?:json)?\s*(\{[\s\S]*\}|\[[\s\S]*\])\s*```/i', $trimmed, $matches) === 1) {
            return trim($matches[1]);
        }

        // 2) If the response ends with a JSON object or array, extract that tail.
        if (preg_match('/(\{[\s\S]*\})\s*$/', $trimmed, $matches) === 1) {
            return trim($matches[1]);
        }

        if (preg_match('/(\[[\s\S]*\])\s*$/', $trimmed, $matches) === 1) {
            return trim($matches[1]);
        }

        // 3) As a fallback, try to find the first JSON object/array in the text.
        if (preg_match('/(\{[\s\S]*\})/', $trimmed, $matches) === 1) {
            return trim($matches[1]);
        }

        if (preg_match('/(\[[\s\S]*\])/', $trimmed, $matches) === 1) {
            return trim($matches[1]);
        }

        // 4) Final fallback: attempt to extract a balanced JSON block (handles nested braces and strings).
        $balanced = $this->extractBalancedJson($trimmed);
        if ($balanced !== null) {
            return $balanced;
        }

        // 5) Nothing found: return the whole trimmed text (will cause decode error upstream).
        return $trimmed;
    }

    /**
     * Attempt to extract the first balanced JSON object or array from the text.
     * This scans for the first '{' or '[' and walks forward tracking nesting,
     * taking care to ignore braces that appear inside strings.
     */
    protected function extractBalancedJson(string $text): ?string
    {
        $len = strlen($text);
        for ($i = 0; $i < $len; $i++) {
            $char = $text[$i];
            if ($char !== '{' && $char !== '[') {
                continue;
            }

            $stack = [];
            $start = $i;
            $inString = false;
            $escape = false;

            for ($j = $i; $j < $len; $j++) {
                $c = $text[$j];

                if ($inString) {
                    if ($escape) {
                        $escape = false;
                    } elseif ($c === '\\') {
                        $escape = true;
                    } elseif ($c === '"') {
                        $inString = false;
                    }

                    continue;
                }

                if ($c === '"') {
                    $inString = true;

                    continue;
                }

                if ($c === '{' || $c === '[') {
                    $stack[] = $c;

                    continue;
                }

                if ($c === '}' || $c === ']') {
                    if (empty($stack)) {
                        break;
                    }

                    $open = array_pop($stack);
                    if (($open === '{' && $c !== '}') || ($open === '[' && $c !== ']')) {
                        // mismatched braces; abandon this start
                        break 2;
                    }

                    if (empty($stack)) {
                        // found balanced block
                        $end = $j;

                        return trim(substr($text, $start, $end - $start + 1));
                    }
                }
            }
        }

        return null;
    }
}
