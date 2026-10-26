<?php

namespace App\Livewire;

use App\Models\ChatMessage;
use App\Services\ChatBot;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ChatWidget extends Component
{
    public string $input = '';
    #[Locked]
    public string $sid = '';

    public function mount(): void
    {
        $this->sid = session()->getId();
    }

    public function send(ChatBot $bot): void
    {
        $text = mb_substr(trim($this->input), 0, 1000);
        $key = 'chat:'.request()->ip();
        if ($text === '' || RateLimiter::tooManyAttempts($key, 15)) {
            return;
        }
        RateLimiter::hit($key, 60);
        $this->input = '';
        ChatMessage::create(['session_id' => $this->sid, 'role' => 'user', 'content' => $text]);
        // Manual mode: if an agent already replied in this session, leave the conversation to them.
        if (! ChatMessage::where('session_id', $this->sid)->where('role', 'agent')->exists()) {
            ChatMessage::create(['session_id' => $this->sid, 'role' => 'bot', 'content' => $bot->reply($this->sid, $text)]);
        }
    }

    public function render()
    {
        return view('livewire.chat-widget', [
            'messages' => ChatMessage::where('session_id', $this->sid)->orderBy('id')->get(),
        ]);
    }
}
