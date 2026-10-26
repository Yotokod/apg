<?php

namespace App\Livewire;

use App\Models\GalleryItem;
use Livewire\Attributes\Url;
use Livewire\Component;

class Gallery extends Component
{
    private const PAGE = 24;

    #[Url(as: 'cat', except: 'all')]
    public string $cat = 'all';

    public int $limit = self::PAGE;

    public function setCat(string $cat): void
    {
        $this->cat = $cat;
        $this->limit = self::PAGE;
    }

    public function loadMore(): void
    {
        $this->limit += self::PAGE;
    }

    public function render()
    {
        $counts = GalleryItem::where('is_active', true)->selectRaw('category, count(*) as n')->groupBy('category')->pluck('n', 'category')->all();
        $cat = $this->cat === 'all' || isset($counts[$this->cat]) ? $this->cat : 'all';

        $query = GalleryItem::where('is_active', true)
            ->when($cat !== 'all', fn ($q) => $q->where('category', $cat))
            ->orderBy('position')->orderByDesc('is_featured')->orderBy('id');
        $total = (clone $query)->count();

        return view('livewire.gallery', [
            'items' => $query->take($this->limit)->get(),
            'counts' => $counts,
            'allCount' => array_sum($counts),
            'total' => $total,
            'activeCat' => $cat,
        ])->layout('components.layouts.app', [
            'title' => __('gallery.title').' | '.site('name'),
            'description' => __('gallery.subtitle'),
        ]);
    }
}
