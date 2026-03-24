<?php

namespace App\Http\Controllers\Import;

use App\Http\Requests\Import\ImportResume as Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;

class ImportResume
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validated();
        $uploadedFile = $request->file('file');

        if ($uploadedFile === null) {
            return response()->json([
                'ok' => false,
                'message' => 'Debes subir un archivo de CV para importar.',
            ], 422);
        }

        $parameters = [
            'source' => $uploadedFile->getRealPath(),
            '--disk' => $data['disk'] ?? 'local',
            '--path' => $data['path'] ?? 'imports/extracted-data.json',
        ];

        if (($data['keep_json'] ?? false) === true) {
            $parameters['--keep-json'] = true;
        }

        $exitCode = Artisan::call('data:import-resume', $parameters);
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
