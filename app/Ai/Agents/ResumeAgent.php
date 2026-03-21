<?php

namespace App\Ai\Agents;

use App\Models\ResumeKeyword;
use App\Services\Ai\EmbeddingService;
use Illuminate\Support\Facades\Cache;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;

class ResumeAgent implements Agent, Conversational, HasTools
{
    use Promptable;

    /**
     * The agent receives strict instructions: only answer questions about the user's
     * curriculum vitae (resume). If the user asks anything outside that scope, the
     * agent MUST refuse with a short, polite message.
     */
    public function __construct(
        public string $instructions = "You are a helpful assistant that ONLY answers questions about the user's resume (CV)."
            .' Use the provided resume context when relevant. If the user asks anything not related to the resume,'
            ." reply with a short refusal: 'Lo siento, sólo puedo responder preguntas relacionadas con el CV.'",
        public iterable $messages = [],
        public iterable $tools = []
    ) {}

    public function instructions(): string
    {
        return $this->instructions;
    }

    public function messages(): iterable
    {
        return $this->messages;
    }

    public function tools(): iterable
    {
        return $this->tools;
    }

    /**
     * Lightweight heuristic to decide whether an incoming query is about the CV.
     * Returns true if common CV-related keywords are present or if the query contains
     * words that map to CV sections (experience, education, skills, projects, etc.).
     */
    public function allowsQuery(string $input): bool
    {
        $input = $this->normalizeInput($input);

        if ($this->containsAnyIndicator($input)) {
            return true;
        }

        $genericKeywords = $this->getGenericKeywords();
        $evaluationVerbs = $this->getEvaluationVerbs();

        if ($this->containsAnyWithWhile($input, $genericKeywords)) {
            return true;
        }

        if ($this->containsEvaluationCombination($input, $evaluationVerbs, $genericKeywords)) {
            return true;
        }

        return false;
    }

    private function normalizeInput(string $input): string
    {
        return mb_strtolower($input);
    }

    private function containsAnyIndicator(string $input): bool
    {
        $keywords = [
            'cv', 'currículum', 'curriculum', 'curriculo', 'resumé', 'resume',
            'experiencia', 'experience', 'education', 'formación', 'estudios',
            'habilidades', 'skills', 'skill', 'proyecto', 'proyectos', 'projects',
            'publicación', 'publications', 'certificado', 'certificate', 'award', 'premio',
            'referencia', 'references', 'idioma', 'languages', 'contacto', 'contact', 'perfil', 'summary',
        ];

        return $this->containsAnyWithWhile($input, $keywords);
    }

    private function containsAnyWithWhile(string $input, array $keywords): bool
    {
        $i = 0;
        $len = count($keywords);
        while ($i < $len) {
            if (str_contains($input, $keywords[$i])) {
                return true;
            }
            $i++;
        }

        return false;
    }

    private function getGenericKeywords(): array
    {
        return Cache::remember('resume_keywords_generic', 60 * 60, function () {
            return ResumeKeyword::where('category', 'resume')->pluck('keyword')->map(fn ($k) => mb_strtolower($k))->toArray();
        });
    }

    private function getEvaluationVerbs(): array
    {
        return Cache::remember('resume_keywords_verbs', 60 * 60, function () {
            return ResumeKeyword::where('category', 'verb')->pluck('keyword')->map(fn ($k) => mb_strtolower($k))->toArray();
        });
    }

    private function containsEvaluationCombination(string $input, array $verbs, array $genericKeywords): bool
    {
        $i = 0;
        $len = count($verbs);
        while ($i < $len) {
            $v = $verbs[$i];
            if (str_contains($input, $v)) {
                if ($this->containsAnyIndicator($input)) {
                    return true;
                }

                if ($this->containsAnyWithWhile($input, $genericKeywords)) {
                    return true;
                }
            }
            $i++;
        }

        return false;
    }

    /**
     * Recover semantically relevant resume fragments for a given query.
     * Returns a short formatted context string suitable to attach to a prompt.
     */
    public function semanticContext(string $query, int $limit = 3): string
    {
        $svc = new EmbeddingService;
        $results = $svc->findMostSimilar($query, $limit);

        if (empty($results)) {
            return '';
        }

        $parts = [];
        foreach ($results as $r) {
            $rec = $r['record'];
            $score = isset($r['score']) ? round((float) $r['score'], 4) : 0.0;
            $content = trim(preg_replace('/\s+/', ' ', (string) ($rec->content ?? '')));
            $parts[] = "[score: {$score}] {$content}";
        }

        return "Contexto del CV (más relevante primero):\n".implode("\n\n", $parts);
    }
}
