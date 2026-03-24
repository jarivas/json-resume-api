<?php

namespace App\Console\Commands;

use App\Models\Award;
use App\Models\Basic;
use App\Models\Certificate;
use App\Models\Education;
use App\Models\Interest;
use App\Models\Language;
use App\Models\Project;
use App\Models\Publication;
use App\Models\Reference;
use App\Models\Skill;
use App\Models\Volunteer;
use App\Models\Work;
use App\Observers\ResumeModelObserver;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use RuntimeException;
use Swaggest\JsonSchema\InvalidValue;
use Swaggest\JsonSchema\Schema as JsonSchema;
use Swaggest\JsonSchema\SchemaContract;
use Throwable;

class ImportJsonData extends Command
{
    public const string JSON_RESUME_SCHEMA_URL = 'https://raw.githubusercontent.com/jsonresume/resume-schema/master/schema.json';

    public const string JSON_RESUME_SCHEMA_CACHE_KEY = 'json_resume_schema_definition';

    protected ?SchemaContract $jsonResumeSchema = null;

    protected $signature = 'data:import
        {source : URL o ruta local del archivo JSON}
        {--disk=local : Disco de almacenamiento}
        {--path=imports/imported-data.json : Ruta destino dentro del disco}';

    protected $description = 'Importa datos JSON desde una URL o archivo local y los guarda en storage';

    public function handle(): int
    {
        $source = (string) $this->argument('source');
        $disk = (string) $this->option('disk');
        $path = (string) $this->option('path');
        // Reset local resume embedding state before importing the payload.
        $this->resetResumeState();

        try {
            $content = $this->resolveContent($source);
            $payload = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
            $this->validateResumePayload($payload);

            Storage::disk($disk)->put(
                $path,
                json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)
            );

            $this->persistResume($payload);

            $this->info("Importación completada en {$disk}:{$path}");

            return self::SUCCESS;
        } catch (\JsonException $exception) {
            $this->error('JSON inválido: '.$exception->getMessage());

            return self::FAILURE;
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * Reset local embedding state before importing a resume.
     */
    protected function resetResumeState(): void
    {
        $this->clearResumeCaches();
        $this->truncateResumeTables();
    }

    /**
     * Truncate resume-related tables to ensure a clean import state.
     */
    protected function truncateResumeTables(): void
    {
        $tables = [
            'resume_embeddings',
            'resume_keywords',
            'projects',
            'references',
            'interests',
            'languages',
            'skills',
            'publications',
            'certificates',
            'awards',
            'educations',
            'volunteers',
            'works',
            'basics',
        ];

        Schema::disableForeignKeyConstraints();
        foreach ($tables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                DB::table($table)->truncate();
            }
        }
        Schema::enableForeignKeyConstraints();
    }

    protected function clearResumeCaches(): void
    {
        Cache::forget('resume_keywords_generic');
        Cache::forget('resume_keywords_verbs');
    }

    protected function resolveContent(string $source): string
    {
        if ($this->isUrl($source)) {
            $response = Http::timeout(30)->acceptJson()->get($source);

            if ($response->failed()) {
                throw new RuntimeException("No se pudo descargar el recurso: {$source}");
            }

            return (string) $response->body();
        }

        $localPath = File::exists($source) ? $source : base_path($source);

        if (! File::exists($localPath)) {
            throw new RuntimeException("No existe el archivo local: {$source}");
        }

        return (string) File::get($localPath);
    }

    protected function isUrl(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    protected function validateResumePayload(array $payload): void
    {
        $validator = Validator::make($payload, [
            '$schema' => ['sometimes', 'string', 'url', 'in:'.self::JSON_RESUME_SCHEMA_URL],
        ]);

        if ($validator->fails()) {
            throw new RuntimeException('El JSON no cumple el formato JSON Resume: '.$validator->errors()->first());
        }

        $payloadObject = json_decode(json_encode($payload, JSON_THROW_ON_ERROR), false, 512, JSON_THROW_ON_ERROR);

        try {
            $this->jsonResumeSchema()->in($payloadObject);
        } catch (InvalidValue $exception) {
            throw new RuntimeException('El JSON no cumple el schema oficial de JSON Resume: '.$exception->getMessage());
        } catch (Throwable $exception) {
            throw new RuntimeException('No se pudo validar el JSON Resume con JSON Schema: '.$exception->getMessage());
        }
    }

    protected function jsonResumeSchema(): SchemaContract
    {
        if ($this->jsonResumeSchema instanceof SchemaContract) {
            return $this->jsonResumeSchema;
        }

        $schemaDefinition = Cache::rememberForever(self::JSON_RESUME_SCHEMA_CACHE_KEY, function (): object {
            $response = Http::timeout(30)->acceptJson()->get(self::JSON_RESUME_SCHEMA_URL);

            if ($response->failed()) {
                throw new RuntimeException('No se pudo descargar el schema oficial de JSON Resume.');
            }

            $schema = json_decode((string) $response->body(), false, 512, JSON_THROW_ON_ERROR);

            if (! is_object($schema)) {
                throw new RuntimeException('El schema oficial de JSON Resume no tiene un formato JSON válido.');
            }

            return $schema;
        });

        try {
            $this->jsonResumeSchema = JsonSchema::import($schemaDefinition);
        } catch (Throwable $exception) {
            throw new RuntimeException('No se pudo cargar el schema de JSON Resume: '.$exception->getMessage());
        }

        return $this->jsonResumeSchema;
    }

    protected function persistResume(array $payload): void
    {
        DB::transaction(function () use ($payload) {
            $observer = new ResumeModelObserver;

            $this->persistBasicInfo($payload, $observer);
            $this->persistWorkExperience($payload, $observer);
            $this->persistVolunteer($payload, $observer);
            $this->persistEducation($payload, $observer);
            $this->persistAwards($payload, $observer);
            $this->persistCertificates($payload, $observer);
            $this->persistPublications($payload, $observer);
            $this->persistSkills($payload, $observer);
            $this->persistLanguages($payload, $observer);
            $this->persistInterests($payload, $observer);
            $this->persistReferences($payload, $observer);
            $this->persistProjects($payload, $observer);
        });
    }

    protected function persistBasicInfo(array $payload, ResumeModelObserver $observer): void
    {
        $basic = $this->createBasic($payload);
        if ($basic) {
            $observer->saved($basic);
        }
    }

    protected function persistWorkExperience(array $payload, ResumeModelObserver $observer): void
    {
        foreach (Arr::get($payload, 'work', []) as $item) {
            $model = Work::create([
                'name' => (string) Arr::get($item, 'name', ''),
                'position' => (string) Arr::get($item, 'position', ''),
                'url' => Arr::get($item, 'url'),
                'startDate' => $this->normalizeIsoDate(Arr::get($item, 'startDate')),
                'endDate' => $this->normalizeIsoDate(Arr::get($item, 'endDate', Arr::get($item, 'startDate'))),
                'summary' => (string) Arr::get($item, 'summary', ''),
                'highlights' => Arr::get($item, 'highlights', []),
            ]);
            $observer->saved($model);
        }
    }

    protected function persistVolunteer(array $payload, ResumeModelObserver $observer): void
    {
        foreach (Arr::get($payload, 'volunteer', []) as $item) {
            $model = Volunteer::create([
                'organization' => (string) Arr::get($item, 'organization', ''),
                'position' => (string) Arr::get($item, 'position', ''),
                'url' => Arr::get($item, 'url'),
                'startDate' => $this->normalizeIsoDate(Arr::get($item, 'startDate')),
                'endDate' => $this->normalizeIsoDate(Arr::get($item, 'endDate', Arr::get($item, 'startDate'))),
                'summary' => (string) Arr::get($item, 'summary', ''),
                'highlights' => Arr::get($item, 'highlights', []),
            ]);
            $observer->saved($model);
        }
    }

    protected function persistEducation(array $payload, ResumeModelObserver $observer): void
    {
        foreach (Arr::get($payload, 'education', []) as $item) {
            $model = Education::create([
                'institution' => (string) Arr::get($item, 'institution', ''),
                'url' => Arr::get($item, 'url'),
                'area' => (string) Arr::get($item, 'area', ''),
                'studyType' => (string) Arr::get($item, 'studyType', ''),
                'startDate' => $this->normalizeIsoDate(Arr::get($item, 'startDate')),
                'endDate' => $this->normalizeIsoDate(Arr::get($item, 'endDate', Arr::get($item, 'startDate'))),
                'score' => Arr::get($item, 'score'),
                'summary' => (string) Arr::get($item, 'summary', ''),
                'courses' => Arr::get($item, 'courses', []),
            ]);
            $observer->saved($model);
        }
    }

    protected function persistAwards(array $payload, ResumeModelObserver $observer): void
    {
        foreach (Arr::get($payload, 'awards', []) as $item) {
            $model = Award::create([
                'title' => (string) Arr::get($item, 'title', ''),
                'date' => $this->normalizeIsoDate(Arr::get($item, 'date')),
                'awarder' => (string) Arr::get($item, 'awarder', ''),
                'summary' => (string) Arr::get($item, 'summary', ''),
            ]);
            $observer->saved($model);
        }
    }

    protected function persistCertificates(array $payload, ResumeModelObserver $observer): void
    {
        foreach (Arr::get($payload, 'certificates', []) as $item) {
            $model = Certificate::create([
                'name' => (string) Arr::get($item, 'name', ''),
                'date' => $this->normalizeIsoDate(Arr::get($item, 'date')),
                'issuer' => (string) Arr::get($item, 'issuer', ''),
                'url' => (string) Arr::get($item, 'url', ''),
            ]);
            $observer->saved($model);
        }
    }

    protected function persistPublications(array $payload, ResumeModelObserver $observer): void
    {
        foreach (Arr::get($payload, 'publications', []) as $item) {
            $model = Publication::create([
                'name' => (string) Arr::get($item, 'name', ''),
                'publisher' => (string) Arr::get($item, 'publisher', ''),
                'releaseDate' => $this->normalizeIsoDate(Arr::get($item, 'releaseDate')),
                'url' => Arr::get($item, 'url'),
                'summary' => (string) Arr::get($item, 'summary', ''),
            ]);
            $observer->saved($model);
        }
    }

    protected function persistSkills(array $payload, ResumeModelObserver $observer): void
    {
        foreach (Arr::get($payload, 'skills', []) as $item) {
            $model = Skill::create([
                'name' => (string) Arr::get($item, 'name', ''),
                'level' => (string) Arr::get($item, 'level', ''),
                'keywords' => Arr::get($item, 'keywords', []),
            ]);
            $observer->saved($model);
        }
    }

    protected function persistLanguages(array $payload, ResumeModelObserver $observer): void
    {
        foreach (Arr::get($payload, 'languages', []) as $item) {
            $model = Language::create([
                'language' => (string) Arr::get($item, 'language', ''),
                'fluency' => (string) Arr::get($item, 'fluency', ''),
            ]);
            $observer->saved($model);
        }
    }

    protected function persistInterests(array $payload, ResumeModelObserver $observer): void
    {
        foreach (Arr::get($payload, 'interests', []) as $item) {
            $model = Interest::create([
                'name' => (string) Arr::get($item, 'name', ''),
                'keywords' => Arr::get($item, 'keywords', []),
            ]);
            $observer->saved($model);
        }
    }

    protected function persistReferences(array $payload, ResumeModelObserver $observer): void
    {
        foreach (Arr::get($payload, 'references', []) as $item) {
            $model = Reference::create([
                'name' => (string) Arr::get($item, 'name', ''),
                'reference' => (string) Arr::get($item, 'reference', ''),
            ]);
            $observer->saved($model);
        }
    }

    protected function persistProjects(array $payload, ResumeModelObserver $observer): void
    {
        foreach (Arr::get($payload, 'projects', []) as $item) {
            $model = Project::create([
                'name' => (string) Arr::get($item, 'name', ''),
                'startDate' => $this->normalizeIsoDate(Arr::get($item, 'startDate')),
                'endDate' => $this->normalizeIsoDate(Arr::get($item, 'endDate', Arr::get($item, 'startDate'))),
                'description' => (string) Arr::get($item, 'description', ''),
                'highlights' => Arr::get($item, 'highlights', []),
                'url' => Arr::get($item, 'url'),
            ]);
            $observer->saved($model);
        }
    }

    protected function createBasic(array $payload): ?Basic
    {
        $basics = Arr::get($payload, 'basics');

        if (! is_array($basics)) {
            return null;
        }

        return Basic::create([
            'name' => (string) Arr::get($basics, 'name', ''),
            'label' => (string) Arr::get($basics, 'label', ''),
            'email' => (string) Arr::get($basics, 'email', 'unknown@example.com'),
            'phone' => (string) Arr::get($basics, 'phone', ''),
            'url' => Arr::get($basics, 'url'),
            'summary' => Arr::get($basics, 'summary'),
            'location' => Arr::get($basics, 'location', []),
            'profiles' => Arr::get($basics, 'profiles', []),
        ]);
    }

    protected function normalizeIsoDate(mixed $value): string
    {
        if (! is_string($value) || $value === '') {
            return now()->format('Y-m-d');
        }

        if (preg_match('/^[1-2][0-9]{3}$/', $value) === 1) {
            return $value.'-01-01';
        }

        if (preg_match('/^[1-2][0-9]{3}-[0-1][0-9]$/', $value) === 1) {
            return $value.'-01';
        }

        return $value;
    }
}
