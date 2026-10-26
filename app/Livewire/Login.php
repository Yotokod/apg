<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class Login extends Component
{
    public string $email = '';
    public string $password = '';

    public function login()
    {
        $this->validate(['email' => 'required|email', 'password' => 'required']);
        $key = 'login:'.strtolower($this->email).'|'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5) || RateLimiter::tooManyAttempts('login-ip:'.request()->ip(), 20)) {
            $this->addError('email', __('auth.throttle', ['s' => RateLimiter::availableIn($key)]));

            return;
        }
        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            RateLimiter::hit($key, 60);
            RateLimiter::hit('login-ip:'.request()->ip(), 60);
            $this->addError('email', __('auth.failed'));

            return;
        }
        RateLimiter::clear($key);
        session()->regenerate();

        return redirect()->intended(route('account'));
    }

    public function render()
    {
        return view('livewire.login')->layout('components.layouts.app', ['title' => __('nav.login')]);
    }
}
