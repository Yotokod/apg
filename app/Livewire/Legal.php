<?php

namespace App\Livewire;

use App\Support\Identity;
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

    /** Admin-authored HTML (current locale, then French), sanitised, or null to use the built-in text. */
    private function customHtml(): ?string
    {
        $html = Identity::richText(Identity::localizedOverride('legal.'.$this->type));
        if (Identity::isBlankHtml($html)) {
            return null;
        }
        $values = array_map(fn ($v) => e((string) $v), ['name' => site('name'), 'address' => site('address'), 'phone' => site('phone'), 'email' => site('email'), 'cities' => site('cities')]);

        return strtr($html, collect($values)->mapWithKeys(fn ($v, $k) => [":$k" => $v])->all());
    }

    public function render()
    {
        $page = __('pages.legal.'.$this->type);
        $custom = $this->customHtml();

        return view('livewire.legal', [
            'page' => $page,
            'custom' => $custom,
            'sections' => $custom ? [] : array_map(fn ($s) => ['title' => $s['title'], 'body' => $this->interpolate($s['body'])], $page['sections']),
            'updated' => Carbon::parse(self::UPDATED)->translatedFormat('d F Y'),
        ])->layout('components.layouts.app', ['title' => $page['title'].' | '.site('name')]);
    }
}
