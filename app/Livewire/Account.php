<?php

namespace App\Livewire;

use App\Models\Enrollment;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Account extends Component
{
    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('home');
    }

    public function render()
    {
        return view('livewire.account', [
            'enrollments' => Enrollment::with('course')->where('user_id', auth()->id())->get(),
            'orders' => Order::where('user_id', auth()->id())->latest()->take(10)->get(),
        ])->layout('components.layouts.app', ['title' => __('nav.account')]);
    }
}
