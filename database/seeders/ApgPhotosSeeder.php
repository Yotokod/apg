<?php

namespace Database\Seeders;

use App\Models\GalleryItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/** Official APG photos (database/seeders/assets/apg) -> public disk + gallery (featured, first positions). */
class ApgPhotosSeeder extends Seeder
{
    private const COLORS = [
        'cuivre' => ['fr' => 'robe cuivrée et noire', 'en' => 'copper and black plumage'],
        'creme' => ['fr' => 'robe crème et blanche', 'en' => 'cream and white plumage'],
        'gris' => ['fr' => 'robe grise barrée', 'en' => 'barred grey plumage'],
    ];

    public function run(): void
    {
        $disk = Storage::disk('public');
        $files = collect(File::files(database_path('seeders/assets/apg')))->sortBy(fn ($f) => $f->getFilename())->values();

        foreach ($files as $i => $file) {
            $name = $file->getFilename();
            $rel = 'gallery/apg-'.$name;
            if (! $disk->exists($rel)) {
                $disk->put($rel, File::get($file->getPathname()));
            }
            $this->thumb($disk->path($rel), $disk->path('thumbs/'.$rel.'.webp'));

            [, $color, $n] = preg_match('/reproducteur-([a-z]+)-(\d+)/', $name, $m) ? $m : [null, 'cuivre', $i + 1];
            $label = self::COLORS[$color] ?? self::COLORS['cuivre'];
            GalleryItem::updateOrCreate(['image' => $rel], [
                'title' => ['fr' => 'Reproducteur Goliath, '.$label['fr'], 'en' => 'Goliath breeder, '.$label['en']],
                'alt' => ['fr' => 'Sujet reproducteur Goliath de la ferme APG, '.$label['fr'], 'en' => 'Goliath breeder from the APG farm, '.$label['en']],
                'category' => 'poulets',
                'is_featured' => true,
                'is_active' => true,
                'position' => -100 + $i,
            ]);
        }
    }

    private function thumb(string $source, string $target): void
    {
        if (is_file($target) || ! ($img = @imagecreatefromjpeg($source))) {
            return;
        }
        File::ensureDirectoryExists(dirname($target));
        imagewebp(imagesx($img) > 1000 ? imagescale($img, 1000) : $img, $target, 78);
    }
}
