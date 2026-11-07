<?php

namespace Tests\Feature;

use App\Filament\Resources\Certificates\Pages\ManageCertificates;
use App\Livewire\CourseShow;
use App\Livewire\LessonPlayer;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Setting;
use App\Models\User;
use App\Services\CertificateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Livewire\Livewire;
use Tests\TestCase;

class CertificateTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Course $course;

    /** @var Collection<int, Lesson> */
    private Collection $lessons;

    protected function setUp(): void
    {
        parent::setUp();
        site_flush();
        $this->course = Course::create(['title' => ['fr' => 'Élevage Goliath', 'en' => 'Goliath farming'], 'slug' => 'elevage-goliath']);
        $this->lessons = $this->lessonsFor($this->course, 3);
        $this->user = User::factory()->create(['name' => 'Koffi Mensah']);
        Enrollment::create(['user_id' => $this->user->id, 'course_id' => $this->course->id, 'paid' => true]);
    }

    protected function tearDown(): void
    {
        site_flush();
        parent::tearDown();
    }

    private function lessonsFor(Course $course, int $n): Collection
    {
        $module = $course->modules()->create(['title' => ['fr' => 'Module'], 'position' => 0]);

        return collect(range(1, $n))->map(fn ($i) => $module->lessons()->create(['title' => ['fr' => "Leçon $i"], 'position' => $i]));
    }

    private function complete(iterable $lessons, ?User $user = null): void
    {
        foreach ($lessons as $lesson) {
            LessonProgress::updateOrCreate(['user_id' => ($user ?? $this->user)->id, 'lesson_id' => $lesson->id], ['completed_at' => now()]);
        }
    }

    private function settings(array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        site_flush();
    }

    private function issue(): Certificate
    {
        $this->complete($this->lessons);

        return app(CertificateService::class)->issueIfEligible($this->user, $this->course);
    }

    public function test_not_eligible_at_99_percent_but_eligible_at_100(): void
    {
        $course = Course::create(['title' => ['fr' => 'Long'], 'slug' => 'long']);
        $lessons = $this->lessonsFor($course, 100);
        Enrollment::create(['user_id' => $this->user->id, 'course_id' => $course->id, 'paid' => true]);
        $service = app(CertificateService::class);

        $this->complete($lessons->take(99));
        $this->assertSame(99, $service->progress($this->user, $course));
        $this->assertFalse($service->isEligible($this->user, $course));
        $this->assertNull($service->issueIfEligible($this->user, $course));

        $this->complete($lessons->slice(99));
        $this->assertTrue($service->isEligible($this->user, $course));
        $this->assertNotNull($service->issueIfEligible($this->user, $course));
    }

    public function test_min_progress_and_disable_settings(): void
    {
        $service = app(CertificateService::class);
        $this->complete($this->lessons->take(2)); // 66 %
        $this->assertFalse($service->isEligible($this->user, $this->course));

        $this->settings(['cert.min_progress' => '60']);
        $this->assertTrue($service->isEligible($this->user, $this->course));

        $this->settings(['cert.enabled' => '0']);
        $this->assertFalse($service->isEligible($this->user, $this->course));
    }

    public function test_requires_paid_enrollment(): void
    {
        $other = User::factory()->create();
        $this->complete($this->lessons, $other);
        $this->assertNull(app(CertificateService::class)->issueIfEligible($other, $this->course));
    }

    public function test_auto_issue_on_last_lesson_completion_and_idempotent(): void
    {
        $this->complete($this->lessons->take(2));
        Livewire::actingAs($this->user)->test(LessonPlayer::class, ['id' => $this->lessons[0]->id])->call('complete');
        $this->assertSame(0, Certificate::count());

        Livewire::actingAs($this->user)->test(LessonPlayer::class, ['id' => $this->lessons[2]->id])
            ->call('complete')->assertDispatched('toast', message: __('certificate.issued_toast'));
        $certificate = Certificate::sole();
        $this->assertSame('Koffi Mensah', $certificate->student_name);
        $this->assertMatchesRegularExpression('/^APG-\d{4}-\d{6}$/', $certificate->number);
        $this->assertMatchesRegularExpression('/^[A-Z2-9]{12}$/', $certificate->code);
        $this->assertSame(['fr' => 'Élevage Goliath', 'en' => 'Goliath farming'], $certificate->course_title);

        Livewire::actingAs($this->user)->test(LessonPlayer::class, ['id' => $this->lessons[2]->id])->call('complete');
        $again = app(CertificateService::class)->issueIfEligible($this->user, $this->course);
        $this->assertSame(1, Certificate::count());
        $this->assertTrue($again->is($certificate));
    }

    public function test_number_prefix_setting(): void
    {
        $this->settings(['cert.number_prefix' => 'GOL']);
        $this->assertStringStartsWith('GOL-'.now()->year.'-', $this->issue()->number);
    }

    public function test_public_page_shows_certificate_with_signature_and_stamp(): void
    {
        $this->settings([
            'org.legal_name' => 'APG SARL', 'org.director_name' => 'Jean Goliath', 'org.director_title.fr' => 'Fondateur',
            'org.signature' => 'identity/signature.png', 'org.stamp' => 'identity/stamp.png',
        ]);
        $certificate = $this->issue();

        $this->get('/certificat/'.$certificate->code)->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertSee('name="robots" content="noindex, nofollow"', false)
            ->assertSee('Koffi Mensah')->assertSee('Élevage Goliath')->assertSee($certificate->number)
            ->assertSee('APG SARL')->assertSee('Jean Goliath')->assertSee('Fondateur')
            ->assertSee('storage/identity/signature.png', false)->assertSee('storage/identity/stamp.png', false)
            ->assertSee(__('certificate.valid'))->assertSee($certificate->shortCode())
            ->assertSee('size: A4 landscape', false);

        $this->get('/certificat/'.strtolower($certificate->code))->assertOk();
    }

    public function test_page_falls_back_to_defaults_without_identity_settings(): void
    {
        $certificate = $this->issue();
        $this->get($certificate->url())->assertOk()
            ->assertSee(__('certificate.title'))->assertSee(site('name'))->assertSee(__('certificate.director_default_title'))
            ->assertDontSee('class="signature"', false)->assertDontSee('class="stamp"', false);
    }

    public function test_revoked_certificate_is_invalid_and_unsigned(): void
    {
        $this->settings(['org.signature' => 'identity/signature.png', 'org.stamp' => 'identity/stamp.png']);
        $certificate = $this->issue();
        $certificate->update(['revoked_at' => now()]);

        $this->get($certificate->url())->assertOk()
            ->assertSee(__('certificate.revoked_banner'))->assertSee(__('certificate.revoked'))
            ->assertDontSee('storage/identity/signature.png', false)->assertDontSee('storage/identity/stamp.png', false);
    }

    public function test_unknown_or_malformed_codes_and_no_data_leak(): void
    {
        $mine = $this->issue();
        $other = User::factory()->create(['name' => 'Autre Élève', 'email' => 'secret@example.com']);
        Enrollment::create(['user_id' => $other->id, 'course_id' => $this->course->id, 'paid' => true]);
        $this->complete($this->lessons, $other);
        app(CertificateService::class)->issueIfEligible($other, $this->course);

        $this->get('/certificat/ABCDEFGHJKLM')->assertNotFound();
        $this->get('/certificat/'.$mine->id)->assertNotFound();
        $this->get('/certificat/'.$mine->number)->assertNotFound();
        $this->get($mine->url())->assertOk()->assertDontSee('Autre Élève')->assertDontSee('secret@example.com')->assertDontSee($this->user->email);

        $this->actingAs($this->user)->get('/compte')->assertOk()->assertSee($mine->number)->assertDontSee('Autre Élève')
            ->assertSee(__('certificate.mine'))->assertSee(__('certificate.view'));
    }

    public function test_lookups_are_throttled(): void
    {
        foreach (range(1, 30) as $i) {
            $this->get('/certificat/ABCDEFGHJKLM')->assertNotFound();
        }
        $this->get('/certificat/ABCDEFGHJKLM')->assertStatus(429);
    }

    public function test_course_page_claim_with_custom_name_and_single_rename(): void
    {
        $this->settings(['cert.enabled' => '0']);
        $this->complete($this->lessons);
        $this->settings(['cert.enabled' => '1']);

        $page = Livewire::actingAs($this->user)->test(CourseShow::class, ['slug' => $this->course->slug])
            ->assertSee(__('certificate.get'))->assertSet('certName', 'Koffi Mensah');

        $page->set('certName', 'K')->call('claimCertificate')->assertHasErrors(['certName' => 'min']);
        $page->set('certName', str_repeat('a', 101))->call('claimCertificate')->assertHasErrors(['certName' => 'max']);
        $page->set('certName', '<script>')->call('claimCertificate')->assertHasErrors(['certName' => 'regex']);
        $this->assertSame(0, Certificate::count());

        $page->set('certName', 'Koffi A. Mensah')->call('claimCertificate')->assertHasNoErrors()
            ->assertSee(__('certificate.view'));
        $certificate = Certificate::sole();
        $this->assertSame('Koffi A. Mensah', $certificate->student_name);

        $page->set('certName', 'Koffi Adébayo Mensah')->call('renameCertificate')->assertHasNoErrors();
        $this->assertSame('Koffi Adébayo Mensah', $certificate->fresh()->student_name);

        $page->set('certName', 'Autre Nom')->call('renameCertificate')->assertHasErrors('certName');
        $this->assertSame('Koffi Adébayo Mensah', $certificate->fresh()->student_name);
    }

    public function test_guest_and_unenrolled_cannot_claim(): void
    {
        Livewire::test(CourseShow::class, ['slug' => $this->course->slug])->assertDontSee(__('certificate.get'))
            ->call('claimCertificate')->assertForbidden();
    }

    public function test_admin_resource_access_actions_and_preview(): void
    {
        $certificate = $this->issue();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($this->user)->get('/admin/certificates')->assertForbidden();
        $this->actingAs($this->user)->get('/certificat-apercu')->assertForbidden();
        $this->actingAs($admin)->get('/admin/certificates')->assertOk()->assertSee($certificate->number);

        Livewire::actingAs($admin)->test(ManageCertificates::class)
            ->callTableAction('revoke', $certificate);
        $this->assertTrue($certificate->fresh()->isRevoked());

        Livewire::actingAs($admin)->test(ManageCertificates::class)
            ->callTableAction('reissue', $certificate, data: ['student_name' => 'Koffi Corrigé']);
        $this->assertSame('Koffi Corrigé', $certificate->fresh()->student_name);
        $this->assertFalse($certificate->fresh()->isRevoked());

        $this->actingAs($admin)->get('/certificat-apercu')->assertOk()
            ->assertSee(__('certificate.specimen'))->assertSee(__('certificate.specimen_banner'));
        $this->assertSame(1, Certificate::count(), 'preview never stores a certificate');
    }
}
