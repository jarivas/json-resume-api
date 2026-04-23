<?php

namespace Tests\Feature;

use App\Ai\Agents\ResumeImportAgent;
use App\Services\Import\DocumentSkillImportService;
use Illuminate\Support\Str;
use Tests\TestCase;

class DocumentSkillImportServiceTest extends TestCase
{
    public function test_extract_certificate_and_skills_from_agent_response()
    {
        // Fake an agent response with certificates + skills JSON
        $payload = json_encode([
            'certificates' => [
                [
                    'name' => 'IFCD99 - Programación en Inteligencia Artificial y Big Data en entornos 5G',
                    'issuer' => 'Integra Conocimiento',
                    'date' => '2024-01-01',
                ],
            ],
            'skills' => [
                [
                    'name' => 'Web Development',
                    'level' => 'Master',
                    'keywords' => ['HTML', 'CSS', 'JavaScript'],
                ],
            ],
        ]);

        // Use the agent fake helper provided by the Laravel AI SDK
        ResumeImportAgent::fake([$payload]);

        // Create a tiny temporary file to simulate document text
        $tmp = sys_get_temp_dir().'/test_cert_'.Str::random(6).'.txt';
        file_put_contents($tmp, 'This is a test document content.');

        $service = new DocumentSkillImportService;

        $result = $service->extractCertificateAndSkills($tmp);

        // Clean up
        @unlink($tmp);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('certificate', $result);
        $this->assertArrayHasKey('skills', $result);

        $cert = $result['certificate'];
        $skills = $result['skills'];

        $this->assertNotNull($cert);
        $this->assertCount(1, $skills);
        $this->assertEquals('IFCD99 - Programación en Inteligencia Artificial y Big Data en entornos 5G', $cert->name);
        $this->assertEquals('Integra Conocimiento', $cert->issuer);
        $this->assertEquals('2024-01-01', $cert->date->format('Y-m-d'));

        $this->assertEquals('Web Development', $skills[0]['name']);
        $this->assertEquals('Master', $skills[0]['level']);
        $this->assertEquals(['HTML', 'CSS', 'JavaScript'], $skills[0]['keywords']);
    }
}
