<?php

namespace App\Console\Commands;

use App\Services\ResumeImport\ResumeDocumentImportService;
use Illuminate\Console\Command;
use RuntimeException;

class ImportResumeDocumentCommand extends Command
{
    protected $signature = 'data:import-resume
        {source : Ruta local o URL del documento (pdf, doc, docx, google docs)}
        {--disk=local : Disco donde se escribe el JSON generado}
        {--path=imports/extracted-data.json : Ruta destino del JSON generado}
        {--keep-json : Conserva el JSON generado tras completar data:import}';

    protected $description = 'Importa PDF, Word o Google Docs público y lo transforma a JSON Resume para data:import';

    public function __construct(protected ResumeDocumentImportService $importService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $result = $this->importService->import(
                source: (string) $this->argument('source'),
                disk: (string) $this->option('disk'),
                path: (string) $this->option('path'),
                keepJson: (bool) $this->option('keep-json'),
            );

            if ($result['import_output'] !== '') {
                $this->line($result['import_output']);
            }

            if ($result['json_kept']) {
                $this->info('Proceso completado. JSON conservado en '.$result['disk'].':'.$result['path']);
            } else {
                $this->info('Proceso completado. JSON temporal eliminado tras ejecutar data:import.');
            }

            return self::SUCCESS;
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}
