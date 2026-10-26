<?php

namespace App\Livewire;

use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Livewire\Attributes\On;
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
        LessonProgress::firstOrCreate(
            ['user_id' => auth()->id(), 'lesson_id' => $this->lesson->id],
            ['completed_at' => now()],
        )->update(['completed_at' => now()]);
        $this->dispatch('toast', message: __('lesson.done'));
    }

    /** Re-render when the nested quiz records progress. */
    #[On('lesson-progress')]
    public function refreshProgress(): void
    {
    }

    public function render()
    {
        $course = $this->lesson->module->course;
        $modules = $course->modules()->with('lessons')->get();
        $siblings = $modules->flatMap->lessons->values();
        $i = $siblings->search(fn ($l) => $l->id === $this->lesson->id);
        $done = LessonProgress::where('user_id', auth()->id())->whereNotNull('completed_at')
            ->whereIn('lesson_id', $siblings->pluck('id'))->pluck('lesson_id')->all();

        return view('livewire.lesson-player', [
            'course' => $course,
            'modules' => $modules,
            'done' => $done,
            'total' => $siblings->count(),
            'position' => $i + 1,
            'prev' => $i > 0 ? $siblings[$i - 1] : null,
            'next' => $siblings[$i + 1] ?? null,
        ])->layout('components.layouts.app', ['title' => $this->lesson->tr('title')]);
    }
}
