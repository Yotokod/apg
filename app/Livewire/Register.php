<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class Register extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $password = '';

    public function register()
    {
        $key = 'register:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('email', __('auth.throttle', ['s' => RateLimiter::availableIn($key)]));

            return;
        }
        $data = $this->validate([
            'name' => 'required|string|min:2|max:100', 'email' => 'required|email:rfc|max:190|unique:users,email',
            'phone' => 'nullable|string|max:30', 'password' => ['required', 'string', 'max:100', Password::min(8)],
        ]);
        RateLimiter::hit($key, 3600);
        Auth::login(User::create($data + ['role' => 'customer']));
        session()->regenerate();

        return redirect()->route('account');
    }

    public function render()
    {
        return view('livewire.register')->layout('components.layouts.app', ['title' => __('auth.register')]);
    }
}
