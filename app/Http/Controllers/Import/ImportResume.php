<?php

namespace App\Http\Controllers\Import;

use App\Http\Requests\Import\ImportResume as Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;

class ImportResume
{
    public function __invoke(Request $request): JsonResponse
    {
        $uploadedFile = $request->file('file');

        if ($uploadedFile === null) {
            return response()->json([
                'ok' => false,
                'message' => 'Debes subir un archivo de CV para importar.',
            ], 422);
        }

        $exitCode = Artisan::call('data:import-resume', [
            'source' => $uploadedFile->getRealPath(),
        ]);
        $output = trim(Artisan::output());

        if ($exitCode !== 0) {
            return response()->json([
                'ok' => false,
                'message' => $output !== '' ? $output : 'No se pudo importar el documento de CV.',
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Importación de documento completada.',
            'output' => $output,
        ]);
    }
}
