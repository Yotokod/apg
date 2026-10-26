<?php

namespace Tests\Feature;

use App\Livewire\Cart;
use App\Livewire\CourseShow;
use App\Livewire\Courses;
use App\Livewire\LessonPlayer;
use App\Livewire\ProductShow;
use App\Livewire\Shop;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_shop_paginates_twelve_per_page_and_sorts_by_price(): void
    {
        foreach (range(1, 14) as $i) {
            Product::create(['name' => ['fr' => "Extra $i"], 'slug' => "extra-$i", 'category' => 'Extra', 'price' => 100 + $i, 'stock' => 3]);
        }
        $c = Livewire::test(Shop::class);
        $this->assertCount(12, $c->viewData('products'));
        $this->assertGreaterThan(12, $c->viewData('products')->total());

        $asc = Livewire::test(Shop::class)->set('sort', 'price_asc')->viewData('products');
        $this->assertSame($asc->pluck('price')->sort()->values()->all(), $asc->pluck('price')->all());
    }

    public function test_shop_filters_by_category_price_stock_and_search(): void
    {
        Product::create(['name' => ['fr' => 'Œufs frais'], 'slug' => 'oeufs-frais', 'category' => 'Oeufs', 'price' => 2000, 'stock' => 0]);
        Product::create(['name' => ['fr' => 'Plateau'], 'slug' => 'plateau-x', 'category' => 'Oeufs', 'price' => 3000, 'stock' => 5]);

        $slugs = fn ($c) => $c->viewData('products')->pluck('slug')->all();
        $this->assertSame(['plateau-x', 'oeufs-frais'], $slugs(Livewire::test(Shop::class)->set('category', 'Oeufs')->set('sort', 'price_desc')));
        $this->assertSame(['plateau-x'], $slugs(Livewire::test(Shop::class)->set('category', 'Oeufs')->set('inStock', true)));
        $this->assertSame(['oeufs-frais'], $slugs(Livewire::test(Shop::class)->set('category', 'Oeufs')->set('maxPrice', '2500')));
        $this->assertSame(['oeufs-frais'], $slugs(Livewire::test(Shop::class)->set('search', 'Œufs')));
    }

    public function test_shop_clear_filters_and_empty_state(): void
    {
        Livewire::test(Shop::class)->set('search', 'zzzz-introuvable')->assertSee(__('shop.empty'))
            ->call('clearFilters')->assertSet('search', '')->assertDontSee(__('shop.empty'));
    }

    public function test_add_to_cart_ignores_unknown_inactive_and_sold_out(): void
    {
        $inactive = Product::create(['name' => ['fr' => 'X'], 'slug' => 'x', 'price' => 1, 'stock' => 5, 'is_active' => false]);
        $soldOut = Product::create(['name' => ['fr' => 'Y'], 'slug' => 'y', 'price' => 1, 'stock' => 0]);
        $ok = Product::where('is_active', true)->where('stock', '>', 0)->first();

        Livewire::test(Shop::class)->call('add', 99999)->call('add', $inactive->id)->call('add', $soldOut->id)->call('add', $ok->id);
        $this->assertSame([$ok->id => 1], session('cart'));
    }

    public function test_cart_clamps_quantity_and_removes_lines(): void
    {
        [$a, $b] = Product::where('is_active', true)->limit(2)->get();
        session(['cart' => [$a->id => 2, $b->id => 1, 424242 => 3]]);

        $c = Livewire::test(Cart::class);
        $this->assertCount(2, $c->viewData('lines'));
        $this->assertArrayNotHasKey(424242, session('cart'));

        $c->call('setQty', $a->id, 500);
        $this->assertSame(99, session('cart')[$a->id]);
        $c->call('setQty', 31337, 5);
        $this->assertArrayNotHasKey(31337, session('cart'));
        $c->call('qty', $b->id, -1);
        $this->assertArrayNotHasKey($b->id, session('cart'));
        $c->call('remove', $a->id);
        $this->assertSame([], session('cart'));
        $c->assertSee(__('cart.empty'));
    }

    public function test_cart_steps_require_valid_info_before_payment(): void
    {
        $p = Product::first();
        session(['cart' => [$p->id => 1]]);

        Livewire::test(Cart::class)->assertSet('step', 1)
            ->call('goTo', 2)->assertSet('step', 2)
            ->call('goTo', 3)->assertHasErrors(['name', 'phone', 'city'])->assertSet('step', 2)
            ->set('name', 'Koffi')->set('phone', '97000000')->set('city', 'Cotonou')
            ->call('goTo', 3)->assertHasNoErrors()->assertSet('step', 3);
    }

    public function test_product_page_renders_schema_and_whatsapp(): void
    {
        $p = Product::where('is_active', true)->first();
        $p->update(['name' => ['fr' => 'Poulet "Goliath" </script><b>']]);

        $html = $this->get(route('product.show', $p->slug))->assertOk()->getContent();
        $this->assertStringContainsString('application/ld+json', $html);
        $this->assertStringNotContainsString('</script><b>', $html);
        $this->assertStringContainsString('https:\/\/schema.org', str_replace('https://schema.org', 'https:\/\/schema.org', $html));
        $this->assertStringContainsString('https://wa.me/'.site('whatsapp'), $html);
        $this->get('/produit/introuvable')->assertNotFound();
    }

    public function test_product_page_quantity_and_add(): void
    {
        $p = Product::where('is_active', true)->where('stock', '>', 0)->first();
        Livewire::test(ProductShow::class, ['slug' => $p->slug])
            ->set('qty', 500)->assertSet('qty', 99)
            ->set('qty', 0)->assertSet('qty', 1)
            ->call('step', 3)->assertSet('qty', 4)
            ->call('add');
        $this->assertSame(4, session('cart')[$p->id]);
    }

    public function test_courses_catalogue_filters(): void
    {
        $paid = Course::first();
        $paid->update(['price' => 5000]);

        $slugs = fn ($c) => $c->viewData('courses')->pluck('slug')->all();
        $this->assertSame([$paid->slug], $slugs(Livewire::test(Courses::class)->set('price', 'paid')));
        $this->assertNotContains($paid->slug, $slugs(Livewire::test(Courses::class)->set('price', 'free')));
        $big = $slugs(Livewire::test(Courses::class)->set('minModules', 6));
        $this->assertSame(Course::has('modules', '>=', 6)->orderBy('id')->pluck('slug')->all(), $big);
        Livewire::test(Courses::class)->set('search', 'zzzz-introuvable')->assertSee(__('courses.empty'));
    }

    public function test_course_page_states_and_progress(): void
    {
        $course = Course::with('modules.lessons')->first();
        $this->get(route('courses.show', $course->slug))->assertOk()->assertSee(__('catalog.courses.login_to_enroll'));

        $user = User::factory()->create();
        $this->actingAs($user);
        $c = Livewire::test(CourseShow::class, ['slug' => $course->slug])->assertSee(__('catalog.courses.enroll_free'))->call('enroll');
        $this->assertTrue(Enrollment::where('user_id', $user->id)->where('course_id', $course->id)->exists());

        $first = $course->modules->flatMap->lessons->first();
        LessonProgress::create(['user_id' => $user->id, 'lesson_id' => $first->id, 'completed_at' => now()]);
        Livewire::test(CourseShow::class, ['slug' => $course->slug])
            ->assertSee(__('catalog.courses.resume'))
            ->assertViewHas('done', [$first->id]);
    }

    public function test_lesson_player_shows_outline_and_requires_enrolment(): void
    {
        $lesson = Lesson::with('module')->first();
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('lesson', $lesson->id))->assertForbidden();

        Enrollment::create(['user_id' => $user->id, 'course_id' => $lesson->module->course_id, 'paid' => true]);
        Livewire::test(LessonPlayer::class, ['id' => $lesson->id])
            ->assertSee(__('catalog.lesson.outline'))
            ->call('complete')->assertViewHas('done', [$lesson->id]);
    }

    public function test_every_catalog_translation_key_exists_in_both_locales(): void
    {
        $files = collect(['app/Livewire', 'app/Support', 'resources/views/livewire', 'resources/views/components/catalog'])
            ->flatMap(fn ($d) => File::allFiles(base_path($d)))
            ->filter(fn ($f) => str_ends_with($f->getFilename(), 'php'));
        $keys = $files->flatMap(function ($f) {
            preg_match_all("/['\"](catalog\.[a-z0-9_]*[a-z0-9](?:\.[a-z0-9_]*[a-z0-9])+)['\"]/", file_get_contents($f->getPathname()), $m);

            return $m[1];
        })->unique();
        $dynamic = [
            'catalog.badge.new', 'catalog.badge.popular', 'catalog.badge.low', 'catalog.badge.out',
            'catalog.cart.step_cart', 'catalog.cart.step_info', 'catalog.cart.step_payment',
            'catalog.cart.trust_delivery', 'catalog.cart.trust_secure', 'catalog.cart.trust_support',
            'catalog.courses.inc_lessons', 'catalog.courses.inc_quiz', 'catalog.courses.inc_anytime', 'catalog.courses.inc_support',
            'catalog.product.tab_description', 'catalog.product.tab_delivery', 'catalog.product.tab_advice',
            'catalog.product.delivery_air', 'catalog.product.delivery_taxi', 'catalog.product.delivery_transport',
            'catalog.shop.sort_newest', 'catalog.shop.sort_price_asc', 'catalog.shop.sort_price_desc',
            'catalog.shop.view_grid', 'catalog.shop.view_list',
            'catalog.product.advice_1', 'catalog.product.advice_2', 'catalog.product.advice_3', 'catalog.product.advice_4',
            'catalog.courses.faq_q1', 'catalog.courses.faq_q2', 'catalog.courses.faq_q3', 'catalog.courses.faq_q4',
            'catalog.courses.faq_a1', 'catalog.courses.faq_a2', 'catalog.courses.faq_a3', 'catalog.courses.faq_a4',
        ];
        $isPrefix = fn ($k) => collect($dynamic)->contains(fn ($d) => $d !== $k && str_starts_with($d, $k));
        $keys = $keys->reject($isPrefix)->merge($dynamic)->unique();
        $this->assertGreaterThan(30, $keys->count());

        foreach (['fr', 'en'] as $locale) {
            foreach ($keys as $key) {
                $this->assertTrue(trans()->hasForLocale($key, $locale), "Missing $locale translation: $key");
            }
        }
    }
}
