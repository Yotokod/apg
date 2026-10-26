<?php

namespace App\Livewire;

use App\Models\Post;
use App\Support\SafeHtml;
use Livewire\Component;

class BlogShow extends Component
{
    private const WORDS_PER_MINUTE = 200;

    public string $slug;

    public function mount(string $slug): void
    {
        $this->slug = $slug;
        abort_unless(BlogIndex::published()->where('slug', $slug)->exists(), 404);
    }

    public static function coverUrl(?string $cover): ?string
    {
        if (! $cover) {
            return null;
        }

        return preg_match('#^https?://#i', $cover) ? $cover : asset('storage/'.ltrim($cover, '/'));
    }

    private function related(Post $post)
    {
        $base = BlogIndex::published()->whereKeyNot($post->id)->orderByDesc('published_at')->limit(3);
        $same = $post->category ? (clone $base)->where('category', $post->category)->get() : collect();

        return $same->isNotEmpty() ? $same : $base->get();
    }

    private function jsonLd(Post $post, string $title, ?string $cover): string
    {
        $data = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $title,
            'description' => (string) $post->tr('excerpt'),
            'image' => $cover,
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified' => $post->updated_at?->toIso8601String(),
            'mainEntityOfPage' => route('blog.show', $post->slug),
            'author' => ['@type' => 'Organization', 'name' => site('name')],
            'publisher' => ['@type' => 'Organization', 'name' => site('name'), 'logo' => ['@type' => 'ImageObject', 'url' => url(site('logo'))]],
        ]);

        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP);
    }

    public function render()
    {
        $post = BlogIndex::published()->where('slug', $this->slug)->firstOrFail();
        $body = SafeHtml::clean($post->tr('body'));
        $title = (string) $post->tr('title');
        $cover = self::coverUrl($post->cover);

        return view('livewire.blog-show', [
            'post' => $post,
            'title' => $title,
            'body' => $body,
            'cover' => $cover,
            'minutes' => max(1, (int) ceil(str_word_count(strip_tags($body)) / self::WORDS_PER_MINUTE)),
            'related' => $this->related($post),
            'url' => route('blog.show', $post->slug),
            'jsonLd' => $this->jsonLd($post, $title, $cover),
        ])->layout('components.layouts.app', ['title' => $title.' | '.site('name')]);
    }
}
