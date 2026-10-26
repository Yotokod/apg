<?php

namespace App\Livewire;

use App\Models\ContactMessage;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Url;
use Livewire\Component;

class Contact extends Component
{
    private const MAX_ATTEMPTS = 5;

    private const SUBJECTS = ['order', 'delivery', 'courses', 'other'];

    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $message = '';

    #[Url]
    public string $subject = '';

    /** Honeypot: real users never see or fill this field. */
    public string $website = '';

    public bool $sent = false;

    public function send(): void
    {
        if ($this->website !== '') {
            $this->sent = true;

            return;
        }
        $key = 'contact:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $this->addError('message', __('pages.contact.throttle', ['seconds' => RateLimiter::availableIn($key)]));

            return;
        }
        RateLimiter::hit($key, 60);

        $data = $this->validate([
            'name' => 'required|string|min:2|max:100',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|string|max:30',
            'subject' => 'nullable|in:'.implode(',', self::SUBJECTS),
            'message' => 'required|string|min:5|max:3000',
        ]);

        ContactMessage::create($data);
        $this->sent = true;
        $this->dispatch('toast', message: __('pages.contact.sent_title'));
    }

    public function resetForm(): void
    {
        $this->reset('name', 'email', 'phone', 'message', 'subject', 'website', 'sent');
    }

    public function render()
    {
        return view('livewire.contact', [
            'cities' => array_map('trim', explode(',', (string) site('cities'))),
            'faq' => array_slice(__('pages.faq.items'), 0, 4),
        ])->layout('components.layouts.app', ['title' => __('pages.contact.meta_title').' | '.site('name')]);
    }
}
