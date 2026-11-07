<?php

namespace App\Livewire;

use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Services\CertificateService;
use Livewire\Attributes\On;
use Livewire\Component;

class LessonPlayer extends Component
{
    /** Scene key => Blade component rendered by <x-lesson.scene> (admin labels: lesson.scene_keys.*). */
    public const SCENES = [
        'temperature' => 'lesson.scenes.temperature',
        'feed-calc' => 'lesson.scenes.feed-calc',
        'density' => 'lesson.scenes.density',
        'biosecurity' => 'lesson.scenes.biosecurity',
        'hatch-cycle' => 'lesson.scenes.hatch-cycle',
        'laying-cycle' => 'lesson.scenes.laying-cycle',
        'feeding' => 'illustrations.feeding',
        'chick-growth' => 'illustrations.chick-growth',
        'egg-hatch' => 'illustrations.egg-hatch',
        'farm-house' => 'illustrations.farm-house',
        'incubator' => 'illustrations.incubator',
        'vaccination' => 'illustrations.vaccination',
        'delivery' => 'illustrations.delivery',
        'profit-chart' => 'illustrations.profit-chart',
        'hero-farm' => 'illustrations.hero-farm',
    ];

    private const DEFAULT_SCENE_SECONDS = 8;

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
        $this->issueCertificate();
    }

    private function issueCertificate(): void
    {
        $certificate = app(CertificateService::class)->issueIfEligible(auth()->user(), $this->lesson->module->course);
        if ($certificate?->wasRecentlyCreated) {
            $this->dispatch('toast', message: __('certificate.issued_toast'));
        }
    }

    /** Re-render when the nested quiz records progress. */
    #[On('lesson-progress')]
    public function refreshProgress(): void
    {
    }

    /** Localised explainer steps from the lessons.scenes JSON column. */
    private function sceneSteps(): array
    {
        $locale = app()->getLocale();
        $pick = fn ($v) => is_array($v) ? ($v[$locale] ?? $v['fr'] ?? '') : (string) $v;

        return collect($this->lesson->scenes ?? [])
            ->filter(fn ($s) => is_array($s) && ($pick($s['title'] ?? '') !== '' || $pick($s['caption'] ?? '') !== ''))
            ->map(fn (array $s) => [
                'illustration' => $s['illustration'] ?? null,
                'icon' => $s['icon'] ?? null,
                'title' => $pick($s['title'] ?? ''),
                'caption' => $pick($s['caption'] ?? ''),
                'points' => array_values(array_filter((array) (($s['points'][$locale] ?? null) ?: ($s['points']['fr'] ?? [])))),
                'duration' => max(3, min(30, (int) ($s['duration'] ?? self::DEFAULT_SCENE_SECONDS))),
            ])->values()->all();
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
            'scenes' => $this->sceneSteps(),
        ])->layout('components.layouts.app', ['title' => $this->lesson->tr('title')]);
    }
}
