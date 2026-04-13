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
     * Create a Certificate model from document content using the resume import agent.
     */
    public function createCertificateFromDocument(string $path, ?string $sourceUrl = null): ?Certificate
    {
        $agent = new ResumeImportAgent;
        $prompt = $this->buildPromptForResume($path);

        $raw = $agent->promptWithModelFallback($prompt);
        Log::debug('ResumeImportAgent raw response', ['len' => strlen((string) $raw), 'sample' => mb_substr((string) $raw, 0, 1000)]);

        $cleaned = $this->cleanAgentJson((string) $raw);
        Log::debug('ResumeImportAgent cleaned response', ['len' => strlen((string) $cleaned), 'sample' => mb_substr((string) $cleaned, 0, 1000)]);

        $decoded = json_decode($cleaned, true);
        Log::debug('ResumeImportAgent json_decode', ['error' => json_last_error_msg()]);

        // If the model returned non-JSON (or invalid JSON), retry with a
        // strict instruction asking for ONLY the expected JSON envelope.
        if (! is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('ResumeImportAgent produced invalid JSON, attempting strict retry');

            $strict = <<<'TXT'
Please output ONLY a single valid JSON object with a top-level "certs" array.
Do NOT include any explanation or text outside the JSON. Example:
{"certs":[{"name":"Certificate name","issuer":"Org","date":"2022-01-01"}]}
TXT;

            $retryPrompt = $strict."\n\nDocument content:\n".$prompt;

            $rawRetry = $agent->promptWithModelFallback($retryPrompt);
            Log::debug('ResumeImportAgent raw retry', ['len' => strlen((string) $rawRetry), 'sample' => mb_substr((string) $rawRetry, 0, 1000)]);

            $cleanedRetry = $this->cleanAgentJson((string) $rawRetry);
            Log::debug('ResumeImportAgent cleaned retry', ['len' => strlen((string) $cleanedRetry), 'sample' => mb_substr((string) $cleanedRetry, 0, 1000)]);

            $decodedRetry = json_decode($cleanedRetry, true);
            Log::debug('ResumeImportAgent json_decode retry', ['error' => json_last_error_msg()]);

            if (is_array($decodedRetry)) {
                $decoded = $decodedRetry;
            }
        }
        // If the model returned a valid JSON object but the certificates array
        // is present and empty, ask the model one more time with a strict
        // instruction focused on extracting certificates (and encourage skills
        // to be returned in the same object as well).
        $certsArray = $decoded['certs'] ?? $decoded['certificates'] ?? $decoded['certifications'] ?? null;

        if (is_array($decoded) && is_array($certsArray) && count($certsArray) === 0) {
            Log::warning('ResumeImportAgent returned empty certs/certificates; attempting focused retry');

            $strictCertPrompt = <<<'TXT'
Please output ONLY a single valid JSON object with top-level "certificates" and "skills" arrays (if present).
Extract any certificates or formal qualifications from the provided document text and add them to the "certificates" array.
Also include a "skills" array with structured skill objects when possible.
Each certificate must be an object with at least a "name" and, if available, "issuer" and "date" (YYYY-MM-DD).
If no certificates are found, return {"certificates":[]} exactly.
Do NOT include any explanation or text outside the JSON.
Example: {"certificates":[{"name":"Certificate name","issuer":"Org","date":"2022-01-01"}], "skills":[{"name":"Web Development","level":"Master","keywords":["HTML","CSS"]}]}
TXT;

            $retryPrompt = $strictCertPrompt."\n\nDocument content:\n".$prompt;

            $rawRetry = $agent->promptWithModelFallback($retryPrompt);
            Log::debug('ResumeImportAgent raw cert-focused retry', ['len' => strlen((string) $rawRetry), 'sample' => mb_substr((string) $rawRetry, 0, 1000)]);

            $cleanedRetry = $this->cleanAgentJson((string) $rawRetry);
            Log::debug('ResumeImportAgent cleaned cert-focused retry', ['len' => strlen((string) $cleanedRetry), 'sample' => mb_substr((string) $cleanedRetry, 0, 1000)]);

            $decodedRetry = json_decode($cleanedRetry, true);
            Log::debug('ResumeImportAgent json_decode cert-focused retry', ['error' => json_last_error_msg()]);

            if (is_array($decodedRetry)) {
                $decoded = $decodedRetry;
            }
        }
        // Cache the decoded resume import response so importForCertificate can reuse
        // the parsed skills without issuing another AI request.
        $this->lastResumeImportDecoded = is_array($decoded) ? $decoded : null;

        // Prefer any certificate-like array the model returned: 'certs',
        // 'certificates' or 'certifications'. If none present, fall back to
        // a small heuristic extractor.
        $certs = $decoded['certs'] ?? $decoded['certificates'] ?? $decoded['certifications'] ?? null;

        if (! is_array($decoded) || ! is_array($certs) || count($certs) === 0) {
            $heuristic = $this->parseCertificatesFromText($prompt);
            if (! empty($heuristic)) {
                // Keep using 'certs' key downstream for compatibility.
                $decoded['certs'] = $heuristic;
                $certs = $decoded['certs'];
            } else {
                return null;
            }
        }

        $item = $certs[0];

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
     * Extract a certificate and its skills from a document path.
     * Returns an array with keys: ['certificate' => Certificate, 'skills' => array]
     * or null if no certificate could be extracted.
     */
    public function extractCertificateAndSkills(string $path, ?string $sourceUrl = null): ?array
    {
        $agent = new ResumeImportAgent;
        $promptText = $this->buildPromptForResume($path);

        $strictHeader = <<<'TXT'
Please output ONLY a single valid JSON object with top-level "certificates" and "skills" arrays (if present).
The "certificates" array should contain objects with at least "name" and optionally "issuer", "date", "url".
The "skills" array should contain structured skill objects: {"name":"...","level":"...","keywords":[...]}
Do NOT include any explanation or text outside the JSON.
Example: {"certificates":[{"name":"Certificate name","issuer":"Org","date":"2022-01-01"}],"skills":[{"name":"Web Development","level":"Master","keywords":["HTML","CSS"]}]}
TXT;

        $fullPrompt = $strictHeader."\n\nDocument content:\n".$promptText;

        $raw = $agent->promptWithModelFallback($fullPrompt);
        Log::debug('ResumeImportAgent combined raw response', ['len' => strlen((string) $raw), 'sample' => mb_substr((string) $raw, 0, 1000)]);

        $cleaned = $this->cleanAgentJson((string) $raw);
        Log::debug('ResumeImportAgent combined cleaned response', ['len' => strlen((string) $cleaned), 'sample' => mb_substr((string) $cleaned, 0, 1000)]);

        $decoded = json_decode($cleaned, true);
        Log::debug('ResumeImportAgent combined json_decode', ['error' => json_last_error_msg()]);

        if (! is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            // Fallback: attempt one strict retry asking explicitly for the exact envelope
            Log::warning('ResumeImportAgent produced invalid JSON for combined output, attempting strict retry');

            $retryPrompt = $strictHeader."\n\nDocument content:\n".$promptText;
            $rawRetry = $agent->promptWithModelFallback($retryPrompt);
            $cleanedRetry = $this->cleanAgentJson((string) $rawRetry);
            $decodedRetry = json_decode($cleanedRetry, true);
            Log::debug('ResumeImportAgent combined retry json_decode', ['error' => json_last_error_msg()]);

            if (is_array($decodedRetry)) {
                $decoded = $decodedRetry;
            }
        }

        // Cache full decoded response for potential reuse by other methods
        $this->lastResumeImportDecoded = is_array($decoded) ? $decoded : null;

        // Prefer 'certificates' but accept other keys
        $certs = $decoded['certificates'] ?? $decoded['certs'] ?? $decoded['certifications'] ?? null;

        if (! is_array($certs) || count($certs) === 0) {
            $heuristic = $this->parseCertificatesFromText($promptText);
            if (! empty($heuristic)) {
                $certs = $heuristic;
            } else {
                return null;
            }
        }

        $item = $certs[0];
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
     * Very small heuristic extractor for certificate-like lines from a
     * document text when the AI fails to return certs. Returns an array of
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
     * Return a JSON Resume fragment for an Education model.
     */
    public function resumeFragmentForEducation(Education $education): array
    {
        $entry = [
            'institution' => $education->institution,
        ];

        if (! empty($education->area)) {
            $entry['area'] = $education->area;
        }

        if (! empty($education->studyType)) {
            $entry['studyType'] = $education->studyType;
        }

        if (! empty($education->startDate)) {
            $entry['startDate'] = (string) $education->startDate;
        }

        if (! empty($education->endDate)) {
            $entry['endDate'] = (string) $education->endDate;
        }

        if (! empty($education->url)) {
            $entry['url'] = $education->url;
        }

        return ['education' => [$entry]];
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
