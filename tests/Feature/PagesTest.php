<?php

namespace Tests\Feature;

use App\Livewire\Contact;
use App\Models\Post;
use App\Support\SafeHtml;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class PagesTest extends TestCase
{
    use RefreshDatabase;

    private function makePost(array $over = []): Post
    {
        return Post::create($over + [
            'title' => ['fr' => 'Bien élever des poussins', 'en' => 'Raising chicks'],
            'slug' => 'bien-elever-des-poussins',
            'excerpt' => ['fr' => 'Les bases.', 'en' => 'The basics.'],
            'body' => ['fr' => '<p>Bonjour <strong>monde</strong></p><script>alert(1)</script><img src="x" onerror="alert(1)"><a href="javascript:alert(1)">x</a>'],
            'category' => 'Conseils',
            'published_at' => now()->subDay(),
            'is_active' => true,
        ]);
    }

    public function test_static_pages_render_in_both_locales(): void
    {
        foreach (['fr', 'en'] as $locale) {
            $this->withSession(['locale' => $locale]);
            foreach (['/a-propos', '/faq', '/cgv', '/confidentialite', '/mentions-legales', '/blog', '/contact'] as $url) {
                $this->get($url)->assertOk();
            }
        }
    }

    public function test_faq_search_filters_and_exposes_json_ld(): void
    {
        $this->get('/faq')->assertSee('FAQPage', false);
        Livewire::test(\App\Livewire\Faq::class)->set('search', 'zzzz-introuvable')->assertSee(__('pages.faq.empty'));
    }

    public function test_blog_index_search_category_and_show_with_json_ld(): void
    {
        $this->makePost();
        $this->makePost(['slug' => 'brouillon', 'title' => ['fr' => 'Brouillon'], 'is_active' => false]);

        Livewire::test(\App\Livewire\BlogIndex::class)->assertSee('Bien élever')->assertDontSee('Brouillon')
            ->set('search', 'inconnu')->assertDontSee('Bien élever')
            ->set('search', '')->set('category', 'Autre')->assertDontSee('Bien élever');

        $this->get('/blog/bien-elever-des-poussins')->assertOk()->assertSee('"@type":"Article"', false)->assertSee('Bonjour');
        $this->get('/blog/brouillon')->assertNotFound();
        $this->get('/blog/inexistant')->assertNotFound();
    }

    public function test_blog_body_is_sanitized(): void
    {
        $html = SafeHtml::clean($this->makePost()->tr('body'));
        $this->assertStringContainsString('<strong>monde</strong>', $html);
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('onerror', $html);
        $this->assertStringNotContainsString('javascript:', $html);
    }

    public function test_contact_honeypot_and_rate_limit(): void
    {
        RateLimiter::clear('contact:127.0.0.1');
        Livewire::test(Contact::class)->set('name', 'Bot')->set('email', 'b@b.co')->set('message', 'spam spam')->set('website', 'http://spam')
            ->call('send')->assertSet('sent', true);
        $this->assertSame(0, RateLimiter::attempts('contact:127.0.0.1'));

        for ($i = 0; $i < 5; $i++) {
            Livewire::test(Contact::class)->set('name', 'Ann')->set('email', 'a@b.co')->set('message', 'Bonjour !')->call('send')->assertSet('sent', true);
        }
        Livewire::test(Contact::class)->set('name', 'Ann')->set('email', 'a@b.co')->set('message', 'Bonjour !')->call('send')
            ->assertSet('sent', false)->assertHasErrors('message');
    }
}
