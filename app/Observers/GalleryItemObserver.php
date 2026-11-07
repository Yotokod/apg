<?php

namespace App\Observers;

use App\Models\GalleryItem;
use App\Support\ImageThumbs;
use Illuminate\Support\Facades\Storage;

/** Keeps the WebP thumbnail in sync and cleans up admin uploads (only files under gallery/, never wp-media). */
class GalleryItemObserver
{
    public const UPLOAD_DIR = 'gallery/';

    public function saved(GalleryItem $item): void
    {
        if (! $item->wasRecentlyCreated && ! $item->wasChanged('image')) {
            return;
        }
        if ($item->image) {
            // New rows reuse an existing thumbnail (seeders, re-imports); a replaced image always gets a fresh one.
            ImageThumbs::make($item->image, force: ! $item->wasRecentlyCreated);
        }
        $previous = $item->getOriginal('image');
        if ($item->wasChanged('image') && $previous && $previous !== $item->image) {
            $this->removeFiles($previous, $item->id);
        }
    }

    public function deleted(GalleryItem $item): void
    {
        $this->removeFiles($item->image, $item->id);
    }

    private function removeFiles(?string $path, int $ignoreId): void
    {
        if (! $path || ! str_starts_with($path, self::UPLOAD_DIR) || str_contains($path, '..')) {
            return;
        }
        if (GalleryItem::where('image', $path)->whereKeyNot($ignoreId)->exists()) {
            return;
        }
        Storage::disk('public')->delete($path);
        ImageThumbs::delete($path);
    }
}
