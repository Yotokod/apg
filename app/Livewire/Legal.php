<?php

namespace App\Livewire;

use Illuminate\Support\Carbon;
use Livewire\Component;

/** CGV / privacy / legal notice. Texts are generic drafts: to be validated by a lawyer before production. */
class Legal extends Component
{
    private const TYPES = ['cgv', 'privacy', 'mentions'];

    private const UPDATED = '2026-10-26';

    public string $type = 'cgv';

    public function mount(string $type = 'cgv'): void
    {
        abort_unless(in_array($type, self::TYPES, true), 404);
        $this->type = $type;
    }

    private function interpolate(string $text): string
    {
        return strtr($text, [
            ':name' => site('name'), ':address' => site('address'), ':phone' => site('phone'),
            ':email' => site('email'), ':cities' => site('cities'),
        ]);
    }

    public function render()
    {
        $page = __('pages.legal.'.$this->type);

        return view('livewire.legal', [
            'page' => $page,
            'sections' => array_map(fn ($s) => ['title' => $s['title'], 'body' => $this->interpolate($s['body'])], $page['sections']),
            'updated' => Carbon::parse(self::UPDATED)->translatedFormat('d F Y'),
        ])->layout('components.layouts.app', ['title' => $page['title'].' | '.site('name')]);
    }
}
