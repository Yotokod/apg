<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Url;
use Livewire\Component;

class Contact extends Component
{
    public string $name = '';
    public string $email = '';
    public string $message = '';

    #[Url]
    public string $subject = '';

    public bool $sent = false;

    public function send(): void
    {
        $this->validate(['name' => 'required|min:2', 'email' => 'required|email', 'message' => 'required|min:5']);
        Log::info('Contact message', $this->only('name', 'email', 'subject', 'message'));
        $this->sent = true;
        $this->dispatch('toast', message: __('contact.sent'));
    }

    public function render()
    {
        return view('livewire.contact')
            ->layout('components.layouts.app', ['title' => __('nav.contact').' | '.site('name')]);
    }
}
