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
 *   certs|certifications[]{name, issuer, date},
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

        $skills = $this->buildSkills($data);
        if ($skills !== []) {
            $resume['skills'] = $skills;
        }

        $languages = $this->buildLanguages($data);
        if ($languages !== []) {
            $resume['languages'] = $languages;
        }

        return $resume;
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

        // Accept 'certs' (intermediate format) and 'certifications' / 'certificates' (model aliases)
        $entries = array_merge(
            $this->arrayOf($data, 'certs'),
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
            $target[$targetKey] = $value;
        }
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
}
