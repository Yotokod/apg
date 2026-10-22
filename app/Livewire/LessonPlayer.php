<?php

namespace App\Livewire;

use App\Models\Enrollment;
use App\Models\Lesson;
use Livewire\Component;

class LessonPlayer extends Component
{
    public Lesson $lesson;

    public function mount(int $id): void
    {
        $this->lesson = Lesson::with('module.course', 'nodes')->findOrFail($id);
        abort_unless(auth()->check() && Enrollment::where('user_id', auth()->id())
            ->where('course_id', $this->lesson->module->course_id)->where('paid', true)->exists(), 403);
    }

    public function complete(): void
    {
        \App\Models\LessonProgress::firstOrCreate(
            ['user_id' => auth()->id(), 'lesson_id' => $this->lesson->id],
            ['completed_at' => now()],
        )->update(['completed_at' => now()]);
        $this->dispatch('toast', message: __('lesson.done'));
    }

    public function render()
    {
        $siblings = $this->lesson->module->course->lessons()->orderBy('course_modules.position')->orderBy('lessons.position')->get();
        $i = $siblings->search(fn ($l) => $l->id === $this->lesson->id);

        return view('livewire.lesson-player', [
            'prev' => $siblings[$i - 1] ?? null,
            'next' => $siblings[$i + 1] ?? null,
        ])->layout('components.layouts.app', ['title' => $this->lesson->tr('title')]);
    }
}
