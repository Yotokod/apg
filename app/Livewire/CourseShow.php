<?php

namespace App\Livewire;

use App\Models\Course;
use App\Models\Enrollment;
use Livewire\Component;

class CourseShow extends Component
{
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

            return redirect()->route('contact', ['subject' => $this->course->tr('title')]);
        }
        Enrollment::firstOrCreate(['user_id' => auth()->id(), 'course_id' => $this->course->id], ['paid' => true]);
        $this->dispatch('toast', message: __('courses.enrolled'));
    }

    public function render()
    {
        $enrolled = auth()->check() && Enrollment::where('user_id', auth()->id())
            ->where('course_id', $this->course->id)->where('paid', true)->exists();
        $done = auth()->check()
            ? \App\Models\LessonProgress::where('user_id', auth()->id())->whereNotNull('completed_at')->pluck('lesson_id')->all()
            : [];

        return view('livewire.course-show', compact('enrolled', 'done'))
            ->layout('components.layouts.app', ['title' => $this->course->tr('title')]);
    }
}
