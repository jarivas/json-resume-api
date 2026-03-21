<?php

namespace App\Observers;

use App\Services\Ai\EmbeddingService;
use Illuminate\Support\Str;
use App\Models\ResumeKeyword;
use Illuminate\Support\Arr;

class ResumeModelObserver
{
    protected EmbeddingService $embeddings;

    public function __construct()
    {
        $this->embeddings = new EmbeddingService();
    }

    public function saved($model): void
    {
        // Create a short content summary to embed. Use model attributes commonly useful for CV.
        $summary = $this->buildSummary($model);

        if ($summary) {
            $this->embeddings->upsertEmbeddingForModel($model, $summary);
            $this->upsertKeywordsFromSummary($summary, $model);
        }
    }

    
        protected function upsertKeywordsFromSummary(string $summary, $model): void
        {
            $candidates = [];
            $verbs = [];

            // 1) Ask the model for suggested keywords/verbs, if it exposes helper methods
            foreach (['suggestResumeKeywords', 'resumeKeywords', 'suggestKeywords'] as $method) {
                if (method_exists($model, $method)) {
                    try {
                        $result = $model->{$method}();
                    } catch (\Throwable $e) {
                        $result = null;
                    }

                    if (is_iterable($result)) {
                        foreach ($result as $r) {
                            $r = trim(mb_strtolower((string) $r));
                            if ($r !== '') {
                                $candidates[] = $r;
                            }
                        }
                    } elseif (is_string($result) && $result !== '') {
                        $candidates[] = trim(mb_strtolower($result));
                    }

                    if (! empty($candidates)) {
                        break;
                    }
                }
            }

            // 2) If no suggestions from model methods, try calling the LLM agent directly (if available)
            if (empty($candidates) && function_exists('agent')) {
                try {
                    $instr = "Given the following short resume summary, return a JSON object with two arrays: 'keywords' (short phrases or tokens useful to identify this CV) and 'verbs' (action/indicator verbs a user might use to ask about these skills). Example: {\"keywords\": [\"php 8.1\", \"machine learning\"], \"verbs\": [\"tiene\", \"ha trabajado\"] }. Only return valid JSON.";
                    $response = agent(instructions: $instr)->prompt($summary);
                    $text = is_object($response) && isset($response->text) ? (string) $response->text : (string) $response;
                    $json = json_decode($text, true);
                    if (is_array($json)) {
                        if (! empty($json['keywords']) && is_array($json['keywords'])) {
                            foreach ($json['keywords'] as $k) {
                                $k = trim(mb_strtolower((string) $k));
                                if ($k !== '') {
                                    $candidates[] = $k;
                                }
                            }
                        }
                        if (! empty($json['verbs']) && is_array($json['verbs'])) {
                            foreach ($json['verbs'] as $v) {
                                $v = trim(mb_strtolower((string) $v));
                                if ($v !== '') {
                                    $verbs[] = $v;
                                }
                            }
                        }
                    } else {
                        // If not JSON, try comma separated fallback
                        $parts = array_map('trim', preg_split('/[,;|\\n]+/', $text));
                        foreach ($parts as $p) {
                            if (mb_strlen($p) > 2) {
                                $candidates[] = mb_strtolower($p);
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    // ignore LLM failures and fallback below
                }
            }

            // 3) Fallback: derive candidate keywords from the summary text (ngrams) if still empty
            if (empty($candidates)) {
                $text = mb_strtolower($summary);
                $words = preg_split('/[^\p{L}\p{N}]+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
                $words = array_values(array_filter($words, fn($w) => mb_strlen($w) > 2));

                $max = min(100, count($words));
                for ($i = 0; $i < $max; $i++) {
                    $candidates[] = $words[$i];
                    if (isset($words[$i + 1])) {
                        $candidates[] = $words[$i].' '.$words[$i + 1];
                    }
                    if (isset($words[$i + 2])) {
                        $candidates[] = $words[$i].' '.$words[$i + 1].' '.$words[$i + 2];
                    }
                }

                // Capture version-like tokens (php 8.1, php8.0, 7.4) as candidates too
                if (preg_match_all('/php\s?\d(?:\.\d)?/i', $text, $m)) {
                    foreach (Arr::flatten($m) as $token) {
                        $candidates[] = trim(mb_strtolower($token));
                    }
                }
            }

            // Normalize, dedupe and persist as generic resume keywords and verbs
            $candidates = array_values(array_unique(array_filter(array_map(fn($s) => trim((string) $s), $candidates))));
            foreach ($candidates as $kw) {
                if ($kw === '') {
                    continue;
                }
                ResumeKeyword::firstOrCreate([
                    'keyword' => $kw,
                ], ['category' => 'resume']);
            }

            $verbs = array_values(array_unique(array_filter(array_map(fn($s) => trim((string) $s), $verbs))));
            foreach ($verbs as $verb) {
                if ($verb === '') {
                    continue;
                }
                ResumeKeyword::firstOrCreate([
                    'keyword' => $verb,
                ], ['category' => 'verb']);
            }
        }

    protected function buildSummary($model): ?string
    {
        // Prefer common fields; fallback to model->toArray() truncated
        $attrs = [];

        if (isset($model->position) || isset($model->name)) {
            if (isset($model->position)) {
                $attrs[] = $model->position;
            }
            if (isset($model->name)) {
                $attrs[] = $model->name;
            }
        }

        if (! empty($model->summary)) {
            $attrs[] = $model->summary;
        }

        if (isset($model->highlights) && is_array($model->highlights)) {
            $attrs[] = implode('; ', array_slice($model->highlights, 0, 5));
        }

        if (empty($attrs)) {
            $data = $model->toArray();
            $flat = [];
            foreach ($data as $k => $v) {
                if (is_scalar($v)) {
                    $flat[] = $k.': '.$v;
                }
            }
            $attrs[] = implode(' | ', array_slice($flat, 0, 6));
        }

        $text = trim(implode('. ', $attrs));

        return $text !== '' ? Str::limit($text, 1000) : null;
    }
}
