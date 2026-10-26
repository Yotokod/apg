<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/** Builds web-sized WebP copies (storage/app/public/thumbs/<path>.webp) used by SiteData::imageUrl(). */
class MediaThumbs extends Command
{
    protected $signature = 'media:thumbs {--width=1000} {--quality=78}';

    protected $description = 'Generate optimized WebP thumbnails for downloaded WordPress media';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $made = 0;

        foreach ($disk->allFiles('wp-media') as $path) {
            if (! preg_match('/\.(jpe?g|png|webp)$/i', $path)) {
                continue;
            }
            $target = 'thumbs/'.$path.'.webp';
            if ($disk->exists($target)) {
                continue;
            }
            $img = $this->load($disk->path($path));
            if (! $img) {
                continue;
            }
            $img = $this->fit($img, (int) $this->option('width'));
            File::ensureDirectoryExists(dirname($disk->path($target)));
            imagewebp($img, $disk->path($target), (int) $this->option('quality'));
            $made++;
        }
        $this->info("$made thumbnails created");

        return self::SUCCESS;
    }

    private function load(string $file): ?\GdImage
    {
        $img = @imagecreatefromstring((string) file_get_contents($file));
        if (! $img) {
            return null;
        }
        imagepalettetotruecolor($img);
        imagealphablending($img, true);
        imagesavealpha($img, true);

        return $img;
    }

    private function fit(\GdImage $img, int $maxWidth): \GdImage
    {
        return imagesx($img) > $maxWidth ? imagescale($img, $maxWidth) : $img;
    }
}
