<?php

namespace App\Services\ResumeImport;

use App\Ai\Agents\ResumeImportAgent;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Ai\Files\Document;
use RuntimeException;

class ResumeDocumentImportService
{
    public function __construct(protected ResumeSourceResolver $sourceResolver) {}

    /**
     * @return array{import_output: string, json_kept: bool, disk: string, path: string}
     */
    public function import(string $source, string $disk, string $path, bool $keepJson): array
    {
        $resolvedSource = $this->sourceResolver->resolve($source);
        $temporaryJsonPath = storage_path('app/imports/tmp-import-'.Str::uuid().'.json');

        try {
            $payload = $this->mapSourceToJsonResume($resolvedSource);
            $json = $this->encodePayload($payload);

            File::ensureDirectoryExists(dirname($temporaryJsonPath));
            File::put($temporaryJsonPath, $json);
            Storage::disk($disk)->put($path, $json);

            $exitCode = Artisan::call('data:import', [
                'source' => $temporaryJsonPath,
                '--disk' => $disk,
                '--path' => $path,
            ]);

            $output = trim(Artisan::output());

            if ($exitCode !== 0) {
                throw new RuntimeException($output !== '' ? $output : 'data:import falló al procesar el JSON generado.');
            }

            if (! $keepJson) {
                Storage::disk($disk)->delete($path);
            }

            return [
                'import_output' => $output,
                'json_kept' => $keepJson,
                'disk' => $disk,
                'path' => $path,
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
        $prompt = $this->buildPrompt($source);
        $attachments = $this->buildAttachments($source);
        $responseText = $agent->promptWithModelFallback($prompt, $attachments);
        $decoded = json_decode($this->extractJsonPayload($responseText), true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($decoded)) {
            throw new RuntimeException('El agente no devolvió un objeto JSON válido para JSON Resume.');
        }

        return $decoded;
    }

    protected function buildPrompt(ResolvedResumeSource $source): string
    {
        if ($source->type === 'google-doc') {
            return "Convierte este contenido de CV a JSON Resume.\n\n".(string) $source->textContent;
        }

        return 'Convierte el documento adjunto a JSON Resume. Usa solo información del documento.';
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

        if (preg_match('/```(?:json)?\s*(\{[\s\S]*\}|\[[\s\S]*\])\s*```/i', $trimmed, $matches) === 1) {
            return trim($matches[1]);
        }

        return $trimmed;
    }
}
