<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $file = __DIR__.'/data/blog.json';
        if (! is_file($file)) {
            return;
        }
        foreach (json_decode(file_get_contents($file), true) ?: [] as $p) {
            Post::updateOrCreate(['slug' => $p['slug']], [
                'title' => ['fr' => $p['title']],
                'excerpt' => ['fr' => $p['excerpt']],
                'body' => ['fr' => $p['content']],
                'cover' => $p['cover'] ?? null,
                'category' => $p['category'] ?? null,
                'published_at' => $p['date'],
                'is_active' => true,
            ]);
        }
    }
}
