<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Lightweight, dependency-free exam AI helper.
 *
 * Works fully offline using classic NLP similarity (word Jaccard + character
 * trigram cosine) so it needs no paid keys. If a free OpenAI-compatible
 * endpoint is configured via AI_API_URL + AI_API_KEY (e.g. OpenRouter free
 * models), essay scoring is upgraded to use it, with a safe local fallback.
 */
class ExamAi
{
    /** Normalize text for comparison. */
    public function normalize(string $text): string
    {
        $text = mb_strtolower(trim($text));
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text) ?? $text;
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }

    /** Token (word) set for an answer. */
    private function tokens(string $text): array
    {
        $n = $this->normalize($text);

        return $n === '' ? [] : explode(' ', $n);
    }

    /** Character trigram multiset for catching reworded-but-similar structure. */
    private function trigrams(string $text): array
    {
        $n = str_replace(' ', '_', $this->normalize($text));
        $grams = [];
        $len = mb_strlen($n);
        if ($len < 3) {
            return $n === '' ? [] : [$n => 1];
        }
        for ($i = 0; $i <= $len - 3; $i++) {
            $g = mb_substr($n, $i, 3);
            $grams[$g] = ($grams[$g] ?? 0) + 1;
        }

        return $grams;
    }

    private function jaccard(array $a, array $b): float
    {
        $a = array_unique($a);
        $b = array_unique($b);
        if (! $a && ! $b) {
            return 0.0;
        }
        $inter = count(array_intersect($a, $b));
        $union = count(array_unique(array_merge($a, $b)));

        return $union ? $inter / $union : 0.0;
    }

    private function cosine(array $a, array $b): float
    {
        if (! $a || ! $b) {
            return 0.0;
        }
        $dot = 0.0;
        foreach ($a as $k => $v) {
            if (isset($b[$k])) {
                $dot += $v * $b[$k];
            }
        }
        $magA = sqrt(array_sum(array_map(fn ($v) => $v * $v, $a)));
        $magB = sqrt(array_sum(array_map(fn ($v) => $v * $v, $b)));

        return ($magA && $magB) ? $dot / ($magA * $magB) : 0.0;
    }

    /**
     * Similarity between two texts as a 0-100 percentage.
     * Blends word-overlap (vocabulary) and character-trigram (sentence
     * construction), so it catches both copied words and reworded structure.
     */
    public function similarity(string $a, string $b): int
    {
        if (trim($a) === '' || trim($b) === '') {
            return 0;
        }
        $word = $this->jaccard($this->tokens($a), $this->tokens($b));
        $struct = $this->cosine($this->trigrams($a), $this->trigrams($b));
        $blend = (0.45 * $word) + (0.55 * $struct);

        return (int) round($blend * 100);
    }

    /**
     * Score an essay answer 0-100 with short feedback.
     * Uses an optional reference/model answer or keyword rubric when provided.
     */
    public function scoreEssay(string $answer, ?string $reference = null, array $keywords = []): array
    {
        $answer = trim($answer);
        if ($answer === '') {
            return ['score' => 0, 'feedback' => 'No answer provided.', 'engine' => 'local'];
        }

        $remote = $this->scoreEssayRemote($answer, $reference, $keywords);
        if ($remote) {
            return $remote;
        }

        $words = count($this->tokens($answer));
        $sentences = max(1, preg_match_all('/[.!?]+/', $answer));

        $score = 0;
        $notes = [];

        // Relevance against reference / keywords (up to 70 pts).
        if ($reference && trim($reference) !== '') {
            $sim = $this->similarity($answer, $reference);
            $score += (int) round($sim * 0.7);
            $notes[] = "Relevance to model answer: {$sim}%.";
        } elseif ($keywords) {
            $hitWords = $this->tokens(implode(' ', $keywords));
            $ansWords = array_unique($this->tokens($answer));
            $hit = count(array_intersect(array_unique($hitWords), $ansWords));
            $cov = $hitWords ? $hit / count(array_unique($hitWords)) : 0;
            $score += (int) round($cov * 70);
            $notes[] = 'Covered '.$hit.' of '.count(array_unique($hitWords)).' key points.';
        } else {
            // No rubric: reward substance up to 50 pts.
            $score += min(50, (int) round($words / 4));
            $notes[] = 'No model answer set — scored on content depth.';
        }

        // Development / length (up to 20 pts).
        $score += min(20, (int) round($words / 8));
        // Structure (up to 10 pts).
        $score += min(10, $sentences * 2);

        $score = max(0, min(100, $score));
        $notes[] = "{$words} words, {$sentences} sentence(s).";

        return ['score' => $score, 'feedback' => implode(' ', $notes), 'engine' => 'local'];
    }

    /** Optional upgrade via a free OpenAI-compatible endpoint. */
    private function scoreEssayRemote(string $answer, ?string $reference, array $keywords): ?array
    {
        $url = config('services.exam_ai.url');
        $key = config('services.exam_ai.key');
        $model = config('services.exam_ai.model');
        if (! $url || ! $key) {
            return null;
        }

        try {
            $rubric = $reference ? "Model answer: {$reference}" : ($keywords ? 'Key points: '.implode(', ', $keywords) : 'No model answer; judge depth and coherence.');
            $resp = Http::timeout(20)->withToken($key)->post(rtrim($url, '/').'/chat/completions', [
                'model' => $model ?: 'meta-llama/llama-3.1-8b-instruct:free',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a strict but fair exam grader. Reply ONLY with compact JSON: {"score": <0-100 integer>, "feedback": "<one sentence>"}.'],
                    ['role' => 'user', 'content' => "{$rubric}\n\nStudent answer:\n{$answer}"],
                ],
                'temperature' => 0.2,
            ]);
            if (! $resp->successful()) {
                return null;
            }
            $content = $resp->json('choices.0.message.content');
            if (! $content) {
                return null;
            }
            preg_match('/\{.*\}/s', $content, $m);
            $json = json_decode($m[0] ?? $content, true);
            if (! is_array($json) || ! isset($json['score'])) {
                return null;
            }

            return [
                'score' => max(0, min(100, (int) $json['score'])),
                'feedback' => (string) ($json['feedback'] ?? 'Graded by AI.'),
                'engine' => 'ai',
            ];
        } catch (\Throwable $e) {
            Log::warning('ExamAi remote scoring failed: '.$e->getMessage());

            return null;
        }
    }
}
