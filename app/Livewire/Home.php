<?php

namespace App\Livewire;

use App\Models\Course;
use App\Models\GalleryItem;
use App\Models\Lesson;
use App\Models\Plan;
use App\Models\Post;
use App\Models\Product;
use App\Models\Testimonial;
use App\Support\Catalog;
use App\Support\SiteData;
use Illuminate\Support\Collection;
use Livewire\Component;

class Home extends Component
{
    public function render()
    {
        $products = Product::where('is_active', true);
        $courses = Course::where('is_active', true);

        return view('livewire.home', [
            'categories' => $this->categories(),
            'products' => (clone $products)->orderByRaw('image is null')->latest()->take(8)->get(),
            'courses' => (clone $courses)->withCount('lessons')->orderBy('id')->take(3)->get(),
            'testimonials' => Testimonial::where('is_active', true)->latest()->take(12)->get(),
            'gallery' => $this->gallery(),
            'posts' => $this->posts(),
            'stats' => [
                ['fa-egg', (clone $products)->count(), 'home.stat1'],
                ['fa-book-open-reader', (clone $courses)->count(), 'home.stat2'],
                ['fa-circle-play', Lesson::count(), 'home.stat3'],
                ['fa-images', GalleryItem::where('is_active', true)->count(), 'home.stat4'],
                ['fa-comments', Testimonial::where('is_active', true)->count(), 'home.stat5'],
            ],
            'plans' => site('app_mode') === 'marketplace' ? Plan::where('is_active', true)->get() : collect(),
        ])->layout('components.layouts.app', ['title' => site('name'), 'description' => __('site.meta_desc')]);
    }

    /** @return list<array{key:string,label:string,count:int,image:?string}> */
    private function categories(): array
    {
        return collect(SiteData::categories())->map(fn ($n, $key) => [
            'key' => $key,
            'label' => Catalog::categoryLabel($key),
            'count' => (int) $n,
            'image' => SiteData::imageUrl(Product::where('is_active', true)->where('category', $key)->whereNotNull('image')->value('image')),
        ])->values()->all();
    }

    private function gallery(): Collection
    {
        $base = GalleryItem::where('is_active', true)->orderBy('position')->orderBy('id');
        $featured = (clone $base)->where('is_featured', true)->take(8)->get();

        return $featured->isNotEmpty() ? $featured : $base->take(8)->get();
    }

    private function posts(): Collection
    {
        if (! SiteData::hasTable('posts')) {
            return collect();
        }

        return Post::where('is_active', true)
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->latest('published_at')->take(3)->get();
    }
}
