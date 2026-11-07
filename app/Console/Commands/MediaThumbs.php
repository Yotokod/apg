<?php

namespace App\Console\Commands;

use App\Support\ImageThumbs;
use Illuminate\Console\Command;
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
            if ($disk->exists(ImageThumbs::pathFor($path))) {
                continue;
            }
            if (ImageThumbs::make($path, false, (int) $this->option('width'), (int) $this->option('quality'))) {
                $made++;
            }
        }
        $this->info("$made thumbnails created");

        return self::SUCCESS;
    }
}
