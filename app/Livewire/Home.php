<?php

namespace App\Livewire;

use App\Models\Course;
use App\Models\Plan;
use App\Models\Product;
use Livewire\Component;

class Home extends Component
{
    public function render()
    {
        return view('livewire.home', [
            'products' => Product::where('is_active', true)->latest()->take(4)->get(),
            'courses' => Course::where('is_active', true)->take(3)->get(),
            'plans' => site('app_mode') === 'marketplace' ? Plan::where('is_active', true)->get() : collect(),
        ])->layout('components.layouts.app', ['title' => site('name')]);
    }
}
