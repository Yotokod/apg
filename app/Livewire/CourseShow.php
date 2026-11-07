<?php

namespace App\Livewire;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use App\Services\CertificateService;
use Livewire\Component;

class CourseShow extends Component
{
    public const LEARN_ITEMS = 8;

    public Course $course;

    public string $certName = '';

    public function mount(string $slug): void
    {
        $this->course = Course::where('slug', $slug)->where('is_active', true)->with('modules.lessons')->firstOrFail();
        $this->certName = (string) auth()->user()?->name;
    }

    /** Learner claims the certificate (name editable before issuance). */
    public function claimCertificate(CertificateService $certificates): void
    {
        abort_unless(auth()->check(), 403);
        $this->validate(['certName' => CertificateService::NAME_RULES]);
        $certificate = $certificates->issueIfEligible(auth()->user(), $this->course, $this->certName);
        $this->dispatch('toast', message: __($certificate ? 'certificate.issued_toast' : 'certificate.not_eligible'));
    }

    /** One-time name correction after issuance. */
    public function renameCertificate(CertificateService $certificates): void
    {
        abort_unless(auth()->check(), 403);
        $this->validate(['certName' => CertificateService::NAME_RULES]);
        $certificate = $certificates->find(auth()->user(), $this->course);
        if (! $certificate || ! $certificates->rename($certificate, $this->certName)) {
            $this->addError('certName', __('certificate.rename_locked'));

            return;
        }
        $this->dispatch('toast', message: __('certificate.renamed'));
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
        $certificates = app(CertificateService::class);
        $certificate = $enrolled ? $certificates->find(auth()->user(), $this->course) : null;

        return view('livewire.course-show', [
            'enrolled' => $enrolled,
            'pending' => $enrollment && ! $enrollment->paid,
            'done' => $done,
            'totalLessons' => $lessonIds->count(),
            'resume' => $resume,
            'certificate' => $certificate,
            'canClaim' => $enrolled && ! $certificate && $certificates->isEligible(auth()->user(), $this->course),
            'learn' => $this->course->modules->take(self::LEARN_ITEMS),
            'related' => Course::where('is_active', true)->whereKeyNot($this->course->id)->withCount(['lessons', 'modules'])->orderBy('id')->limit(3)->get(),
        ])->layout('components.layouts.app', ['title' => $this->course->tr('title').' | '.site('name')]);
    }
}
