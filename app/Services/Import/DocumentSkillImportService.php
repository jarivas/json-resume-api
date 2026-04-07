<?php

namespace App\Services\Import;

use App\Ai\Agents\SkillExtractionAgent;
use App\Models\Certificate;
use App\Models\Education;
use App\Models\Skill;
use Illuminate\Support\Str;
use Laravel\Ai\Files;
use Laravel\Ai\Files\Document as AiDocument;
use Laravel\Ai\Responses\StoredFileResponse;

class DocumentSkillImportService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function importForEducation(Education $education, string $path): array
    {
        if (Files::isFaked()) {
            $document = new StoredFileResponse(Files::fakeId(basename($path)));
        } else {
            $document = AiDocument::fromPath($path)->put();
        }

        $agent = new SkillExtractionAgent;
        $prompt = $this->buildPrompt();

        $raw = $agent->promptWithModelFallback($prompt, [$document->id]);

        $skills = $this->normalizeAgentResponse($raw);

        foreach ($skills as $s) {
            $name = trim((string) ($s['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $skill = Skill::firstOrCreate([
                'name' => Str::lower($name),
            ], [
                'level' => $s['level'] ?? '',
                'keywords' => $s['keywords'] ?? [],
            ]);

            $education->skills()->syncWithoutDetaching($skill->id);
        }

        return $skills;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function importForCertificate(Certificate $certificate, string $path): array
    {
        if (Files::isFaked()) {
            $document = new StoredFileResponse(Files::fakeId(basename($path)));
        } else {
            $document = AiDocument::fromPath($path)->put();
        }

        $agent = new SkillExtractionAgent;
        $prompt = $this->buildPrompt();

        $raw = $agent->promptWithModelFallback($prompt, [$document->id]);

        $skills = $this->normalizeAgentResponse($raw);

        foreach ($skills as $s) {
            $name = trim((string) ($s['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $skill = Skill::firstOrCreate([
                'name' => Str::lower($name),
            ], [
                'level' => $s['level'] ?? '',
                'keywords' => $s['keywords'] ?? [],
            ]);

            $certificate->skills()->syncWithoutDetaching($skill->id);
        }

        return $skills;
    }

    protected function buildPrompt(): string
    {
        return <<<'TXT'
You are analyzing a professional education or certification document to enrich a candidate's CV/resume profile.

Extract all relevant technical skills, domain-specific competencies, technologies, tools, and frameworks from the attached document.
Return ONLY a JSON array — no markdown, no code fences, no explanatory text.

Example output:
[
  {
    "name": "C++ Development: Proficiency in C++ programming language for embedded systems and IoT projects",
    "level": "intermediate",
    "keywords": ["Arduino", "microcontrollers", "sensors"]
  },
  {
    "name": "Python Web Frameworks: Django and Flask for building web applications and REST APIs",
    "keywords": ["Django ORM", "Flask blueprints", "REST"]
  }
]

Rules:
- Output valid JSON only — no markdown fences, no code blocks, no surrounding text.
- Each skill object must have: "name" (string, descriptive and specific).
- Optional fields: "level" (one of: "beginner", "intermediate", "advanced", "expert"), "keywords" (array of related tools, subtopics, or technologies).
- Skill names must be descriptive and self-contained — include context so the skill is meaningful without reading the document.
- Do NOT include vague soft skills (e.g. "communication", "teamwork", "leadership", "time management").
- Do NOT include duplicates.
- Normalize casing consistently (e.g. "Python", not "python" or "PYTHON").
TXT;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function normalizeAgentResponse(string $raw): array
    {
        // Strip markdown fences that LLMs sometimes add despite being told not to
        $cleaned = preg_replace('/^```(?:json)?\s*/i', '', trim($raw));
        $cleaned = preg_replace('/\s*```$/i', '', trim((string) $cleaned));

        $decoded = json_decode(trim((string) $cleaned), true);

        // Unwrap {"skills": [...]} envelope if present
        if (is_array($decoded) && isset($decoded['skills']) && is_array($decoded['skills'])) {
            $decoded = $decoded['skills'];
        }

        if (! is_array($decoded)) {
            return [];
        }

        $out = [];

        foreach ($decoded as $item) {
            if (! is_array($item)) {
                continue;
            }

            $out[] = [
                'name' => $item['name'] ?? null,
                'level' => isset($item['level']) ? (string) $item['level'] : null,
                'keywords' => isset($item['keywords']) && is_array($item['keywords']) ? $item['keywords'] : [],
            ];
        }

        return $out;
    }
}
