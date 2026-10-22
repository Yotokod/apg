<?php

namespace App\Livewire;

use App\Models\Course;
use Livewire\Component;

class Courses extends Component
{
    public function render()
    {
        return view('livewire.courses', [
            'courses' => Course::where('is_active', true)->withCount('lessons')->get(),
        ])->layout('components.layouts.app', ['title' => __('nav.courses').' | '.site('name')]);
    }
}
