<?php

namespace App\Livewire;

use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;

class Faq extends Component
{
    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'cat')]
    public string $category = '';

    private function items(): array
    {
        $needle = Str::lower(trim($this->search));

        return array_values(array_filter(__('pages.faq.items'), fn ($i) => ($this->category === '' || $i['cat'] === $this->category)
            && ($needle === '' || Str::contains(Str::lower($i['q'].' '.$i['a']), $needle))));
    }

    private function jsonLd(): string
    {
        return json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn ($i) => ['@type' => 'Question', 'name' => $i['q'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $i['a']]], __('pages.faq.items')),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP);
    }

    public function render()
    {
        return view('livewire.faq', [
            'items' => $this->items(),
            'categories' => __('pages.faq.categories'),
            'jsonLd' => $this->jsonLd(),
        ])->layout('components.layouts.app', ['title' => __('pages.faq.meta_title').' | '.site('name')]);
    }
}
