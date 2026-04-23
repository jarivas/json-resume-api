<?php

namespace App\Services\Import;

use App\Ai\Agents\ResumeImportAgent;
use App\Ai\Agents\SkillExtractionAgent;
use App\Models\Certificate;
use App\Models\Education;
use App\Models\Skill;
use App\Services\ResumeImport\DocumentTextExtractor;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DocumentSkillImportService
{
    /**
     * Last decoded response from ResumeImportAgent to avoid duplicate AI calls.
     *
     * @var array<string,mixed>|null
     */
    protected ?array $lastResumeImportDecoded = null;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function importForEducation(Education $education, string $path): array
    {
        // If no meaningful text can be extracted from the document, skip AI calls.
        try {
            $extracted = (new DocumentTextExtractor)->extract($path);
        } catch (\Throwable) {
            $extracted = '';
        }

        if (trim((string) $extracted) === '') {
            return [];
        }

        $agent = new SkillExtractionAgent;
        $prompt = $this->buildPromptWithExtractedText($path);

        $raw = $agent->promptWithModelFallback($prompt);

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
        // If the resume import agent already returned structured skills, reuse them
        // to avoid making a second AI request.
        if (isset($this->lastResumeImportDecoded['skills']) && is_array($this->lastResumeImportDecoded['skills'])) {
            $rawSkills = json_encode($this->lastResumeImportDecoded['skills']);
            $skills = $this->normalizeAgentResponse((string) $rawSkills);
        } else {
            // If no meaningful text can be extracted from the document, skip AI calls.
            try {
                $extracted = (new DocumentTextExtractor)->extract($path);
            } catch (\Throwable) {
                $extracted = '';
            }

            if (trim((string) $extracted) === '') {
                return [];
            }

            $agent = new SkillExtractionAgent;
            $prompt = $this->buildPromptWithExtractedText($path);

            $raw = $agent->promptWithModelFallback($prompt);

            $skills = $this->normalizeAgentResponse($raw);
        }

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

    protected function buildPromptWithExtractedText(string $path): string
    {
        $base = $this->buildPrompt();

        try {
            $extracted = (new DocumentTextExtractor)->extract($path);
        } catch (\Throwable) {
            $extracted = '';
        }

        if ($extracted !== '') {
            return $base."\n\nDocument content:\n".mb_substr($extracted, 0, 20000);
        }

        return $base;
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

    protected function buildPromptForResume(string $path): string
    {
        try {
            $extracted = (new DocumentTextExtractor)->extract($path);
        } catch (\Throwable) {
            $extracted = '';
        }

        return $extracted ?: '';
    }

    /**
     * Extract a certificate and its skills from a document path.
     * Returns an array with keys: ['certificate' => Certificate, 'skills' => array]
     * or null if no certificate could be extracted.
     */
    public function extractCertificateAndSkills(string $path, ?string $sourceUrl = null): ?array
    {
        $agent = new ResumeImportAgent;
        $promptText = $this->buildPromptForResume($path);

        $strictHeader = <<<'TXT'
Please output ONLY a single valid JSON object with top-level "certificates" and "skills" arrays (always include both keys; use empty arrays if none found).

CERTIFICATES:
- Each certificate object must contain at least `name` (string). Optionally include `issuer`, `date` (YYYY-MM-DD) and `url`.

SKILLS (VERY IMPORTANT - follow these rules exactly):
- Return an array of skill objects. Each skill object MUST include:
    - `name`: concise and technical (tool, framework, technology, algorithm, methodology, or domain competency). Avoid job titles or occupational categories.
    - `level`: one of: "beginner", "intermediate", "advanced", "expert". Default to "intermediate" if unsure.
    - `keywords`: array of short strings with concrete technologies, libraries, protocols, or keywords mentioned in the document.
- Return at most 12 skills, focusing on explicit technical skills and tools mentioned in the document. Do NOT include soft skills (e.g., "communication", "teamwork"), job titles, occupation codes, or generic role names (e.g., "Analistas").
- If the document lists modules or course names, extract the technical topics inside them (e.g., from "Módulo: Seguridad en redes 5G" extract skill `Network Security` with keywords `["5G","firewall","TLS"]`).
- Normalize names (e.g., "JavaScript", "Python", "5G").

FORMAT:
- Output valid JSON only, nothing else.
- Use the exact field names `certificates` and `skills`.

EXAMPLES (input → desired skill object):
- "HTML, CSS, JavaScript" → {"name":"Web Development","level":"intermediate","keywords":["HTML","CSS","JavaScript"]}
- "Redes 5G, seguridad" → {"name":"5G Network Security","level":"advanced","keywords":["5G","network security","encryption"]}
- "Django, Flask" → {"name":"Python Web Frameworks","level":"intermediate","keywords":["Django","Flask"]}

Example final JSON:
{"certificates":[{"name":"IFCD99 - Programación en Inteligencia Artificial y Big Data en entornos 5G","issuer":"Integra Conocimiento","date":"2024-01-01"}],"skills":[{"name":"Web Development","level":"advanced","keywords":["HTML","CSS","JavaScript"]}]}
TXT;

        $fullPrompt = $strictHeader."\n\nDocument content:\n".$promptText;

        $raw = $agent->promptWithModelFallback($fullPrompt);

        Log::debug('ResumeImportAgent raw response for combined certificate+skills', ['raw' => (string) $raw]);

        $cleaned = $this->cleanAgentJson((string) $raw);

        $decoded = json_decode($cleaned, true);

        if (! is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            // Fallback: attempt one strict retry asking explicitly for the exact envelope
            Log::warning('ResumeImportAgent produced invalid JSON for combined output, attempting strict retry');

            $retryPrompt = $strictHeader."\n\nDocument content:\n".$promptText;
            $rawRetry = $agent->promptWithModelFallback($retryPrompt);
            $cleanedRetry = $this->cleanAgentJson((string) $rawRetry);
            $decodedRetry = json_decode($cleanedRetry, true);
            Log::debug('ResumeImportAgent combined retry json_decode', ['error' => json_last_error_msg(), 'raw' => (string) $rawRetry]);

            if (is_array($decodedRetry)) {
                $decoded = $decodedRetry;
            }
        }

        // Cache full decoded response for potential reuse by other methods
        $this->lastResumeImportDecoded = is_array($decoded) ? $decoded : null;

        // Prefer 'certificates' but accept other keys
        $certificates = $decoded['certificates'] ?? $decoded['certificates'] ?? $decoded['certifications'] ?? null;

        if (! is_array($certificates) || count($certificates) === 0) {
            $heuristic = $this->parseCertificatesFromText($promptText);
            if (! empty($heuristic)) {
                $certificates = $heuristic;
            } else {
                return null;
            }
        }

        $item = $certificates[0];
        $certificate = $this->createCertificateFromArray($item, $item['url'] ?? $sourceUrl ?? null);

        // Import skills returned by the model (if any). Accept 'skills' as
        // an array of objects or strings.
        $skillsData = $decoded['skills'] ?? null;
        $skills = [];
        if (is_array($skillsData) && count($skillsData) > 0) {
            $skills = $this->importSkillsArrayForCertificate($certificate, $skillsData);
        } else {
            // If model didn't return structured skills, fall back to skill extraction
            // agent to avoid losing skill extraction entirely.
            $skills = $this->importForCertificate($certificate, $path);
        }

        return ['certificate' => $certificate, 'skills' => $skills];
    }

    /**
     * Create a Certificate model from an already-parsed array (e.g. when
     * the client sends a JSON resume fragment instead of a document).
     */
    public function createCertificateFromArray(array $item, ?string $sourceUrl = null): Certificate
    {
        $dateRaw = $item['date'] ?? null;
        $date = $this->normalizeDate($dateRaw) ?: now()->toDateString();

        $url = $item['url'] ?? $sourceUrl ?? '';

        return Certificate::create([
            'name' => $item['name'] ?? 'Imported certificate',
            'issuer' => $item['issuer'] ?? '',
            'date' => $date,
            'url' => $url,
        ]);
    }

    /**
     * Import an array of structured skills (from JSON) and attach them to
     * the provided Certificate model. Returns the normalized skills array.
     *
     * @param  array<int,array<string,mixed>>  $skillsData
     * @return array<int,array<string,mixed>>
     */
    public function importSkillsArrayForCertificate(Certificate $certificate, array $skillsData): array
    {
        $out = [];

        foreach ($skillsData as $s) {
            if (! is_array($s) && ! is_string($s)) {
                continue;
            }

            $name = '';
            $level = '';
            $keywords = [];

            if (is_string($s)) {
                $name = trim($s);
            } else {
                $name = trim((string) ($s['name'] ?? ''));
                $level = isset($s['level']) ? (string) $s['level'] : '';
                $keywords = isset($s['keywords']) && is_array($s['keywords']) ? $s['keywords'] : [];
            }

            if ($name === '') {
                continue;
            }

            $skill = Skill::firstOrCreate([
                'name' => Str::lower($name),
            ], [
                'level' => $level,
                'keywords' => $keywords,
            ]);

            $certificate->skills()->syncWithoutDetaching($skill->id);

            $out[] = [
                'name' => $name,
                'level' => $level ?: $skill->level ?? '',
                'keywords' => $keywords ?: ($skill->keywords ?? []),
            ];
        }

        return $out;
    }

    /**
     * Import an array of structured skills (from JSON) and attach them to
     * the provided Education model. Returns the normalized skills array.
     *
     * @param  array<int,array<string,mixed>>  $skillsData
     * @return array<int,array<string,mixed>>
     */
    public function importSkillsArrayForEducation(Education $education, array $skillsData): array
    {
        $out = [];

        foreach ($skillsData as $s) {
            if (! is_array($s) && ! is_string($s)) {
                continue;
            }

            $name = '';
            $level = '';
            $keywords = [];

            if (is_string($s)) {
                $name = trim($s);
            } else {
                $name = trim((string) ($s['name'] ?? ''));
                $level = isset($s['level']) ? (string) $s['level'] : '';
                $keywords = isset($s['keywords']) && is_array($s['keywords']) ? $s['keywords'] : [];
            }

            if ($name === '') {
                continue;
            }

            $skill = Skill::firstOrCreate([
                'name' => Str::lower($name),
            ], [
                'level' => $level,
                'keywords' => $keywords,
            ]);

            $education->skills()->syncWithoutDetaching($skill->id);

            $out[] = [
                'name' => $name,
                'level' => $level ?: $skill->level ?? '',
                'keywords' => $keywords ?: ($skill->keywords ?? []),
            ];
        }

        return $out;
    }

    /**
     * Create an Education model from an already-parsed array (e.g. when
     * the client sends a JSON resume fragment instead of a document).
     */
    public function createEducationFromArray(array $item, ?string $sourceUrl = null): Education
    {
        $startRaw = $item['startDate'] ?? null;
        $endRaw = $item['endDate'] ?? null;
        // Provide sensible defaults for required datetime columns to avoid
        // NOT NULL constraint violations when agent doesn't return dates.
        $startDate = $this->normalizeDate($startRaw) ?: now()->toDateString();
        $endDate = $this->normalizeDate($endRaw) ?: now()->toDateString();

        $url = $item['url'] ?? $sourceUrl ?? '';

        return Education::create([
            'institution' => $item['institution'] ?? 'Imported education',
            'url' => $url,
            'area' => $item['area'] ?? '',
            'studyType' => $item['studyType'] ?? '',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'score' => $item['score'] ?? '',
            'summary' => $item['summary'] ?? '',
            'courses' => $item['courses'] ?? [],
        ]);
    }

    /**
     * Extract an education and its skills from a document path.
     * Returns an array with keys: ['education' => Education, 'skills' => array]
     * or null if no education could be extracted.
     */
    public function extractEducationAndSkills(string $path, ?string $sourceUrl = null): ?array
    {
        $agent = new ResumeImportAgent;
        $promptText = $this->buildPromptForResume($path);

        $strictHeader = <<<'TXT'
Please output ONLY a single valid JSON object with top-level "educations" and "skills" arrays (always include both keys; use empty arrays if none found).

EDUCATIONS:
- Each education object may contain: `institution`, `area`, `studyType`, `startDate` (YYYY-MM-DD), `endDate` (YYYY-MM-DD), `score`, `summary`, `courses` (array), and `url`.

SKILLS:
- Return an array of skill objects. Each skill object MUST include `name` and may include `level` and `keywords` (array).

FORMAT:
- Output valid JSON only, nothing else.

Example: {"educations":[{"institution":"University X","area":"Computer Science","studyType":"Bachelor","startDate":"2018-09-01","endDate":"2022-06-01","courses":["Algorithms"]}],"skills":[{"name":"Algorithms","level":"advanced","keywords":["sorting","graphs"]}]}
TXT;

        $fullPrompt = $strictHeader."\n\nDocument content:\n".$promptText;

        $raw = $agent->promptWithModelFallback($fullPrompt);

        $cleaned = $this->cleanAgentJson((string) $raw);

        $decoded = json_decode($cleaned, true);

        if (! is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('ResumeImportAgent produced invalid JSON for education combined output, attempting strict retry');

            $retryPrompt = $strictHeader."\n\nDocument content:\n".$promptText;
            $rawRetry = $agent->promptWithModelFallback($retryPrompt);
            $cleanedRetry = $this->cleanAgentJson((string) $rawRetry);
            $decodedRetry = json_decode($cleanedRetry, true);
            Log::debug('ResumeImportAgent education combined retry json_decode', ['error' => json_last_error_msg()]);

            if (is_array($decodedRetry)) {
                $decoded = $decodedRetry;
            }
        }

        $this->lastResumeImportDecoded = is_array($decoded) ? $decoded : null;

        $educations = $decoded['educations'] ?? $decoded['education'] ?? null;

        if (! is_array($educations) || count($educations) === 0) {
            return null;
        }

        $item = $educations[0];
        $education = $this->createEducationFromArray($item, $item['url'] ?? $sourceUrl ?? null);

        $skillsData = $decoded['skills'] ?? null;
        $skills = [];
        if (is_array($skillsData) && count($skillsData) > 0) {
            $skills = $this->importSkillsArrayForEducation($education, $skillsData);
        } else {
            $skills = $this->importForEducation($education, $path);
        }

        return ['education' => $education, 'skills' => $skills];
    }

    /**
     * Very small heuristic extractor for certificate-like lines from a
     * document text when the AI fails to return certificates. Returns an array of
     * cert objects with at least a 'name' key.
     *
     * @return array<int,array<string,string>>
     */
    protected function parseCertificatesFromText(string $text): array
    {
        $out = [];
        // Normalize whitespace for easier matching
        $norm = preg_replace('/\s+/', ' ', trim($text));

        // 1) Denominación de la especialidad
        if (preg_match('/Denominaci[oó]n de la especialidad:\s*(.+?)(?:Familia|\.|$)/i', $norm, $m)) {
            $name = trim($m[1]);
            if ($name !== '') {
                $out[] = ['name' => $name, 'issuer' => $this->findIssuerNear($norm, $m[1]), 'date' => $this->findDateNear($norm, $m[1])];
            }
        }

        // 2) Module lines like 'Módulo 1 Seguridad y tecnología 5G 35 horas'
        if (preg_match_all('/M[óo]dulo\s*\d+\s+([^\d]+?)\s+\d+\s+horas/iu', $norm, $matches)) {
            foreach ($matches[1] as $m) {
                $name = trim($m);
                if ($name !== '') {
                    $out[] = ['name' => $name, 'issuer' => $this->findIssuerNear($norm, $m), 'date' => $this->findDateNear($norm, $m)];
                }
            }
        }

        // 2b) Simpler pattern: lines starting with 'Certificado:' which some
        // documents include verbatim and are easier to capture reliably.
        if (preg_match_all('/Certificado:\s*(.+?)(?:\.|,|;|$)/i', $norm, $simpleCerts)) {
            foreach ($simpleCerts[1] as $m) {
                $name = trim($m);
                if ($name !== '') {
                    $out[] = ['name' => $name, 'issuer' => $this->findIssuerNear($norm, $m), 'date' => $this->findDateNear($norm, $m)];
                }
            }
        }

        // 3) Generic 'Certificado(s):' lines
        if (preg_match_all('/Certificat(?:e|o)s?:?\s*(.+?)(?:\.|,|;|$)/iu', $norm, $matches2)) {
            foreach ($matches2[1] as $m) {
                $name = trim($m);
                if ($name !== '') {
                    $out[] = ['name' => $name, 'issuer' => $this->findIssuerNear($norm, $m), 'date' => $this->findDateNear($norm, $m)];
                }
            }
        }

        // Deduplicate by lowercased name
        $seen = [];
        $filtered = [];
        foreach ($out as $item) {
            $k = mb_strtolower($item['name']);
            if (! isset($seen[$k])) {
                $seen[$k] = true;
                $filtered[] = $item;
            }
        }

        return $filtered;
    }

    protected function findIssuerNear(string $text, string $anchor): string
    {
        // Look for common issuer keywords near the anchor; search within 200 chars after anchor
        $pos = mb_stripos($text, $anchor);
        if ($pos === false) {
            return '';
        }

        $snippet = mb_substr($text, $pos, 300);

        if (preg_match('/(Instituto|Asociaci[oó]n|Registro|Ministerio|Universidad|Servicio|Centro|Agencia)[^\.,;\n]{0,60}/i', $snippet, $m)) {
            return trim($m[0]);
        }

        // fallback: look for uppercase-ish organization patterns in following text
        if (preg_match('/([A-Z][A-Za-z\s0-9\-\(\)\.,]{5,60})/u', $snippet, $m2)) {
            return trim($m2[1]);
        }

        return '';
    }

    protected function findDateNear(string $text, string $anchor): ?string
    {
        $pos = mb_stripos($text, $anchor);
        if ($pos === false) {
            $search = $text;
        } else {
            $search = mb_substr($text, $pos, 300);
        }

        // YYYY-MM-DD
        if (preg_match('/(\d{4}[-\/]\d{1,2}[-\/]\d{1,2})/', $search, $m)) {
            return $this->normalizeDate($m[1]);
        }

        // Month name + year (e.g., Septiembre 2021)
        if (preg_match('/(enero|febrero|marzo|abril|mayo|junio|julio|agosto|septiembre|octubre|noviembre|diciembre)\s+(\d{4})/i', $search, $m2)) {
            $monthNames = [
                'enero' => '01', 'febrero' => '02', 'marzo' => '03', 'abril' => '04', 'mayo' => '05', 'junio' => '06',
                'julio' => '07', 'agosto' => '08', 'septiembre' => '09', 'octubre' => '10', 'noviembre' => '11', 'diciembre' => '12',
            ];
            $mon = strtolower($m2[1]);

            return $m2[2].'-'.($monthNames[$mon] ?? '01').'-01';
        }

        // Year only
        if (preg_match('/(\d{4})/', $search, $m3)) {
            return $this->normalizeDate($m3[1]);
        }

        return null;
    }

    /**
     * Normalize a date string from the agent into YYYY-MM-DD format.
     * Accepts YYYY, YYYY-MM, or YYYY-MM-DD; returns null if input invalid.
     */
    protected function normalizeDate(?string $raw): ?string
    {
        if (empty($raw)) {
            return null;
        }

        $raw = trim($raw);

        // Year only
        if (preg_match('/^\d{4}$/', $raw)) {
            return $raw.'-01-01';
        }

        // Year-month
        if (preg_match('/^(\d{4})[-\/](\d{1,2})$/', $raw, $m)) {
            $month = str_pad($m[2], 2, '0', STR_PAD_LEFT);

            return $m[1].'-'.$month.'-01';
        }

        // Full date
        if (preg_match('/^(\d{4})[-\/](\d{1,2})[-\/](\d{1,2})/', $raw, $m)) {
            $month = str_pad($m[2], 2, '0', STR_PAD_LEFT);
            $day = str_pad($m[3], 2, '0', STR_PAD_LEFT);

            return $m[1].'-'.$month.'-'.$day;
        }

        return null;
    }

    /**
     * Strip common wrappers and whitespace from AI responses so json_decode()
     * can parse them even when the model included fences or trailing text.
     */
    protected function cleanAgentJson(string $raw): string
    {
        $cleaned = preg_replace('/^```(?:json)?\s*/i', '', trim($raw));
        $cleaned = preg_replace('/\s*```$/i', '', trim((string) $cleaned));

        // Some models add explanatory text before/after the JSON. Try to extract
        // the first JSON object/array found in the string.
        if (preg_match('/({.*}|\[.*\])/s', $cleaned, $m)) {
            return $m[0];
        }

        return trim($cleaned);
    }
}
