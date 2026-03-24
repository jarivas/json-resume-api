<?php

namespace App\Http\Controllers\Import;

use App\Http\Requests\Import\ImportJson as Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;

class ImportJson
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validated();
        $uploadedFile = $request->file('file');

        if ($uploadedFile === null) {
            return response()->json([
                'ok' => false,
                'message' => 'Debes subir un archivo JSON para importar.',
            ], 422);
        }

        $exitCode = Artisan::call('data:import', [
            'source' => $uploadedFile->getRealPath(),
            '--disk' => $data['disk'] ?? 'local',
            '--path' => $data['path'] ?? 'imports/imported-data.json',
        ]);

        $output = trim(Artisan::output());

        if ($exitCode !== 0) {
            return response()->json([
                'ok' => false,
                'message' => $output !== '' ? $output : 'No se pudo importar el JSON Resume.',
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Importación completada.',
            'output' => $output,
        ]);
    }
}
