<?php

namespace Tests\Feature;

use App\Filament\Pages\SiteSettings;
use App\Models\Setting;
use App\Models\User;
use App\Support\Identity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class IdentitySettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->create(['role' => 'superadmin']); // empty DB otherwise: proves back-compat with no settings/content
        site_flush();
    }

    protected function tearDown(): void
    {
        site_flush();
        parent::tearDown();
    }

    private function setting(string $key, string $value): void
    {
        Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        site_flush();
    }

    private function superadmin(): User
    {
        return User::where('role', 'superadmin')->firstOrFail();
    }

    public function test_site_precedence_locale_key_then_generic_then_config(): void
    {
        $this->assertSame(config('site_identity.name'), site('name'));
        $this->setting('name', 'Generic Co');
        $this->assertSame('Generic Co', site('name'));
        $this->setting('name.fr', 'Société FR');
        $this->assertSame('Société FR', site('name'));
        app()->setLocale('en');
        $this->assertSame('Generic Co', site('name'));
        $this->assertSame('Grow big with the Goliath chicken', site('tagline'));
    }

    public function test_file_keys_return_urls_and_flush_refreshes_memo(): void
    {
        $this->setting('logo', 'identity/logo.png');
        $this->assertSame(asset('storage/identity/logo.png'), site('logo'));
        $this->assertSame('/images/favicon.png', site('favicon', '/images/favicon.png'));

        Setting::create(['key' => 'currency', 'value' => 'EUR']);
        $this->assertSame('FCFA', site('currency'));
        site_flush();
        $this->assertSame('EUR', site('currency'));
    }

    public function test_only_superadmin_can_open_and_save_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get('/admin/site-settings')->assertForbidden();
        Livewire::actingAs($admin)->test(SiteSettings::class)->assertForbidden();

        $this->actingAs($this->superadmin())->get('/admin/site-settings')->assertOk()->assertSee('Pages légales')->assertSee('Certificat');
        Livewire::actingAs($this->superadmin())->test(SiteSettings::class)
            ->fillForm(['cities' => 'Cotonou, Lomé', 'primary_color' => '#123456'])
            ->call('save')->assertHasNoFormErrors();
        $this->assertSame('Cotonou, Lomé', Setting::where('key', 'cities')->value('value'));
    }

    public function test_saving_empty_value_removes_override(): void
    {
        $this->setting('cities', 'Ville test');
        Livewire::actingAs($this->superadmin())->test(SiteSettings::class)
            ->assertSet('data.cities', 'Ville test')
            ->fillForm(['cities' => ''])->call('save')->assertHasNoFormErrors();

        $this->assertDatabaseMissing('settings', ['key' => 'cities']);
        $this->assertSame(config('site_identity.cities'), site('cities'));
    }

    public function test_invalid_values_are_rejected(): void
    {
        Livewire::actingAs($this->superadmin())->test(SiteSettings::class)
            ->fillForm(['email' => 'not-an-email', 'primary_color' => 'red', 'socials' => ['youtube' => 'javascript:alert(1)']])
            ->call('save')->assertHasFormErrors(['email', 'primary_color', 'socials.youtube']);
        $this->assertDatabaseMissing('settings', ['key' => 'email']);
    }

    public function test_ai_key_is_encrypted_never_echoed_and_blank_keeps_it(): void
    {
        $page = Livewire::actingAs($this->superadmin())->test(SiteSettings::class)
            ->fillForm(['ai_api_key' => 'sk-ant-secret-123'])->call('save')->assertHasNoFormErrors()
            ->assertSet('data.ai_api_key', '');
        $stored = Setting::where('key', 'ai_api_key')->value('value');
        $this->assertNotSame('sk-ant-secret-123', $stored);
        $this->assertSame('sk-ant-secret-123', Crypt::decryptString($stored));
        $this->assertStringNotContainsString('sk-ant-secret-123', $page->html());

        $page->fillForm(['ai_api_key' => ''])->call('save');
        $this->assertSame($stored, Setting::where('key', 'ai_api_key')->value('value'));
        $this->assertArrayNotHasKey('ai_api_key', Identity::export()['settings']);
    }

    public function test_legal_override_is_sanitized_with_locale_fallback_and_default_shows_draft_notice(): void
    {
        $this->get('/cgv')->assertOk()->assertSee('data-legal-draft', false)->assertSee(__('pages.legal.cgv.sections.0.title'));

        $this->setting('legal.cgv.fr', '<h2>Mes CGV maison</h2><script>alert("xss")</script><p onclick="steal()">Vendu par :name</p><a href="javascript:evil()">x</a>');
        $html = $this->get('/cgv')->assertOk()->getContent();
        $this->assertStringContainsString('Mes CGV maison', $html);
        $this->assertStringContainsString('Vendu par '.e(site('name')), $html);
        $this->assertStringNotContainsString('alert("xss")', $html);
        $this->assertStringNotContainsString('steal()', $html);
        $this->assertStringNotContainsString('javascript:evil', $html);
        $this->assertStringNotContainsString('data-legal-draft', $html);

        $this->withSession(['locale' => 'en'])->get('/cgv')->assertOk()->assertSee('Mes CGV maison');
        $this->get('/confidentialite')->assertOk()->assertSee('data-legal-draft', false);
    }

    public function test_homepage_reflects_announcement_theme_and_favicon(): void
    {
        $plain = $this->get('/accueil')->assertOk()->getContent();
        $this->assertStringNotContainsString('theme-overrides', $plain);
        $this->assertStringContainsString(__('site.ann1'), $plain);

        $this->setting('announcement.text.fr', 'Promo spéciale Tabaski');
        $this->setting('announcement.link', '/boutique');
        $this->setting('primary_color', '#1D4ED8');
        $this->setting('favicon', 'identity/fav.png');
        $this->setting('seo.ga_id', 'G-ABC123XYZ');

        $html = $this->get('/accueil')->assertOk()->getContent();
        $this->assertStringContainsString('Promo spéciale Tabaski', $html);
        $this->assertStringContainsString('href="/boutique"', $html);
        $this->assertStringContainsString('--color-brand-600:#1d4ed8', $html);
        $this->assertStringContainsString('storage/identity/fav.png', $html);
        $this->assertStringContainsString('googletagmanager.com/gtag/js?id=G-ABC123XYZ', $html);

        $this->setting('announcement.enabled', '0');
        $this->assertStringNotContainsString('data-announcement', $this->get('/accueil')->getContent());
    }

    public function test_maintenance_blocks_visitors_but_not_admins_or_login(): void
    {
        $this->setting('maintenance.enabled', '1');
        $this->setting('maintenance.message.fr', 'Retour lundi matin');

        $this->get('/accueil')->assertStatus(503)->assertSee('Retour lundi matin')->assertSee('data-maintenance', false);
        $this->get('/admin/login')->assertOk();
        $this->get('/connexion')->assertOk();
        $this->actingAs($this->superadmin())->get('/accueil')->assertOk();
    }

    public function test_import_rejects_unknown_and_secret_keys_and_applies_valid_ones(): void
    {
        foreach ([['evil_key' => 'x'], ['ai_api_key' => 'sk-ant-x'], ['email' => 'nope']] as $bad) {
            try {
                Identity::import(['settings' => $bad]);
                $this->fail('Import should have been refused: '.json_encode($bad));
            } catch (ValidationException) {
                $this->assertDatabaseMissing('settings', ['key' => array_key_first($bad)]);
            }
        }

        $count = Identity::import(['format' => 'apg-identity', 'version' => 1, 'settings' => ['cities' => 'Abomey', 'announcement.enabled' => false]]);
        $this->assertSame(2, $count);
        $this->assertSame('Abomey', site('cities'));
        $this->assertFalse(Identity::enabled('announcement.enabled'));
    }

    public function test_uploads_reject_svg_and_accept_png(): void
    {
        Storage::fake('public');
        $svg = UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');

        Livewire::actingAs($this->superadmin())->test(SiteSettings::class)
            ->fillForm(['logo' => $svg])->call('save')->assertHasFormErrors(['logo']);
        $this->assertDatabaseMissing('settings', ['key' => 'logo']);

        Livewire::actingAs($this->superadmin())->test(SiteSettings::class)
            ->fillForm(['logo' => UploadedFile::fake()->image('logo.png', 200, 80)])->call('save')->assertHasNoFormErrors();
        $path = Setting::where('key', 'logo')->value('value');
        $this->assertStringStartsWith('identity/', $path);
        Storage::disk('public')->assertExists($path);
    }
}
