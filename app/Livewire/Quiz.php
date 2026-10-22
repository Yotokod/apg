<?php

namespace App\Livewire;

use App\Models\LessonProgress;
use App\Models\QuizNode;
use Livewire\Component;

/**
 * Step-by-step branching quiz. Each option may point to a different next node
 * (configured in admin); a null next node ends the quiz.
 */
class Quiz extends Component
{
    public int $lessonId;
    public ?int $nodeId = null;
    public ?int $picked = null;
    public array $path = [];   // [nodeId => optionId]
    public int $correct = 0;
    public bool $finished = false;

    public function mount(int $lessonId): void
    {
        $this->lessonId = $lessonId;
        $this->nodeId = QuizNode::where('lesson_id', $lessonId)->orderByDesc('is_start')->orderBy('id')->value('id');
    }

    public function pick(int $optionId): void
    {
        if ($this->picked) {
            return;
        }
        $opt = QuizNode::findOrFail($this->nodeId)->options()->findOrFail($optionId);
        $this->picked = $opt->id;
        $this->path[$this->nodeId] = $opt->id;
        $this->correct += $opt->is_correct ? 1 : 0;
    }

    public function next(): void
    {
        $opt = QuizNode::findOrFail($this->nodeId)->options()->find($this->picked);
        $this->picked = null;
        if ($opt?->next_node_id) {
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
        }
    }

    public function restart(): void
    {
        $this->reset('picked', 'path', 'correct', 'finished');
        $this->mount($this->lessonId);
    }

    public function render()
    {
        $node = $this->finished ? null : QuizNode::with('options')->find($this->nodeId);
        $total = max(1, count($this->path));

        return view('livewire.quiz', [
            'node' => $node,
            'step' => count($this->path) + ($this->picked ? 0 : 1),
            'percent' => (int) round($this->correct / $total * 100),
        ]);
    }
}
