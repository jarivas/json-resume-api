<?php

namespace Tests\Feature\Import;

use App\Ai\Agents\ResumeImportAgent;
use App\Ai\Agents\SkillExtractionAgent;
use App\Models\Certificate;
use App\Models\Education;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Ai\Files;
use Tests\TestCase;

class ImportFixturesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Map fixture filename => expected skills (array of strings)
     * Exact phrases provided by the user; tests will assert DB contains lowercase name.
     *
     * @return array<string, array<int,string>>
     */
    protected function fixturesExpectedSkills(): array
    {
        return [
            'certificate -IFCD97.pdf' => [
                'C++ Development: Proficiency in C++ programming language for IoT and Smart City projects.',
                'Object-Oriented Programming (OOP): Application of OOP principles and file management in C++.',
                'Embedded Systems & Hardware: Hands-on experience with Arduino IDE, microcontrollers (boards, shields, sensors), and electronic component integration.',
                'IoT Infrastructure: Understanding IoT pillars, connecting physical objects to the internet, and transitioning systems to IoT environments.',
                '5G Technology & Security: Knowledge of 5G network architecture, its vertical applications, and implementing security across the software development lifecycle (design, coding, testing, and deployment).',
                'Artificial Intelligence (AI) & Machine Learning: Understanding AI models, neural networks, expert systems, and machine learning algorithms (manual and automated).',
                'Natural Language Processing (NLP): Programming AI algorithms for text-to-speech, speech-to-text, and chatbots.',
            ],
            'certificate -CERTIFICADO_CONSTRUCCIÓN_DE_PÁGINAS_WEB_950_6-Certificado_cualificados_744.pdf' => [
                'Markup Languages Proficiency',
                'Semantic Web Development',
                'Web Content Architecture',
                'Multimedia Integration',
                'Interactive Form Design',
                'SEO & Organic Positioning',
                'CMS Management',
                'Web Accessibility Standards',
            ],
            'certificate - IFCD99.pdf' => [
                'desarrollo web',
                'programación',
                'php',
                'javascript',
            ],
            'certificate - INF0487.pdf' => [
                'IT Security Auditing',
                'Vulnerability Analysis',
                'Secure Programming',
                'Network Firewalls',
                'Audit Reporting',
            ],
            'certificate - Python flask django .pdf' => [
                'Advanced Python Programming',
                'Django Framework Expertise',
                'Flask Micro-framework',
                'Advanced Database Management',
            ],
            'education - desarrollo-de-aplicaciones-informaticas-ingles.pdf' => [
                'Software Development Lifecycle (SDLC)',
                'Structured Programming Languages',
                'System Analysis & Design',
                'Network & Multi-user Systems',
                'Database Management',
                'Graphic Interface Design (GUI)',
            ],
        ];
    }

    public function test_import_fixtures_extract_expected_skills()
    {
        Storage::fake('local');
        Files::fake();

        $user = User::factory()->create();

        $fixturesDir = base_path('tests/Feature/Import/Fixtures');
        $map = $this->fixturesExpectedSkills();

        foreach ($map as $filename => $expectedSkills) {
            $path = $fixturesDir.DIRECTORY_SEPARATOR.$filename;

            $this->assertFileExists($path, "Fixture $filename must exist");

            $content = File::get($path);

            $isCertificate = Str::startsWith($filename, 'certificate');

            if ($isCertificate) {
                $endpoint = '/api/import/certificate';
            } else {
                $endpoint = '/api/import/education';
            }

            // Use a fake agent response for determinism in tests.
            $fakeResponse = [];

            foreach ($expectedSkills as $s) {
                $fakeResponse[] = ['name' => $s, 'level' => 'intermediate', 'keywords' => []];
            }

            SkillExtractionAgent::fake([json_encode($fakeResponse)]);

            // Use fixture content to derive metadata (no ResumeImportAgent fake)

            $uploaded = UploadedFile::fake()->createWithContent($filename, $content);

            $res = $this->actingAs($user)->postJson($endpoint, [
                'file' => $uploaded,
            ]);

            $res->assertOk();
            $res->assertJsonPath('ok', true);

            // If we faked, assert each expected skill exists in DB and the pivot
            foreach ($expectedSkills as $skillName) {
                $this->assertDatabaseHas('skills', [
                    'name' => Str::lower($skillName),
                ]);
            }

            $pivotTable = $isCertificate ? 'certificate_skill' : 'education_skill';
            if ($isCertificate) {
                $created = Certificate::orderBy('created_at', 'desc')->first();
                $this->assertNotNull($created);
                $this->assertDatabaseHas($pivotTable, ['certificate_id' => $created->id]);
            } else {
                $created = Education::orderBy('created_at', 'desc')->first();
                $this->assertNotNull($created);
                $this->assertDatabaseHas($pivotTable, ['education_id' => $created->id]);
            }
        }
    }

    public function test_certificate_urls_file_processes_each_url()
    {
        Storage::fake('local');

        Files::fake();

        $user = User::factory()->create();

        $urlsFile = base_path('tests/Feature/Import/Fixtures/certificate - urls.txt');
        $this->assertFileExists($urlsFile);

        $urls = array_filter(array_map('trim', explode("\n", File::get($urlsFile))));
        // Ensure URLs are valid for the validator (encode spaces)
        $urls = array_map(fn ($u) => str_replace(' ', '%20', $u), $urls);

        foreach ($urls as $u) {
            $res = $this->actingAs($user)->postJson('/api/import/certificate', [
                'url' => $u,
            ]);

            $res->assertOk();
            $res->assertJsonPath('ok', true);
        }

        $this->assertDatabaseHas('skills', ['name' => Str::lower('Laravel')]);
        $created = Certificate::orderBy('created_at', 'desc')->first();
        $this->assertNotNull($created);
        $this->assertDatabaseHas('certificate_skill', ['certificate_id' => $created->id]);
    }

    public function test_markdown_fenced_agent_response_is_handled_gracefully()
    {
        Storage::fake('local');
        Files::fake();

        $user = User::factory()->create();

        // Simulate an LLM that wraps its JSON output in markdown code fences
        $markdownFencedResponse = "```json\n[{\"name\":\"Docker Containerization\",\"level\":\"intermediate\",\"keywords\":[\"Docker Compose\",\"Kubernetes\"]}]\n```";
        SkillExtractionAgent::fake([$markdownFencedResponse]);

        // Use a real certificate fixture to exercise the extractor
        $fixture = base_path('tests/Feature/Import/Fixtures/certificate - IFCD99.pdf');
        $this->assertFileExists($fixture);

        $uploaded = new UploadedFile($fixture, basename($fixture), null, null, true);

        $res = $this->actingAs($user)->postJson('/api/import/certificate', [
            'file' => $uploaded,
        ]);

        $res->assertOk();
        $res->assertJsonPath('ok', true);

        $this->assertDatabaseHas('skills', ['name' => 'docker containerization']);
        $created = Certificate::orderBy('created_at', 'desc')->first();
        $this->assertNotNull($created);
        $this->assertDatabaseHas('certificate_skill', ['certificate_id' => $created->id]);
    }
}
