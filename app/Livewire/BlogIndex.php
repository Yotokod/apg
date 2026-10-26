<?php

namespace App\Livewire;

use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class BlogIndex extends Component
{
    use WithPagination;

    private const PER_PAGE = 9;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'cat')]
    public string $category = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategory(): void
    {
        $this->resetPage();
    }

    public static function published(): Builder
    {
        return Post::query()->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function render()
    {
        $term = '%'.addcslashes(trim($this->search), '%_\\').'%';
        $posts = self::published()
            ->when($this->category !== '', fn ($q) => $q->where('category', $this->category))
            ->when(trim($this->search) !== '', fn ($q) => $q->where(fn ($w) => $w->where('title', 'like', $term)->orWhere('excerpt', 'like', $term)))
            ->orderByDesc('published_at')->orderByDesc('id')
            ->paginate(self::PER_PAGE);

        return view('livewire.blog-index', [
            'posts' => $posts,
            'categories' => self::published()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category'),
        ])->layout('components.layouts.app', ['title' => __('pages.blog.meta_title').' | '.site('name')]);
    }
}
