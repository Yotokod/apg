<?php

namespace App\Livewire;

use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\QuizNode;
use App\Models\QuizOption;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Livewire\Component;

/**
 * Step-by-step branching quiz. Each option may point to a different next node
 * (configured in admin); a null next node ends the quiz.
 *
 * The story-style UI (resources/js/lesson/quiz.js) calls answer()/back()/restart():
 * correctness is only revealed by the server after a pick, all state is #[Locked].
 */
class Quiz extends Component
{
    public const PASS_PERCENT = 60;

    #[Locked]
    public int $lessonId;

    #[Locked]
    public ?int $nodeId = null;

    #[Locked]
    public ?int $picked = null;

    #[Locked]
    public array $path = [];   // [nodeId => optionId]

    #[Locked]
    public array $history = []; // answered node ids, in order

    #[Locked]
    public int $correct = 0;

    #[Locked]
    public bool $finished = false;

    public function mount(int $lessonId): void
    {
        $this->lessonId = $lessonId;
        $this->assertEnrolled();
        $this->nodeId = QuizNode::where('lesson_id', $lessonId)->orderByDesc('is_start')->orderBy('id')->value('id');
    }

    private function assertEnrolled(): void
    {
        $lesson = Lesson::with('module')->findOrFail($this->lessonId);
        abort_unless(auth()->check() && Enrollment::where('user_id', auth()->id())
            ->where('course_id', $lesson->module->course_id)->where('paid', true)->exists(), 403);
    }

    private function node(): QuizNode
    {
        return QuizNode::where('lesson_id', $this->lessonId)->findOrFail($this->nodeId);
    }

    public function pick(int $optionId): void
    {
        if ($this->picked || $this->finished) {
            return;
        }
        $opt = $this->node()->options()->findOrFail($optionId);
        $this->picked = $opt->id;
        $this->path[$this->nodeId] = $opt->id;
        $this->recount();
    }

    public function next(): void
    {
        if (! $this->picked) {
            return;
        }
        $opt = $this->node()->options()->find($this->picked);
        $this->picked = null;
        $this->history[] = $this->nodeId;
        if ($opt?->next_node_id && QuizNode::where('lesson_id', $this->lessonId)->whereKey($opt->next_node_id)->exists()) {
            $this->nodeId = $opt->next_node_id;

            return;
        }
        $this->finished = true;
        if (auth()->check()) {
            LessonProgress::updateOrCreate(
                ['user_id' => auth()->id(), 'lesson_id' => $this->lessonId],
                ['score' => $this->percent(), 'completed_at' => now()],
            );
            $this->dispatch('lesson-progress');
        }
    }

    /** Story UI: submit a pick, get feedback + the next question in one round trip. */
    #[Renderless]
    public function answer(int $optionId): array
    {
        if ($this->finished || ! $this->nodeId) {
            return ['stale' => true];
        }
        $node = QuizNode::with('options')->where('lesson_id', $this->lessonId)->findOrFail($this->nodeId);
        $opt = $node->options->firstWhere('id', $optionId);
        if (! $opt) {
            return ['stale' => true];
        }
        $this->pick($opt->id);
        $this->next();

        return [
            'correct' => (bool) $opt->is_correct,
            'correctIds' => $node->options->where('is_correct', true)->pluck('id')->values()->all(),
            'explanation' => (string) $node->tr('explanation'),
            'finished' => $this->finished,
            'node' => $this->finished ? null : $this->payload(),
        ] + $this->stats();
    }

    /** Story UI: undo the last answer and return to that question. */
    #[Renderless]
    public function back(): array
    {
        $previous = array_pop($this->history);
        if ($previous === null) {
            return ['node' => $this->finished ? null : $this->payload()] + $this->stats();
        }
        unset($this->path[$previous]);
        $this->nodeId = $previous;
        $this->picked = null;
        $this->finished = false;
        $this->recount();

        return ['node' => $this->payload()] + $this->stats();
    }

    #[Renderless]
    public function restart(): array
    {
        $this->reset('picked', 'path', 'history', 'correct', 'finished');
        $this->mount($this->lessonId);

        return ['node' => $this->nodeId ? $this->payload() : null] + $this->stats();
    }

    private function recount(): void
    {
        $this->correct = QuizOption::whereIn('id', array_values($this->path))->where('is_correct', true)->count();
    }

    private function percent(): int
    {
        return (int) round($this->correct / max(1, count($this->path)) * 100);
    }

    private function stats(): array
    {
        return ['good' => $this->correct, 'answered' => count($this->path), 'percent' => $this->percent(), 'canBack' => $this->history !== []];
    }

    /** Current question without any correctness data. */
    private function payload(): array
    {
        $node = QuizNode::with('options')->where('lesson_id', $this->lessonId)->findOrFail($this->nodeId);

        return [
            'id' => $node->id,
            'question' => (string) $node->tr('question'),
            'options' => $node->options->map(fn (QuizOption $o) => ['id' => $o->id, 'label' => (string) $o->tr('label')])->values()->all(),
        ];
    }

    /** First lesson of the quiz's module that the learner has not completed yet (else the first one). */
    private function reviewLesson(): ?Lesson
    {
        $lesson = Lesson::findOrFail($this->lessonId);
        $candidates = Lesson::where('course_module_id', $lesson->course_module_id)->whereKeyNot($lesson->id)->orderBy('position')->get();
        $done = LessonProgress::where('user_id', auth()->id())->whereNotNull('completed_at')
            ->whereIn('lesson_id', $candidates->pluck('id'))->pluck('lesson_id')->all();

        return $candidates->first(fn (Lesson $l) => ! in_array($l->id, $done, true)) ?? $candidates->first();
    }

    public function render()
    {
        $review = $this->reviewLesson();

        return view('livewire.quiz', [
            'config' => [
                'node' => ($this->finished || ! $this->nodeId) ? null : $this->payload(),
                'finished' => $this->finished,
                'total' => QuizNode::where('lesson_id', $this->lessonId)->count(),
                'passPercent' => self::PASS_PERCENT,
                'review' => $review ? ['title' => $review->tr('title'), 'url' => route('lesson', $review->id)] : null,
            ] + $this->stats(),
        ]);
    }
}
