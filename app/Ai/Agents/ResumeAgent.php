<?php

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use App\Services\Ai\EmbeddingService;

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
        $input = mb_strtolower($input);

        $keywords = [
            'cv', 'currículum', 'curriculum', 'curriculo', 'resumé', 'resume',
            'experiencia', 'experience', 'education', 'formación', 'estudios',
            'habilidades', 'skills', 'skill', 'proyecto', 'proyectos', 'projects',
            'publicación', 'publications', 'certificado', 'certificate', 'award', 'premio',
            'referencia', 'references', 'idioma', 'languages', 'contacto', 'contact', 'perfil', 'summary',
        ];

        foreach ($keywords as $kw) {
            if (str_contains($input, $kw)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Recover semantically relevant resume fragments for a given query.
     * Returns a short formatted context string suitable to attach to a prompt.
     */
    public function semanticContext(string $query, int $limit = 3): string
    {
        $svc = new EmbeddingService();
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
