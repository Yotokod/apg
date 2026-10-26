<?php

namespace App\Livewire;

use App\Models\LessonProgress;
use App\Models\QuizNode;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Step-by-step branching quiz. Each option may point to a different next node
 * (configured in admin); a null next node ends the quiz.
 */
class Quiz extends Component
{
    #[Locked]
    public int $lessonId;

    #[Locked]
    public ?int $nodeId = null;
    public ?int $picked = null;
    #[Locked]
    public array $path = [];   // [nodeId => optionId]

    #[Locked]
    public int $correct = 0;
    public bool $finished = false;

    public function mount(int $lessonId): void
    {
        $this->lessonId = $lessonId;
        $this->assertEnrolled();
        $this->nodeId = QuizNode::where('lesson_id', $lessonId)->orderByDesc('is_start')->orderBy('id')->value('id');
    }

    private function assertEnrolled(): void
    {
        $lesson = \App\Models\Lesson::with('module')->findOrFail($this->lessonId);
        abort_unless(auth()->check() && \App\Models\Enrollment::where('user_id', auth()->id())
            ->where('course_id', $lesson->module->course_id)->where('paid', true)->exists(), 403);
    }

    private function node(): QuizNode
    {
        return QuizNode::where('lesson_id', $this->lessonId)->findOrFail($this->nodeId);
    }

    public function pick(int $optionId): void
    {
        if ($this->picked) {
            return;
        }
        $opt = $this->node()->options()->findOrFail($optionId);
        $this->picked = $opt->id;
        $this->path[$this->nodeId] = $opt->id;
        $this->correct += $opt->is_correct ? 1 : 0;
    }

    public function next(): void
    {
        $opt = $this->node()->options()->find($this->picked);
        $this->picked = null;
        if ($opt?->next_node_id && QuizNode::where('lesson_id', $this->lessonId)->whereKey($opt->next_node_id)->exists()) {
            $this->nodeId = $opt->next_node_id;

            return;
        }
        $this->finished = true;
        $total = max(1, count($this->path));
        if (auth()->check()) {
            LessonProgress::updateOrCreate(
                ['user_id' => auth()->id(), 'lesson_id' => $this->lessonId],
                ['score' => (int) round($this->correct / $total * 100), 'completed_at' => now()],
            );
            $this->dispatch('lesson-progress');
        }
    }

    public function restart(): void
    {
        $this->reset('picked', 'path', 'correct', 'finished');
        $this->mount($this->lessonId);
    }

    public function render()
    {
        $node = $this->finished ? null : QuizNode::with('options')->where('lesson_id', $this->lessonId)->find($this->nodeId);
        $total = max(1, count($this->path));

        return view('livewire.quiz', [
            'node' => $node,
            'step' => count($this->path) + ($this->picked ? 0 : 1),
            'percent' => (int) round($this->correct / $total * 100),
        ]);
    }
}
