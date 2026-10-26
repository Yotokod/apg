<?php

namespace Tests\Feature;

use App\Livewire\Gallery;
use App\Livewire\NewsletterSignup;
use App\Models\GalleryItem;
use App\Models\NewsletterSubscriber;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class HomeGalleryTest extends TestCase
{
    use RefreshDatabase;

    private function photo(string $cat, int $pos, bool $featured = false, bool $active = true): GalleryItem
    {
        return GalleryItem::create([
            'title' => ['fr' => "Photo $pos"], 'alt' => ['fr' => "Alt $pos"], 'image' => "gallery/p$pos.jpg",
            'category' => $cat, 'position' => $pos, 'is_featured' => $featured, 'is_active' => $active,
        ]);
    }

    public function test_home_renders_key_sections(): void
    {
        $this->photo('poussins', 1, true);
        Testimonial::create(['name' => 'Awa', 'country' => 'Bénin', 'text' => 'Très bon élevage', 'is_active' => true]);

        $this->get('/accueil')->assertOk()
            ->assertSee(__('home.how'))
            ->assertSee(__('home.faq'))
            ->assertSee(__('home.gallery_all'))
            ->assertSee('Très bon élevage')
            ->assertSee('name="description"', false)
            ->assertSee('property="og:title"', false)
            ->assertSee(__('news.title'))
            ->assertSee(site('email'));
    }

    public function test_home_hides_gallery_when_empty(): void
    {
        $this->get('/accueil')->assertOk()->assertDontSee(__('home.gallery_all'));
    }

    public function test_gallery_page_filters_and_hides_inactive(): void
    {
        $this->photo('poussins', 1);
        $this->photo('oeufs', 2);
        $this->photo('oeufs', 3, false, false);

        $this->get('/galerie')->assertOk()->assertSee('gallery/p1.jpg', false)->assertDontSee('gallery/p3.jpg', false);

        Livewire::test(Gallery::class)
            ->assertSee('gallery/p1.jpg', false)
            ->call('setCat', 'oeufs')
            ->assertSee('gallery/p2.jpg', false)
            ->assertDontSee('gallery/p1.jpg', false)
            ->assertDontSee('gallery/p3.jpg', false);
    }

    public function test_gallery_load_more_by_24(): void
    {
        foreach (range(1, 30) as $n) {
            $this->photo('elevage', $n);
        }

        Livewire::test(Gallery::class)
            ->assertSee('gallery/p24.jpg', false)
            ->assertDontSee('gallery/p25.jpg', false)
            ->call('loadMore')
            ->assertSee('gallery/p30.jpg', false);
    }

    public function test_gallery_empty_state(): void
    {
        $this->get('/galerie')->assertOk()->assertSee(__('gallery.empty'));
    }

    public function test_newsletter_validation_and_uniqueness(): void
    {
        RateLimiter::clear('newsletter:127.0.0.1');

        Livewire::test(NewsletterSignup::class)->set('email', 'pas-un-email')->call('subscribe')->assertHasErrors(['email']);
        Livewire::test(NewsletterSignup::class)->set('email', '')->call('subscribe')->assertHasErrors(['email' => 'required']);

        Livewire::test(NewsletterSignup::class)->set('email', 'Awa@Example.com')->call('subscribe')->assertHasNoErrors()->assertSet('done', true);
        $this->assertDatabaseHas('newsletter_subscribers', ['email' => 'awa@example.com']);

        Livewire::test(NewsletterSignup::class)->set('email', 'awa@example.com')->call('subscribe')->assertHasErrors(['email' => 'unique']);
        $this->assertSame(1, NewsletterSubscriber::count());
    }

    public function test_newsletter_honeypot_and_rate_limit(): void
    {
        RateLimiter::clear('newsletter:127.0.0.1');

        Livewire::test(NewsletterSignup::class)->set('email', 'bot@example.com')->set('website', 'spam')->call('subscribe');
        $this->assertSame(0, NewsletterSubscriber::count());

        RateLimiter::clear('newsletter:127.0.0.1');
        $c = Livewire::test(NewsletterSignup::class);
        foreach (range(1, 6) as $n) {
            $c->set('email', 'bad')->call('subscribe');
        }
        $c->set('email', 'ok@example.com')->call('subscribe')->assertHasErrors(['email']);
        $this->assertSame(0, NewsletterSubscriber::count());
    }
}
