<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ResumeQATest extends TestCase
{
    use RefreshDatabase;

    public function test_resume_based_questions_after_import(): void
    {
        Storage::fake('local');

        $fixturePath = base_path('tests/Feature/Console/Fixtures/cv.json');

        $this->artisan('data:import', [
            'source' => $fixturePath,
            '--disk' => 'local',
            '--path' => 'imports/cv.json',
        ])->assertExitCode(0);

        Storage::disk('local')->assertExists('imports/cv.json');

        $saved = json_decode(Storage::disk('local')->get('imports/cv.json'), true, 512, JSON_THROW_ON_ERROR);

        // 1) ¿Tiene experiencia PHP?
        $hasPhp = $this->searchKeyword($saved, 'PHP');
        $this->assertTrue($hasPhp, 'Expected PHP experience in CV');

        // 2) ¿Conocimientos en IA?
        $hasAiCert = false;
        foreach (Arr::get($saved, 'certificates', []) as $c) {
            $name = $c['name'] ?? '';
            if (Str::contains(Str::lower($name), ['inteligencia', 'artificial', 'ai'])) {
                $hasAiCert = true;
                break;
            }
        }
        $this->assertTrue($hasAiCert, 'Expected AI-related certificate');

        // 3) ¿Sabe de Machine Learning?
        $knowsMl = $this->searchKeyword($saved, 'Machine Learning') || Str::contains(Str::lower(Arr::get($saved, 'basics.summary', '')), 'machine');
        $this->assertTrue($knowsMl, 'Expected Machine Learning mention');

        // 4) ¿Node.js presente? (esperamos que NO)
        $hasNode = $this->searchKeyword($saved, 'Node') || $this->searchKeyword($saved, 'Node.js');
        $this->assertFalse($hasNode, 'Node.js should not be present in fixture');

        // 5) ¿Qué DBMS conoce? (comprobamos presencia de varios motores comunes)
        $dbms = ['MySQL', 'MariaDB', 'PostgreSQL', 'Elastic', 'Neo4j'];
        foreach ($dbms as $d) {
            $this->assertTrue($this->searchKeyword($saved, $d), "Expected DBMS $d to appear in CV");
        }

        // 6) ¿Qué versiones de PHP ha manejado? (buscar menciones a PHP 5/7/8)
        $phpVersions = ['PHP 5', 'PHP 7', 'PHP 8'];
        foreach ($phpVersions as $v) {
            $this->assertTrue($this->searchKeyword($saved, $v) || $this->searchKeyword($saved, trim($v)), "Expected $v mentioned in CV");
        }
    }

    /**
     * Search keywords and text fields for a token (case-insensitive).
     */
    private function searchKeyword(array $data, string $token): bool
    {
        $needle = Str::lower($token);

        // Search basics.summary and label
        $summary = Str::lower(Arr::get($data, 'basics.summary', ''));
        if (Str::contains($summary, $needle)) {
            return true;
        }

        $label = Str::lower(Arr::get($data, 'basics.label', ''));
        if (Str::contains($label, $needle)) {
            return true;
        }

        // Search skills keywords
        foreach (Arr::get($data, 'skills', []) as $s) {
            foreach (Arr::get($s, 'keywords', []) as $k) {
                if (Str::contains(Str::lower($k), $needle)) {
                    return true;
                }
            }
        }

        // Search work highlights
        foreach (Arr::get($data, 'work', []) as $w) {
            if (Str::contains(Str::lower(implode(' ', Arr::wrap(Arr::get($w, 'highlights', [])))), $needle)) {
                return true;
            }
            if (Str::contains(Str::lower(Arr::get($w, 'summary', '')), $needle)) {
                return true;
            }
        }

        // Search certificates and education
        foreach (Arr::get($data, 'certificates', []) as $c) {
            if (Str::contains(Str::lower($c['name'] ?? ''), $needle) || Str::contains(Str::lower($c['issuer'] ?? ''), $needle)) {
                return true;
            }
        }

        foreach (Arr::get($data, 'education', []) as $e) {
            if (Str::contains(Str::lower(implode(' ', $e)), $needle)) {
                return true;
            }
        }

        return false;
    }
}
