<?php

namespace App\Livewire;

use App\Models\NewsletterSubscriber;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class NewsletterSignup extends Component
{
    public string $email = '';

    public string $website = ''; // honeypot

    public bool $done = false;

    protected function rules(): array
    {
        return ['email' => ['required', 'email:rfc', 'max:190', 'unique:newsletter_subscribers,email']];
    }

    protected function messages(): array
    {
        return [
            'email.required' => __('news.err_required'),
            'email.email' => __('news.err_email'),
            'email.max' => __('news.err_max'),
            'email.unique' => __('news.err_unique'),
        ];
    }

    public function subscribe(): void
    {
        $key = 'newsletter:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('email', __('news.wait'));

            return;
        }
        RateLimiter::hit($key, 60);

        $this->email = mb_strtolower(trim($this->email));
        $this->validate();

        if ($this->website === '') { // bots filling the hidden field are silently ignored
            NewsletterSubscriber::create(['email' => $this->email, 'locale' => app()->getLocale()]);
        }

        $this->reset('email', 'website');
        $this->done = true;
        $this->dispatch('toast', message: __('news.ok'));
    }

    public function render()
    {
        return view('livewire.newsletter-signup');
    }
}
