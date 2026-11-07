<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/** Web-sized WebP copies on the public disk: thumbs/<path>.webp (read by SiteData::imageUrl()). */
class ImageThumbs
{
    public const MAX_WIDTH = 1000;

    public const QUALITY = 78;

    public static function pathFor(string $path): string
    {
        return 'thumbs/'.ltrim($path, '/').'.webp';
    }

    /** Creates (or refreshes with $force) the thumbnail; returns its path or null when the source is not a readable image. */
    public static function make(string $path, bool $force = false, int $maxWidth = self::MAX_WIDTH, int $quality = self::QUALITY): ?string
    {
        $disk = Storage::disk('public');
        $target = self::pathFor($path);
        if (! $force && $disk->exists($target)) {
            return $target;
        }
        if (! preg_match('/\.(jpe?g|png|webp)$/i', $path) || ! $disk->exists($path)) {
            return null;
        }
        $img = @imagecreatefromstring((string) $disk->get($path));
        if (! $img) {
            return null;
        }
        imagepalettetotruecolor($img);
        imagealphablending($img, true);
        imagesavealpha($img, true);
        if (imagesx($img) > $maxWidth) {
            $img = imagescale($img, $maxWidth);
        }
        File::ensureDirectoryExists(dirname($disk->path($target)));
        imagewebp($img, $disk->path($target), $quality);

        return $target;
    }

    public static function delete(string $path): void
    {
        Storage::disk('public')->delete(self::pathFor($path));
    }
}
