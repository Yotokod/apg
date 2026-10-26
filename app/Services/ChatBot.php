<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/** AI replies when an API key is set in Admin > Settings (ai_api_key), otherwise a simple FAQ fallback. */
class ChatBot
{
    private function apiKey(): ?string
    {
        $stored = site('ai_api_key');
        if (! $stored) {
            return null;
        }
        try {
            return \Illuminate\Support\Facades\Crypt::decryptString($stored);
        } catch (\Throwable) {
            return null;
        }
    }

    public function reply(string $sid, string $text): string
    {
        $key = $this->apiKey();
        if ($key) {
            try {
                $r = Http::withHeaders(['x-api-key' => $key, 'anthropic-version' => '2023-06-01'])->timeout(20)
                    ->post('https://api.anthropic.com/v1/messages', [
                        'model' => site('ai_model', 'claude-haiku-5-5'), 'max_tokens' => 400,
                        'system' => 'You are the support assistant of '.site('name').' (poultry sales and online poultry-farming training, Benin). Answer briefly in the user\'s language. Contact: '.site('phone').' / '.site('email').'.',
                        'messages' => [['role' => 'user', 'content' => $text]],
                    ])->throw()->json();

                return $r['content'][0]['text'] ?? __('chat.fallback');
            } catch (\Throwable) {
                // fall through to FAQ
            }
        }
        $t = mb_strtolower($text);
        return match (true) {
            str_contains($t, 'prix') || str_contains($t, 'price') => __('chat.price'),
            str_contains($t, 'formation') || str_contains($t, 'course') => __('chat.course'),
            str_contains($t, 'livraison') || str_contains($t, 'deliver') => __('chat.delivery'),
            default => __('chat.fallback'),
        };
    }
}
