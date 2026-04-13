<?php

namespace App\Http\Controllers\Import;

use App\Http\Requests\Import\ImportEducation as Request;
use App\Services\Import\DocumentSkillImportService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class EducationImportController
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

        $education = $service->createEducationFromDocument($fullPath, $data['url'] ?? null);
        if ($education === null) {
            Storage::delete($storagePath);

            return response()->json(['ok' => false, 'message' => 'No se pudo extraer metadata de la educación.'], 422);
        }

        try {
            $skills = $service->importForEducation($education, $fullPath);
            $resumeFragment = $service->resumeFragmentForEducation($education);
        } finally {
            Storage::delete($storagePath);
        }

        return response()->json(['ok' => true, 'skills' => $skills, 'resume' => $resumeFragment]);
    }
}
