<?php

namespace Database\Seeders;

use App\Models\GalleryItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GallerySeeder extends Seeder
{
    private const MIN_BYTES = 15360;

    private const FEATURED_COUNT = 12;

    private const JUNK = '/^attention\s*\(?\d*\)?$|screenshot|capture d|sans[\s-]titre|whatsapp image|chatgpt|logo|placeholder|icon|mockup|doc-apg|vectoriel/i';

    private const KEYWORDS = [
        'poussins' => ['poussin', 'chick', 'couvoir', 'caille'],
        'oeufs' => ['oeuf', 'œuf', 'egg', 'incub'],
        'poulets' => ['poulet', 'poule', 'coq', 'goliath', 'chicken', 'pintade', 'volaille', 'pollo'],
        'livraison' => ['livraison', 'transport', 'carton', 'expedition', 'delivery'],
        'formation' => ['formation', 'cours', 'module', 'guide', 'atelier', 'training'],
        'cuisine' => ['recette', 'cuisine', 'braise', 'rôti', 'roti', 'plat', 'mafe', 'sauce', 'grill'],
        'equipe' => ['equipe', 'équipe', 'team', 'femme', 'eleveuse', 'éleveur', 'eleveur'],
        'elevage' => ['elevage', 'élevage', 'ferme', 'batiment', 'bâtiment', 'farm', 'aviculture', 'poulailler', 'alimentation'],
    ];

    private const FEATURE_CATEGORIES = ['poussins', 'poulets', 'elevage', 'oeufs'];

    public function run(): void
    {
        $file = storage_path('app/public/wp-media/manifest.json');
        if (! is_file($file)) {
            return;
        }
        $seen = [];
        $rows = [];
        foreach (json_decode(file_get_contents($file), true)['images'] ?? [] as $img) {
            if ($row = $this->candidate($img, $seen)) {
                $rows[] = $row;
            }
        }
        $featured = $this->pickFeatured($rows);
        foreach ($rows as $i => $r) {
            GalleryItem::updateOrCreate(['wp_id' => $r['wp_id']], [
                'title' => ['fr' => $r['title']],
                'alt' => ['fr' => $r['alt']],
                'image' => $r['image'],
                'category' => $r['category'],
                'is_featured' => isset($featured[$r['wp_id']]),
                'is_active' => true,
                'position' => $i,
            ]);
        }
    }

    private function candidate(array $img, array &$seen): ?array
    {
        $path = storage_path('app/public/'.$img['file']);
        $title = trim((string) ($img['title'] ?? ''));
        $alt = trim((string) ($img['alt'] ?? ''));
        $base = pathinfo($img['file'], PATHINFO_FILENAME);
        if (! is_file($path) || ! str_starts_with((string) $img['mime'], 'image/') || preg_match(self::JUNK, $title.' '.$base)) {
            return null;
        }
        $size = filesize($path);
        $key = $size.'|'.preg_replace('/-\d+$/', '', Str::lower($base));
        if ($size < self::MIN_BYTES || isset($seen[$key]) || isset($seen[$size])) {
            return null;
        }
        $seen[$key] = $seen[$size] = true;
        $label = $alt ?: Str::headline($title ?: $base);

        return [
            'wp_id' => $img['id'], 'image' => $img['file'], 'size' => $size, 'mime' => $img['mime'],
            'title' => $label, 'alt' => $label,
            'category' => $this->category(Str::lower("$title $alt $base")),
        ];
    }

    private function category(string $haystack): string
    {
        foreach (self::KEYWORDS as $cat => $words) {
            foreach ($words as $w) {
                if (str_contains($haystack, $w)) {
                    return $cat;
                }
            }
        }

        return 'autre';
    }

    /** @return array<int,true> */
    private function pickFeatured(array $rows): array
    {
        $eligible = array_filter($rows, fn ($r) => in_array($r['category'], self::FEATURE_CATEGORIES, true) && in_array($r['mime'], ['image/jpeg', 'image/webp'], true));
        usort($eligible, fn ($a, $b) => $b['size'] <=> $a['size']);

        return array_fill_keys(array_column(array_slice($eligible, 0, self::FEATURED_COUNT), 'wp_id'), true);
    }
}
