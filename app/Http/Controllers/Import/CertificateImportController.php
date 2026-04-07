<?php

namespace App\Http\Controllers\Import;

use App\Http\Requests\Import\ImportCertificate as Request;
use App\Models\Certificate;
use App\Services\Import\DocumentSkillImportService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificateImportController
{
    public function __invoke(Request $request, DocumentSkillImportService $service)
    {
        $data = $request->validated();

        $fullPath = null;

        if (! empty($data['url'])) {
            $url = $data['url'];

            $resp = Http::get($url);

            if (! $resp->ok()) {
                return response()->json(['ok' => false, 'message' => 'No se pudo descargar la URL proporcionada.'], 422);
            }

            $contentType = $resp->header('Content-Type', 'text/html');
            $ext = 'html';

            if (str_contains($contentType, 'pdf')) {
                $ext = 'pdf';
            } elseif (str_contains($contentType, 'json')) {
                $ext = 'json';
            } elseif (str_contains($contentType, 'word') || str_contains($contentType, 'officedocument')) {
                $ext = 'docx';
            }

            $filename = 'imports/url-'.Str::uuid().'.'.$ext;
            Storage::put($filename, $resp->body());
            $fullPath = Storage::path($filename);
        } else {
            /** @var UploadedFile $file */
            $file = $request->file('file');

            $path = $file->store('imports');
            $fullPath = Storage::path($path);
        }

        $certificate = Certificate::findOrFail($data['certificate_id']);

        $skills = $service->importForCertificate($certificate, $fullPath);

        return response()->json(['ok' => true, 'skills' => $skills]);
    }
}
