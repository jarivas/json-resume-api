<?php

namespace App\Http\Controllers\Import;

use App\Http\Requests\Import\ImportCertificate as Request;
use App\Services\Import\DocumentSkillImportService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CertificateImportController
{
    use ImportFileStorageTrait;

    public function __invoke(Request $request, DocumentSkillImportService $service)
    {
        $data = $request->validated();
        $storagePath = null;
        $fullPath = null;

        if (! empty($data['url'])) {
            [$storagePath, $fullPath] = $this->storeRemoteUrl($data['url']);
            if ($storagePath === null) {
                return response()->json(['ok' => false, 'message' => 'No se pudo descargar la URL proporcionada.'], 422);
            }
        } else {
            /** @var UploadedFile $file */
            $file = $request->file('file');

            [$storagePath, $fullPath] = $this->storeUploadedFile($file);
        }

        try {
            $result = $service->extractCertificateAndSkills($fullPath, $data['url'] ?? null);

            if ($result === null) {
                return response()->json(['ok' => false, 'message' => 'No se pudo extraer metadata del certificado.'], 422);
            }

            $certificate = $result['certificate'];
            $skills = $result['skills'];
            $resumeFragment = $service->resumeFragmentForCertificate($certificate);
        } finally {
            Storage::delete($storagePath);
        }

        return response()->json(['ok' => true, 'skills' => $skills, 'resume' => $resumeFragment]);
    }
}
