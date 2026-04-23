<?php

namespace App\Services\ResumeImport;

/**
 * Maps the simple intermediate extraction format produced by the AI
 * into a valid JSON Resume schema object.
 *
 * Intermediate format keys (what the AI outputs):
 *   name, headline, email, phone, website, summary, city, country_code,
 *   profiles[]{network, url, username},
 *   jobs[]{company, role, start, end, current, description, highlights[], tech[]},
 *   schools[]{school|institution, degree|studyType, field|area, start, end, current},
 *   certificates|certifications[]{name, issuer, date},
 *   languages[]{language, level|fluency},
 *   skills[]   (flat list of technology strings)
 *
 * Keys marked with | mean the model may output either variant; both are handled.
 */
class IntermediateToJsonResumeNormalizer
{
    /**
     * Convert the intermediate array into a JSON Resume-compliant array.
     *
     * @param  array<mixed>  $data
     * @return array<mixed>
     */
    public function normalize(array $data): array
    {
        // If the AI already returned a JSON Resume-like structure, accept
        // it and perform light normalization (accept alternate field names
        // and normalize languages/skills formats). Otherwise convert the
        // intermediate extraction format into JSON Resume.
        if ($this->looksLikeJsonResume($data)) {
            return $this->normalizeJsonResumeInput($data);
        }

        $resume = [];

        $basics = $this->buildBasics($data);
        if ($basics !== []) {
            $resume['basics'] = $basics;
        }

        $work = $this->buildWork($data);
        if ($work !== []) {
            $resume['work'] = $work;
        }

        $education = $this->buildEducation($data);
        if ($education !== []) {
            $resume['education'] = $education;
        }

        $certificates = $this->buildCertificates($data);
        if ($certificates !== []) {
            $resume['certificates'] = $certificates;
        }

        // Extract skills referenced by certificates and merge into top-level skills
        $certificateSkills = $this->extractSkillsFromCertificates($certificates);

        $skills = $this->buildSkills($data);

        // Merge certificate-derived skills into the top-level skills array,
        // avoiding duplicates.
        foreach ($certificateSkills as $certSkill) {
            if (! $this->skillExists($skills, $certSkill)) {
                $skills[] = ['name' => $certSkill];
            }
        }

        if ($skills !== []) {
            $resume['skills'] = $skills;
        }

        $languages = $this->buildLanguages($data);
        if ($languages !== []) {
            $resume['languages'] = $languages;
        }

        return $resume;
    }

    protected function looksLikeJsonResume(array $data): bool
    {
        return isset($data['basics']) || isset($data['work']) || isset($data['education']) || isset($data['$schema']);
    }

    /**
     * Perform light normalization on input that already follows JSON Resume
     * (or a close variant). This accepts alternate names (e.g. 'level' →
     * 'fluency' in languages) and converts flat skill arrays to objects.
     *
     * @param  array<mixed>  $data
     * @return array<mixed>
     */
    protected function normalizeJsonResumeInput(array $data): array
    {
        $out = $data;

        // Languages: accept 'level' as alias for 'fluency'.
        if (isset($out['languages']) && is_array($out['languages'])) {
            foreach ($out['languages'] as $i => $lang) {
                if (! is_array($lang)) {
                    continue;
                }

                if (isset($lang['level']) && ! isset($lang['fluency'])) {
                    $out['languages'][$i]['fluency'] = $lang['level'];
                    unset($out['languages'][$i]['level']);
                }
            }
        }

        // Education: accept alternate keys from older intermediate format.
        if (isset($out['education']) && is_array($out['education'])) {
            foreach ($out['education'] as $i => $edu) {
                if (! is_array($edu)) {
                    continue;
                }

                if (isset($edu['school']) && ! isset($edu['institution'])) {
                    $out['education'][$i]['institution'] = $edu['school'];
                    unset($out['education'][$i]['school']);
                }

                if (isset($edu['degree']) && ! isset($out['education'][$i]['studyType'])) {
                    $out['education'][$i]['studyType'] = $edu['degree'];
                }

                if (isset($edu['field']) && ! isset($out['education'][$i]['area'])) {
                    $out['education'][$i]['area'] = $edu['field'];
                }

                if (isset($edu['start']) && ! isset($out['education'][$i]['startDate'])) {
                    $out['education'][$i]['startDate'] = $edu['start'];
                    unset($out['education'][$i]['start']);
                }

                if (isset($edu['end']) && ! isset($out['education'][$i]['endDate'])) {
                    $out['education'][$i]['endDate'] = $edu['end'];
                    unset($out['education'][$i]['end']);
                }
            }
        }

        // Work entries: accept alternate keys and normalize dates/role/company names.
        if (isset($out['work']) && is_array($out['work'])) {
            foreach ($out['work'] as $i => $job) {
                if (! is_array($job)) {
                    continue;
                }

                if (isset($job['company']) && ! isset($out['work'][$i]['name'])) {
                    $out['work'][$i]['name'] = $job['company'];
                    unset($out['work'][$i]['company']);
                }

                if (isset($job['role']) && ! isset($out['work'][$i]['position'])) {
                    $out['work'][$i]['position'] = $job['role'];
                    unset($out['work'][$i]['role']);
                }

                if (isset($job['start']) && ! isset($out['work'][$i]['startDate'])) {
                    $out['work'][$i]['startDate'] = $job['start'];
                    unset($out['work'][$i]['start']);
                }

                if (isset($job['end']) && ! isset($out['work'][$i]['endDate'])) {
                    $out['work'][$i]['endDate'] = $job['end'];
                    unset($out['work'][$i]['end']);
                }
            }
        }

        // Skills: if provided as flat string array convert to objects with name.
        if (isset($out['skills']) && is_array($out['skills'])) {
            $first = reset($out['skills']);
            if (is_string($first)) {
                $out['skills'] = array_values(array_map(function ($s) {
                    return ['name' => trim((string) $s)];
                }, $out['skills']));
            }
        }

        return $out;
    }

    /**
     * @param  array<mixed>  $data
     * @return array<mixed>
     */
    protected function buildBasics(array $data): array
    {
        $basics = [];

        $this->setIfPresent($basics, 'name', $data, 'name');
        $this->setIfPresent($basics, 'label', $data, 'headline');
        $this->setIfPresent($basics, 'email', $data, 'email');
        $this->setIfPresent($basics, 'phone', $data, 'phone');
        $this->setIfPresent($basics, 'url', $data, 'website');
        $this->setIfPresent($basics, 'summary', $data, 'summary');

        $location = $this->buildLocation($data);
        if ($location !== []) {
            $basics['location'] = $location;
        }

        $profiles = $this->buildProfiles($data);
        if ($profiles !== []) {
            $basics['profiles'] = $profiles;
        }

        return $basics;
    }

    /**
     * @param  array<mixed>  $data
     * @return array<mixed>
     */
    protected function buildLocation(array $data): array
    {
        $location = [];

        $this->setIfPresent($location, 'city', $data, 'city');
        $this->setIfPresent($location, 'countryCode', $data, 'country_code');

        return $location;
    }

    /**
     * @param  array<mixed>  $data
     * @return array<mixed>
     */
    protected function buildProfiles(array $data): array
    {
        $profiles = [];

        foreach ($this->arrayOf($data, 'profiles') as $profile) {
            if (! is_array($profile)) {
                continue;
            }

            $item = [];
            $this->setIfPresent($item, 'network', $profile, 'network');
            $this->setIfPresent($item, 'username', $profile, 'username');
            $this->setIfPresent($item, 'url', $profile, 'url');

            if ($item !== []) {
                $profiles[] = $item;
            }
        }

        return $profiles;
    }

    /**
     * @param  array<mixed>  $data
     * @return array<mixed>
     */
    protected function buildWork(array $data): array
    {
        $work = [];

        foreach ($this->arrayOf($data, 'jobs') as $job) {
            if (! is_array($job)) {
                continue;
            }

            $item = [];
            $this->setIfPresent($item, 'name', $job, 'company');
            $this->setIfPresent($item, 'position', $job, 'role');
            $this->setIfPresent($item, 'url', $job, 'url');
            $this->setIfPresent($item, 'description', $job, 'description');
            $this->setIfPresent($item, 'summary', $job, 'summary');

            $start = $this->stringOf($job, 'start');
            if ($start !== '') {
                $item['startDate'] = $start;
            }

            $isCurrent = ($job['current'] ?? false) === true
                || strtolower((string) ($job['end'] ?? '')) === 'current'
                || strtolower((string) ($job['end'] ?? '')) === 'present';

            if (! $isCurrent) {
                $end = $this->stringOf($job, 'end');
                if ($end !== '' && $this->isValidIso8601($end)) {
                    $item['endDate'] = $end;
                }
            }

            $highlights = $this->stringListOf($job, 'highlights');
            if ($highlights !== []) {
                $item['highlights'] = $highlights;
            }

            if ($item !== []) {
                $work[] = $item;
            }
        }

        return $work;
    }

    /**
     * @param  array<mixed>  $data
     * @return array<mixed>
     */
    protected function buildEducation(array $data): array
    {
        $education = [];

        // Accept both 'schools' (intermediate format) and 'education' (model alias)
        $entries = array_merge($this->arrayOf($data, 'schools'), $this->arrayOf($data, 'education'));

        foreach ($entries as $school) {
            if (! is_array($school)) {
                continue;
            }

            $item = [];
            // Accept 'school' or 'institution' for the institution name
            $institutionValue = $this->stringOf($school, 'school') ?: $this->stringOf($school, 'institution');
            if ($institutionValue !== '') {
                $item['institution'] = $institutionValue;
            }
            // Accept 'degree' or 'studyType'
            $degreeValue = $this->stringOf($school, 'degree') ?: $this->stringOf($school, 'studyType');
            if ($degreeValue !== '') {
                $item['studyType'] = $degreeValue;
            }
            // Accept 'field' or 'area'
            $fieldValue = $this->stringOf($school, 'field') ?: $this->stringOf($school, 'area');
            if ($fieldValue !== '') {
                $item['area'] = $fieldValue;
            }
            $this->setIfPresent($item, 'url', $school, 'url');

            // Accept 'start' (intermediate format) or 'start_date' (model alias)
            $start = $this->stringOf($school, 'start') ?: $this->stringOf($school, 'start_date');
            if ($start !== '' && $this->isValidIso8601($start)) {
                $item['startDate'] = $start;
            }

            $endRaw = $this->stringOf($school, 'end') ?: $this->stringOf($school, 'end_date');
            $isCurrent = ($school['current'] ?? false) === true
                || strtolower($endRaw) === 'current'
                || strtolower($endRaw) === 'present'
                || $endRaw === '';

            if (! $isCurrent && $this->isValidIso8601($endRaw)) {
                $item['endDate'] = $endRaw;
            }

            if ($item !== []) {
                $education[] = $item;
            }
        }

        return $education;
    }

    /**
     * @param  array<mixed>  $data
     * @return array<mixed>
     */
    protected function buildCertificates(array $data): array
    {
        $certificates = [];

        // Accept 'certificates' (intermediate format) and 'certifications' / 'certificates' (model aliases)
        $entries = array_merge(
            $this->arrayOf($data, 'certificates'),
            $this->arrayOf($data, 'certifications'),
            $this->arrayOf($data, 'certificates'),
        );

        foreach ($entries as $cert) {
            if (! is_array($cert)) {
                continue;
            }

            $item = [];
            $this->setIfPresent($item, 'name', $cert, 'name');
            $this->setIfPresent($item, 'issuer', $cert, 'issuer');
            $this->setIfPresent($item, 'url', $cert, 'url');
            $this->setIfPresent($item, 'date', $cert, 'date');

            if ($item !== []) {
                $certificates[] = $item;
            }
        }

        return $certificates;
    }

    /**
     * Consolidate per-job tech arrays and the top-level skills list into
     * JSON Resume skills entries.
     *
     * @param  array<mixed>  $data
     * @return array<mixed>
     */
    protected function buildSkills(array $data): array
    {
        // Gather all tech keywords from every job
        $techKeywords = [];
        foreach ($this->arrayOf($data, 'jobs') as $job) {
            if (! is_array($job)) {
                continue;
            }

            foreach ($this->stringListOf($job, 'tech') as $tech) {
                $techKeywords[] = $tech;
            }
        }
        $techKeywords = array_values(array_unique($techKeywords));

        // Top-level skills list (flat string array)
        $topSkills = $this->stringListOf($data, 'skills');

        $skills = [];

        if ($techKeywords !== []) {
            $skills[] = ['name' => 'Technologies', 'keywords' => $techKeywords];
        }

        foreach ($topSkills as $skill) {
            $skills[] = ['name' => $skill];
        }

        return $skills;
    }

    /**
     * @param  array<mixed>  $data
     * @return array<mixed>
     */
    protected function buildLanguages(array $data): array
    {
        $languages = [];

        foreach ($this->arrayOf($data, 'languages') as $lang) {
            if (! is_array($lang)) {
                continue;
            }

            $item = [];
            $this->setIfPresent($item, 'language', $lang, 'language');
            // Accept 'level' (intermediate) or 'fluency' (JSON Resume field name)
            $fluencyValue = $this->stringOf($lang, 'level') ?: $this->stringOf($lang, 'fluency');
            if ($fluencyValue !== '') {
                $item['fluency'] = $fluencyValue;
            }

            if ($item !== []) {
                $languages[] = $item;
            }
        }

        return $languages;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Set a key on $target only if the source value is a non-empty string.
     *
     * @param  array<mixed>  $target
     * @param  array<mixed>  $source
     */
    protected function setIfPresent(array &$target, string $targetKey, array $source, string $sourceKey): void
    {
        $value = $this->stringOf($source, $sourceKey);

        if ($value !== '') {
            if ($targetKey === 'url') {
                $value = $this->normalizeUrl($value);
            }

            $target[$targetKey] = $value;
        }
    }

    /**
     * Ensure URLs include a scheme. If missing, prepend https:// when it
     * looks like a domain or host (basic heuristic).
     */
    protected function normalizeUrl(string $url): string
    {
        $trimmed = trim($url);

        // If it already has a scheme (e.g., http:, https:, mailto:, ftp:), leave it.
        if (preg_match('/^[a-z][a-z0-9+.-]*:/i', $trimmed) === 1) {
            return $trimmed;
        }

        // If it looks like an email address, return as-is (not a URI scheme issue).
        if (filter_var($trimmed, FILTER_VALIDATE_EMAIL)) {
            return $trimmed;
        }

        // If it contains whitespace or is clearly not a hostname, leave it untouched.
        if (preg_match('/\s/', $trimmed)) {
            return $trimmed;
        }

        // If it contains at least one dot (e.g. example.com or www.example.com)
        // assume it's a URL missing scheme and prepend https://
        if (strpos($trimmed, '.') !== false) {
            return 'https://'.$trimmed;
        }

        return $trimmed;
    }

    /**
     * Return the value at $key as a trimmed string, or '' if missing/empty.
     *
     * @param  array<mixed>  $source
     */
    protected function stringOf(array $source, string $key): string
    {
        $value = $source[$key] ?? null;

        if ($value === null || $value === '' || $value === false) {
            return '';
        }

        return trim((string) $value);
    }

    /**
     * Return the value at $key as an array, or [] if missing/not-an-array.
     *
     * @param  array<mixed>  $source
     * @return array<mixed>
     */
    protected function arrayOf(array $source, string $key): array
    {
        $value = $source[$key] ?? null;

        return is_array($value) ? $value : [];
    }

    /**
     * Return a list of non-empty strings from an array value in $source.
     *
     * @param  array<mixed>  $source
     * @return array<string>
     */
    protected function stringListOf(array $source, string $key): array
    {
        $result = [];

        foreach ($this->arrayOf($source, $key) as $item) {
            if (is_string($item) && trim($item) !== '') {
                $result[] = trim($item);
            }
        }

        return $result;
    }

    /**
     * Check whether a string matches the JSON Resume iso8601 date pattern.
     * Accepts YYYY-MM-DD, YYYY-MM, or YYYY.
     */
    protected function isValidIso8601(string $value): bool
    {
        return (bool) preg_match('/^[1-2][0-9]{3}(-[0-1][0-9](-[0-3][0-9])?)?$/', $value);
    }

    /**
     * Extract skill strings from certificate entries.
     * Looks for explicit arrays (`skills`, `keywords`) or parses a
     * comma-separated `summary` section when present.
     *
     * @param  array<mixed>  $certificates
     * @return array<string>
     */
    protected function extractSkillsFromCertificates(array $certificates): array
    {
        $result = [];

        foreach ($certificates as $cert) {
            if (! is_array($cert)) {
                continue;
            }

            // Explicit 'skills' or 'keywords' arrays
            if (isset($cert['skills']) && is_array($cert['skills'])) {
                foreach ($cert['skills'] as $s) {
                    if (is_string($s) && trim($s) !== '') {
                        $result[] = trim($s);
                    }
                }
            }

            if (isset($cert['keywords']) && is_array($cert['keywords'])) {
                foreach ($cert['keywords'] as $k) {
                    if (is_string($k) && trim($k) !== '') {
                        $result[] = trim($k);
                    }
                }
            }

            // Parse comma-separated list in 'summary' (agent may include skills here)
            if (isset($cert['summary']) && is_string($cert['summary'])) {
                $parts = array_map('trim', explode(',', $cert['summary']));
                foreach ($parts as $p) {
                    if ($p === '') {
                        continue;
                    }

                    // Heuristic: skip very long fragments
                    if (strlen($p) > 60) {
                        continue;
                    }

                    $result[] = $p;
                }
            }
        }

        return array_values(array_unique($result));
    }

    /**
     * Check whether a skill string already exists in the skills array.
     * Matches against `name` and `keywords` entries.
     *
     * @param  array<mixed>  $skills
     */
    protected function skillExists(array $skills, string $name): bool
    {
        $needle = mb_strtolower($name);

        foreach ($skills as $s) {
            if (! is_array($s)) {
                continue;
            }

            if (isset($s['name']) && mb_strtolower((string) $s['name']) === $needle) {
                return true;
            }

            if (isset($s['keywords']) && is_array($s['keywords'])) {
                foreach ($s['keywords'] as $k) {
                    if (is_string($k) && mb_strtolower($k) === $needle) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
