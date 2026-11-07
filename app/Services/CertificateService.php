<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Course completion certificates. Settings (site()): cert.enabled ('0' disables),
 * cert.min_progress (percent of completed lessons, default 100), cert.number_prefix (default APG).
 */
class CertificateService
{
    public const NAME_RULES = ['required', 'string', 'min:2', 'max:100', "regex:/^[\pL\pM\s.'’-]+$/u"];

    private const CODE_ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    private const CODE_LENGTH = 12;

    public function isEnabled(): bool
    {
        return (string) site('cert.enabled', '1') !== '0';
    }

    public function minProgress(): int
    {
        return max(1, min(100, (int) (site('cert.min_progress') ?: 100)));
    }

    /** Completed lessons in percent, rounded down (99.9 % never counts as 100 %). */
    public function progress(User $user, Course $course): int
    {
        $lessonIds = $course->lessons()->pluck('lessons.id');
        if ($lessonIds->isEmpty()) {
            return 0;
        }
        $done = LessonProgress::where('user_id', $user->id)->whereNotNull('completed_at')->whereIn('lesson_id', $lessonIds)->count();

        return intdiv($done * 100, $lessonIds->count());
    }

    public function isEligible(User $user, Course $course): bool
    {
        return $this->isEnabled()
            && Enrollment::where('user_id', $user->id)->where('course_id', $course->id)->where('paid', true)->exists()
            && $this->progress($user, $course) >= $this->minProgress();
    }

    public function find(User $user, Course $course): ?Certificate
    {
        return Certificate::where('user_id', $user->id)->where('course_id', $course->id)->first();
    }

    /** Idempotent: returns the existing certificate, a new one when eligible, or null. */
    public function issueIfEligible(User $user, Course $course, ?string $studentName = null): ?Certificate
    {
        if ($existing = $this->find($user, $course)) {
            return $existing;
        }
        if (! $this->isEligible($user, $course)) {
            return null;
        }

        try {
            return DB::transaction(fn () => $this->create($user, $course, $studentName));
        } catch (UniqueConstraintViolationException) {
            return $this->find($user, $course);
        }
    }

    /** Learner correction: allowed once, never on a revoked certificate. */
    public function rename(Certificate $certificate, string $name): bool
    {
        if (! $certificate->canBeRenamed()) {
            return false;
        }

        return $certificate->update(['student_name' => $this->cleanName($name), 'renamed_at' => now()]);
    }

    /** Admin re-issue with a corrected name: same number/code, valid again. */
    public function reissue(Certificate $certificate, string $name): void
    {
        $certificate->update(['student_name' => $this->cleanName($name), 'revoked_at' => null]);
    }

    /** Unsaved certificate used by the admin preview (rendered with a SPECIMEN watermark). */
    public function specimen(): Certificate
    {
        return new Certificate([
            'number' => $this->prefix().'-'.now()->year.'-000000',
            'code' => 'SPECIMEN0000',
            'student_name' => __('certificate.specimen_name'),
            'course_title' => ['fr' => __('certificate.specimen_course', [], 'fr'), 'en' => __('certificate.specimen_course', [], 'en')],
            'hours' => 12,
            'issued_at' => now(),
        ]);
    }

    private function create(User $user, Course $course, ?string $studentName): Certificate
    {
        $code = $this->uniqueCode();
        $certificate = Certificate::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'number' => 'TMP-'.$code,
            'code' => $code,
            'student_name' => $this->cleanName($studentName ?: $user->name),
            'course_title' => $course->title,
            'hours' => $this->hours($course),
            'score' => $this->score($user, $course),
            'issued_at' => now(),
        ]);
        $certificate->update(['number' => sprintf('%s-%d-%06d', $this->prefix(), $certificate->issued_at->year, $certificate->id)]);

        return $certificate;
    }

    private function prefix(): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) site('cert.number_prefix')) ?: 'APG');
    }

    private function uniqueCode(): string
    {
        do {
            $code = '';
            for ($i = 0; $i < self::CODE_LENGTH; $i++) {
                $code .= self::CODE_ALPHABET[random_int(0, strlen(self::CODE_ALPHABET) - 1)];
            }
        } while (Certificate::where('code', $code)->exists());

        return $code;
    }

    private function cleanName(string $name): string
    {
        return mb_substr(trim(preg_replace('/\s+/u', ' ', $name)), 0, 100);
    }

    /** Sum of lesson durations when the lessons table has a "minutes" column, else unknown. */
    private function hours(Course $course): ?int
    {
        if (! Schema::hasColumn('lessons', 'minutes')) {
            return null;
        }
        $minutes = (int) $course->lessons()->sum('lessons.minutes');

        return $minutes > 0 ? (int) ceil($minutes / 60) : null;
    }

    /** Average quiz score of the course (lessons with a recorded score), else null. */
    private function score(User $user, Course $course): ?int
    {
        $avg = LessonProgress::where('user_id', $user->id)->where('score', '>', 0)
            ->whereIn('lesson_id', $course->lessons()->pluck('lessons.id'))->avg('score');

        return $avg === null ? null : (int) round($avg);
    }
}
