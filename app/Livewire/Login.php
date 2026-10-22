<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Login extends Component
{
    public string $email = '';
    public string $password = '';

    public function login()
    {
        $this->validate(['email' => 'required|email', 'password' => 'required']);
        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], true)) {
            $this->addError('email', __('auth.failed'));

            return;
        }
        session()->regenerate();

        return redirect()->intended(route('account'));
    }

    public function render()
    {
        return view('livewire.login')->layout('components.layouts.app', ['title' => __('nav.login')]);
    }
}
