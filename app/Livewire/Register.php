<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Register extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $password = '';

    public function register()
    {
        $data = $this->validate([
            'name' => 'required|min:2', 'email' => 'required|email|unique:users,email',
            'phone' => 'nullable', 'password' => 'required|min:8',
        ]);
        Auth::login(User::create($data + ['role' => 'customer']), true);
        session()->regenerate();

        return redirect()->route('account');
    }

    public function render()
    {
        return view('livewire.register')->layout('components.layouts.app', ['title' => __('auth.register')]);
    }
}
