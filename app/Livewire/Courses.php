<?php

namespace App\Livewire;

use App\Models\Course;
use App\Models\Enrollment;
use App\Support\Catalog;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Courses extends Component
{
    use WithPagination;

    public const PER_PAGE = 9;
    public const PRICES = ['', 'free', 'paid'];
    public const MODULE_STEPS = [0, 4, 6];

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $price = '';

    #[Url(as: 'modules')]
    public int $minModules = 0;

    public function updated(string $name): void
    {
        if ($name !== 'page') {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'price', 'minModules');
        $this->resetPage();
    }

    public function render()
    {
        $minModules = in_array($this->minModules, self::MODULE_STEPS, true) ? $this->minModules : 0;
        $price = in_array($this->price, self::PRICES, true) ? $this->price : '';

        $courses = Course::where('is_active', true)->withCount(['lessons', 'modules'])
            ->tap(fn ($q) => Catalog::whereTranslatedLike($q, ['title', 'description'], $this->search))
            ->when($price === 'free', fn ($q) => $q->where('price', 0))
            ->when($price === 'paid', fn ($q) => $q->where('price', '>', 0))
            ->when($minModules > 0, fn ($q) => $q->has('modules', '>=', $minModules))
            ->orderBy('id')->paginate(self::PER_PAGE);

        return view('livewire.courses', [
            'courses' => $courses,
            'enrolled' => auth()->check() ? Enrollment::where('user_id', auth()->id())->where('paid', true)->pluck('course_id')->all() : [],
            'activeFilters' => collect([$this->search, $price, $minModules ?: ''])->filter(fn ($v) => $v !== '')->count(),
        ])->layout('components.layouts.app', ['title' => __('nav.courses').' | '.site('name')]);
    }
}
