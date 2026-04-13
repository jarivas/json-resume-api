<?php

namespace App\Http\Controllers\Import;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait ImportFileStorageTrait
{
    /**
     * Download a remote URL and store it under the imports disk.
     * Returns [storagePath, fullPath] or [null, null] on failure.
     *
     * @return array{0:?string,1:?string}
     */
    private function storeRemoteUrl(string $url): array
    {
        $resp = Http::get($url);

        if (! $resp->ok()) {
            return [null, null];
        }

        $contentType = $resp->header('Content-Type');
        $ext = 'html';

        if (str_contains($contentType, 'pdf')) {
            $ext = 'pdf';
        } elseif (str_contains($contentType, 'word') || str_contains($contentType, 'officedocument')) {
            $ext = 'docx';
        }

        $storagePath = 'imports/url-'.Str::uuid().'.'.$ext;
        Storage::put($storagePath, $resp->body());

        return [$storagePath, Storage::path($storagePath)];
    }

    /**
     * Store an uploaded file, preserving/inferring extension. Returns [storagePath, fullPath].
     *
     * @return array{0:string,1:string}
     */
    private function storeUploadedFile(UploadedFile $file): array
    {
        $ext = (string) $file->getClientOriginalExtension();

        if ($ext === '') {
            $mime = $file->getClientMimeType();

            $ext = match ($mime) {
                'application/pdf' => 'pdf',
                'application/msword' => 'doc',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                default => '',
            };
        }

        $ext = $ext !== '' ? '.'.$ext : '';

        $filename = 'upload-'.Str::uuid().$ext;
        $storagePath = $file->storeAs('imports', $filename);

        return [$storagePath, Storage::path($storagePath)];
    }
}
