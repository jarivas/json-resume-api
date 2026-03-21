<?php

namespace App\Observers;

use App\Services\Ai\EmbeddingService;
use Illuminate\Support\Str;

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
