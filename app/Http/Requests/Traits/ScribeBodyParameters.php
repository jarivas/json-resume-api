<?php

namespace App\Http\Requests\Traits;

trait ScribeBodyParameters
{
    /**
     * Generate body parameters for Scribe from the FormRequest rules().
     *
     * @return array
     */
    public function bodyParameters(): array
    {
        $rules = $this->rules();
        $params = [];

        foreach ($rules as $key => $rule) {
            // Prepare a string representation of the rule where possible
            $ruleString = '';
            $required = false;

            if (is_array($rule)) {
                foreach ($rule as $r) {
                    if (is_string($r)) {
                        $ruleString .= $r.'|';
                        if (str_contains($r, 'required')) {
                            $required = true;
                        }
                    }
                }
            } elseif (is_string($rule)) {
                $ruleString = $rule;
                $required = str_contains($ruleString, 'required');
            }

            // Infer type from string rules where available
            $type = 'string';
            if (str_contains($ruleString, 'array')) {
                $type = 'array';
            } elseif (str_contains($ruleString, 'file') || str_contains($ruleString, 'mimes') || str_contains($ruleString, 'mimetypes')) {
                $type = 'file';
            } elseif (str_contains($ruleString, 'integer') || str_contains($ruleString, 'numeric')) {
                $type = 'integer';
            } elseif (str_contains($ruleString, 'boolean')) {
                $type = 'boolean';
            }

            // For files we avoid providing an example path (Scribe may attempt to fopen it).
            if ($type === 'file') {
                $example = null;
            } else {
                $example = match ($type) {
                    'integer' => 1,
                    'boolean' => true,
                    'array' => [],
                    default => 'string',
                };
            }

            // Provide richer hints for well-known keys (location, profiles, emails, urls, dates, etc.)
            $hintDescription = '';
            $hintExample = $example;

            $lowerKey = strtolower($key);

            if (str_ends_with($lowerKey, 'address') || str_contains($lowerKey, '.address')) {
                $hintDescription = 'Street address';
                $hintExample = '123 Main St';
            } elseif (str_contains($lowerKey, 'postal') || str_contains($lowerKey, 'postcode')) {
                $hintDescription = 'Postal code / ZIP';
                $hintExample = '28001';
            } elseif (str_contains($lowerKey, 'city')) {
                $hintDescription = 'City';
                $hintExample = 'Madrid';
            } elseif (str_contains($lowerKey, 'country') || str_ends_with($lowerKey, 'countrycode')) {
                $hintDescription = 'ISO 3166-1 alpha-2 country code';
                $hintExample = 'ES';
            } elseif (str_ends_with($lowerKey, 'url') || str_contains($lowerKey, '.url')) {
                $hintDescription = 'URL';
                $hintExample = 'https://example.com';
            } elseif (str_contains($lowerKey, 'email')) {
                $hintDescription = 'Email address';
                $hintExample = 'user@example.com';
            } elseif (str_contains($lowerKey, 'phone') || str_contains($lowerKey, 'tel')) {
                $hintDescription = 'Phone number';
                $hintExample = '+34 600 000 000';
            } elseif (str_contains($lowerKey, 'date') || str_contains($lowerKey, 'startdate') || str_contains($lowerKey, 'enddate') || preg_match('/\bdate\b/i', $lowerKey)) {
                $hintDescription = 'Date (Y-m-d)';
                $hintExample = '2023-01-01';
            } elseif (str_contains($lowerKey, 'summary') || str_contains($lowerKey, 'description') || str_contains($lowerKey, 'bio')) {
                $hintDescription = 'Short description';
                $hintExample = 'Experienced developer with ...';
            } elseif (str_contains($lowerKey, 'name') || str_contains($lowerKey, 'title') || str_contains($lowerKey, 'label')) {
                $hintDescription = 'Name / title';
                $hintExample = 'Jane Doe';
            } elseif (str_starts_with($lowerKey, 'profiles') || str_contains($lowerKey, 'profiles.*')) {
                if ($lowerKey === 'profiles') {
                    $hintDescription = 'Array of social profiles';
                    $hintExample = [['network' => 'github', 'username' => 'janedoe', 'url' => 'https://github.com/janedoe']];
                    $type = 'array';
                } elseif (str_ends_with($lowerKey, '.network')) {
                    $hintDescription = 'Social network name';
                    $hintExample = 'github';
                } elseif (str_ends_with($lowerKey, '.username')) {
                    $hintDescription = 'Profile username';
                    $hintExample = 'janedoe';
                } elseif (str_ends_with($lowerKey, '.url')) {
                    $hintDescription = 'Profile URL';
                    $hintExample = 'https://github.com/janedoe';
                }
            } elseif (preg_match('/^(awards|certificates|educations|interests|languages|projects|publications|references|skills|volunteer|work)$/', $lowerKey)) {
                $hintDescription = 'Array of references (IDs or objects)';
                $hintExample = ['01Fxxxxxxxxxxxxxxxxxxxxxxx'];
                $type = 'array';
            }

            $params[$key] = [
                'description' => $hintDescription,
                'required' => $required,
                'type' => $type,
                'example' => $hintExample,
            ];
        }

        return $params;
    }
}
