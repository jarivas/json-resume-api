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
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class ImportJsonData extends Command
{
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
        $iso8601Pattern = '/^([1-2][0-9]{3}-[0-1][0-9]-[0-3][0-9]|[1-2][0-9]{3}-[0-1][0-9]|[1-2][0-9]{3})$/';

        $validator = Validator::make($payload, [
            '$schema' => ['sometimes', 'string', 'url'],
            'basics' => ['sometimes', 'array'],
            'basics.name' => ['sometimes', 'string'],
            'basics.label' => ['sometimes', 'string'],
            'basics.email' => ['sometimes', 'email'],
            'basics.phone' => ['sometimes', 'string'],
            'basics.url' => ['sometimes', 'url'],
            'basics.summary' => ['sometimes', 'string'],
            'basics.location' => ['sometimes', 'array'],
            'basics.location.address' => ['sometimes', 'string'],
            'basics.location.postalCode' => ['sometimes', 'string'],
            'basics.location.city' => ['sometimes', 'string'],
            'basics.location.countryCode' => ['sometimes', 'string'],
            'basics.location.region' => ['sometimes', 'string'],
            'basics.profiles' => ['sometimes', 'array'],
            'basics.profiles.*.network' => ['sometimes', 'string'],
            'basics.profiles.*.username' => ['sometimes', 'string'],
            'basics.profiles.*.url' => ['sometimes', 'url'],
            'work' => ['sometimes', 'array'],
            'work.*.name' => ['sometimes', 'string'],
            'work.*.position' => ['sometimes', 'string'],
            'work.*.url' => ['sometimes', 'url'],
            'work.*.startDate' => ['sometimes', 'regex:'.$iso8601Pattern],
            'work.*.endDate' => ['sometimes', 'regex:'.$iso8601Pattern],
            'work.*.summary' => ['sometimes', 'string'],
            'work.*.highlights' => ['sometimes', 'array'],
            'work.*.highlights.*' => ['sometimes', 'string'],
            'volunteer' => ['sometimes', 'array'],
            'volunteer.*.organization' => ['sometimes', 'string'],
            'volunteer.*.position' => ['sometimes', 'string'],
            'volunteer.*.url' => ['sometimes', 'url'],
            'volunteer.*.startDate' => ['sometimes', 'regex:'.$iso8601Pattern],
            'volunteer.*.endDate' => ['sometimes', 'regex:'.$iso8601Pattern],
            'volunteer.*.summary' => ['sometimes', 'string'],
            'volunteer.*.highlights' => ['sometimes', 'array'],
            'volunteer.*.highlights.*' => ['sometimes', 'string'],
            'education' => ['sometimes', 'array'],
            'education.*.institution' => ['sometimes', 'string'],
            'education.*.url' => ['sometimes', 'url'],
            'education.*.area' => ['sometimes', 'string'],
            'education.*.studyType' => ['sometimes', 'string'],
            'education.*.startDate' => ['sometimes', 'regex:'.$iso8601Pattern],
            'education.*.endDate' => ['sometimes', 'regex:'.$iso8601Pattern],
            'education.*.score' => ['sometimes', 'string'],
            'education.*.summary' => ['sometimes', 'string'],
            'education.*.courses' => ['sometimes', 'array'],
            'education.*.courses.*' => ['sometimes', 'string'],
            'awards' => ['sometimes', 'array'],
            'awards.*.title' => ['sometimes', 'string'],
            'awards.*.date' => ['sometimes', 'regex:'.$iso8601Pattern],
            'awards.*.awarder' => ['sometimes', 'string'],
            'awards.*.summary' => ['sometimes', 'string'],
            'certificates' => ['sometimes', 'array'],
            'certificates.*.name' => ['sometimes', 'string'],
            'certificates.*.date' => ['sometimes', 'regex:'.$iso8601Pattern],
            'certificates.*.issuer' => ['sometimes', 'string'],
            'certificates.*.url' => ['sometimes', 'url'],
            'publications' => ['sometimes', 'array'],
            'publications.*.name' => ['sometimes', 'string'],
            'publications.*.publisher' => ['sometimes', 'string'],
            'publications.*.releaseDate' => ['sometimes', 'regex:'.$iso8601Pattern],
            'publications.*.url' => ['sometimes', 'url'],
            'publications.*.summary' => ['sometimes', 'string'],
            'skills' => ['sometimes', 'array'],
            'skills.*.name' => ['sometimes', 'string'],
            'skills.*.level' => ['sometimes', 'string'],
            'skills.*.keywords' => ['sometimes', 'array'],
            'skills.*.keywords.*' => ['sometimes', 'string'],
            'languages' => ['sometimes', 'array'],
            'languages.*.language' => ['sometimes', 'string'],
            'languages.*.fluency' => ['sometimes', 'string'],
            'interests' => ['sometimes', 'array'],
            'interests.*.name' => ['sometimes', 'string'],
            'interests.*.keywords' => ['sometimes', 'array'],
            'interests.*.keywords.*' => ['sometimes', 'string'],
            'references' => ['sometimes', 'array'],
            'references.*.name' => ['sometimes', 'string'],
            'references.*.reference' => ['sometimes', 'string'],
            'projects' => ['sometimes', 'array'],
            'projects.*.name' => ['sometimes', 'string'],
            'projects.*.description' => ['sometimes', 'string'],
            'projects.*.highlights' => ['sometimes', 'array'],
            'projects.*.highlights.*' => ['sometimes', 'string'],
            'projects.*.startDate' => ['sometimes', 'regex:'.$iso8601Pattern],
            'projects.*.endDate' => ['sometimes', 'regex:'.$iso8601Pattern],
            'projects.*.url' => ['sometimes', 'url'],
        ]);

        if ($validator->fails()) {
            throw new RuntimeException('El JSON no cumple el formato JSON Resume: '.$validator->errors()->first());
        }
    }

    protected function persistResume(array $payload): void
    {
        DB::transaction(function () use ($payload) {
            $basic = $this->createBasic($payload);

            foreach (Arr::get($payload, 'work', []) as $item) {
                Work::create([
                    'name' => (string) Arr::get($item, 'name', ''),
                    'position' => (string) Arr::get($item, 'position', ''),
                    'url' => Arr::get($item, 'url'),
                    'startDate' => $this->normalizeIsoDate(Arr::get($item, 'startDate')),
                    'endDate' => $this->normalizeIsoDate(Arr::get($item, 'endDate', Arr::get($item, 'startDate'))),
                    'summary' => (string) Arr::get($item, 'summary', ''),
                    'highlights' => Arr::get($item, 'highlights', []),
                ]);
            }

            foreach (Arr::get($payload, 'volunteer', []) as $item) {
                Volunteer::create([
                    'organization' => (string) Arr::get($item, 'organization', ''),
                    'position' => (string) Arr::get($item, 'position', ''),
                    'url' => Arr::get($item, 'url'),
                    'startDate' => $this->normalizeIsoDate(Arr::get($item, 'startDate')),
                    'endDate' => $this->normalizeIsoDate(Arr::get($item, 'endDate', Arr::get($item, 'startDate'))),
                    'summary' => (string) Arr::get($item, 'summary', ''),
                    'highlights' => Arr::get($item, 'highlights', []),
                ]);
            }

            foreach (Arr::get($payload, 'education', []) as $item) {
                Education::create([
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
            }

            foreach (Arr::get($payload, 'awards', []) as $item) {
                Award::create([
                    'title' => (string) Arr::get($item, 'title', ''),
                    'date' => $this->normalizeIsoDate(Arr::get($item, 'date')),
                    'awarder' => (string) Arr::get($item, 'awarder', ''),
                    'summary' => (string) Arr::get($item, 'summary', ''),
                ]);
            }

            foreach (Arr::get($payload, 'certificates', []) as $item) {
                Certificate::create([
                    'name' => (string) Arr::get($item, 'name', ''),
                    'date' => $this->normalizeIsoDate(Arr::get($item, 'date')),
                    'issuer' => (string) Arr::get($item, 'issuer', ''),
                    'url' => (string) Arr::get($item, 'url', ''),
                ]);
            }

            foreach (Arr::get($payload, 'publications', []) as $item) {
                Publication::create([
                    'name' => (string) Arr::get($item, 'name', ''),
                    'publisher' => (string) Arr::get($item, 'publisher', ''),
                    'releaseDate' => $this->normalizeIsoDate(Arr::get($item, 'releaseDate')),
                    'url' => Arr::get($item, 'url'),
                    'summary' => (string) Arr::get($item, 'summary', ''),
                ]);
            }

            foreach (Arr::get($payload, 'skills', []) as $item) {
                Skill::create([
                    'name' => (string) Arr::get($item, 'name', ''),
                    'level' => (string) Arr::get($item, 'level', ''),
                    'keywords' => Arr::get($item, 'keywords', []),
                ]);
            }

            foreach (Arr::get($payload, 'languages', []) as $item) {
                Language::create([
                    'language' => (string) Arr::get($item, 'language', ''),
                    'fluency' => (string) Arr::get($item, 'fluency', ''),
                ]);
            }

            foreach (Arr::get($payload, 'interests', []) as $item) {
                Interest::create([
                    'name' => (string) Arr::get($item, 'name', ''),
                    'keywords' => Arr::get($item, 'keywords', []),
                ]);
            }

            foreach (Arr::get($payload, 'references', []) as $item) {
                Reference::create([
                    'name' => (string) Arr::get($item, 'name', ''),
                    'reference' => (string) Arr::get($item, 'reference', ''),
                ]);
            }

            foreach (Arr::get($payload, 'projects', []) as $item) {
                Project::create([
                    'name' => (string) Arr::get($item, 'name', ''),
                    'startDate' => $this->normalizeIsoDate(Arr::get($item, 'startDate')),
                    'endDate' => $this->normalizeIsoDate(Arr::get($item, 'endDate', Arr::get($item, 'startDate'))),
                    'description' => (string) Arr::get($item, 'description', ''),
                    'highlights' => Arr::get($item, 'highlights', []),
                    'url' => Arr::get($item, 'url'),
                ]);
            }
        });
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
