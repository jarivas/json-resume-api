<?php

namespace App\Services\ResumeImport;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class ResumeSourceResolver
{
    public function resolve(string $source): ResolvedResumeSource
    {
        if ($this->isGoogleDocsUrl($source)) {
            return $this->resolveGoogleDocsSource($source);
        }

        if ($this->isUrl($source)) {
            return $this->resolveRemoteFileSource($source);
        }

        return $this->resolveLocalFileSource($source);
    }

    protected function resolveGoogleDocsSource(string $source): ResolvedResumeSource
    {
        $documentId = $this->extractGoogleDocumentId($source);

        if ($documentId === null) {
            throw new RuntimeException('URL de Google Docs inválida.');
        }

        $exportUrl = 'https://docs.google.com/document/d/'.$documentId.'/export?format=txt';
        $response = Http::timeout(30)->get($exportUrl);

        if ($response->failed()) {
            throw new RuntimeException('No se pudo descargar el Google Doc público. Verifica permisos de lectura.');
        }

        $text = trim((string) $response->body());

        if ($text === '') {
            throw new RuntimeException('Google Docs no contiene texto legible para importar.');
        }

        return new ResolvedResumeSource(
            type: 'google-doc',
            source: $source,
            textContent: $text,
        );
    }

    protected function resolveRemoteFileSource(string $source): ResolvedResumeSource
    {
        $extension = $this->detectExtensionFromPath($source);
        $this->ensureSupportedExtension($extension);

        $response = Http::timeout(60)->get($source);

        if ($response->failed()) {
            throw new RuntimeException('No se pudo descargar el documento remoto: '.$source);
        }

        $temporaryPath = storage_path('app/imports/tmp-source-'.Str::uuid().'.'.$extension);
        File::ensureDirectoryExists(dirname($temporaryPath));
        File::put($temporaryPath, (string) $response->body());

        return new ResolvedResumeSource(
            type: $extension,
            source: $source,
            localPath: $temporaryPath,
            deleteLocalPathOnCleanup: true,
        );
    }

    protected function resolveLocalFileSource(string $source): ResolvedResumeSource
    {
        $localPath = File::exists($source) ? $source : base_path($source);

        if (! File::exists($localPath)) {
            throw new RuntimeException('No existe el archivo local: '.$source);
        }

        $extension = $this->detectExtensionFromPath($localPath);
        $this->ensureSupportedExtension($extension);

        return new ResolvedResumeSource(
            type: $extension,
            source: $source,
            localPath: $localPath,
        );
    }

    protected function ensureSupportedExtension(string $extension): void
    {
        if (! in_array($extension, ['pdf', 'doc', 'docx'], true)) {
            throw new RuntimeException('Formato no soportado. Usa PDF, DOCX, DOC o Google Docs público.');
        }
    }

    protected function detectExtensionFromPath(string $path): string
    {
        $extension = mb_strtolower((string) pathinfo(parse_url($path, PHP_URL_PATH) ?? $path, PATHINFO_EXTENSION));

        if ($extension !== '') {
            return $extension;
        }

        if (File::exists($path)) {
            $mimeType = File::mimeType($path);

            return match ($mimeType) {
                'application/pdf' => 'pdf',
                'application/msword' => 'doc',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
                default => $extension,
            };
        }

        return $extension;
    }

    protected function extractGoogleDocumentId(string $url): ?string
    {
        if (preg_match('#docs\.google\.com/document/d/([^/]+)#', $url, $matches) !== 1) {
            return null;
        }

        return $matches[1] ?? null;
    }

    protected function isGoogleDocsUrl(string $value): bool
    {
        return preg_match('#^https?://docs\.google\.com/document/d/[^/]+#', $value) === 1;
    }

    protected function isUrl(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }
}
