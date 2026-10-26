<?php

namespace App\Livewire;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use Livewire\Component;

class CourseShow extends Component
{
    public const LEARN_ITEMS = 8;

    public Course $course;

    public function mount(string $slug): void
    {
        $this->course = Course::where('slug', $slug)->where('is_active', true)->with('modules.lessons')->firstOrFail();
    }

    /** Free courses enrol instantly; paid ones go through the shop gateways (admin confirms enrolment). */
    public function enroll()
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }
        if ($this->course->price > 0) {
            $this->dispatch('toast', message: __('courses.paid_contact'));

            return redirect()->route('contact', ['subject' => 'courses']);
        }
        Enrollment::firstOrCreate(['user_id' => auth()->id(), 'course_id' => $this->course->id], ['paid' => true]);
        $this->dispatch('toast', message: __('courses.enrolled'));
    }

    public function render()
    {
        $enrollment = auth()->check() ? Enrollment::where('user_id', auth()->id())->where('course_id', $this->course->id)->first() : null;
        $enrolled = (bool) $enrollment?->paid;
        $lessonIds = $this->course->modules->flatMap->lessons->pluck('id');
        $done = auth()->check()
            ? LessonProgress::where('user_id', auth()->id())->whereNotNull('completed_at')->whereIn('lesson_id', $lessonIds)->pluck('lesson_id')->all()
            : [];
        $resume = $this->course->modules->flatMap->lessons->first(fn ($l) => ! in_array($l->id, $done)) ?? $this->course->modules->flatMap->lessons->first();

        return view('livewire.course-show', [
            'enrolled' => $enrolled,
            'pending' => $enrollment && ! $enrollment->paid,
            'done' => $done,
            'totalLessons' => $lessonIds->count(),
            'resume' => $resume,
            'learn' => $this->course->modules->take(self::LEARN_ITEMS),
            'related' => Course::where('is_active', true)->whereKeyNot($this->course->id)->withCount(['lessons', 'modules'])->orderBy('id')->limit(3)->get(),
        ])->layout('components.layouts.app', ['title' => $this->course->tr('title').' | '.site('name')]);
    }
}
