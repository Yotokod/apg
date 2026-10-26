<?php

namespace App\Livewire;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Product;
use App\Models\Testimonial;
use Livewire\Component;

class About extends Component
{
    /** Real counts only; zero values are hidden in the view. */
    private function stats(): array
    {
        return [
            ['fa-box-open', Product::where('is_active', true)->count(), 'pages.about.stats_products'],
            ['fa-graduation-cap', Course::where('is_active', true)->count(), 'pages.about.stats_courses'],
            ['fa-circle-play', Lesson::count(), 'pages.about.stats_lessons'],
            ['fa-star', Testimonial::where('is_active', true)->count(), 'pages.about.stats_reviews'],
        ];
    }

    public function render()
    {
        return view('livewire.about', [
            'stats' => array_filter($this->stats(), fn ($s) => $s[1] > 0),
            'cities' => array_map('trim', explode(',', (string) site('cities'))),
        ])->layout('components.layouts.app', ['title' => __('pages.about.meta_title').' | '.site('name')]);
    }
}
