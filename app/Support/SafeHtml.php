<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/** Defensive HTML cleaner for imported content: tag allowlist, attribute allowlist, safe URLs only. */
class SafeHtml
{
    private const TAGS = '<p><br><h2><h3><h4><ul><ol><li><strong><b><em><i><blockquote><a><img><figure><figcaption><table><thead><tbody><tr><th><td>';

    private const ATTRS = ['a' => ['href', 'title'], 'img' => ['src', 'alt', 'width', 'height']];

    public static function clean(?string $html): string
    {
        $html = trim(strip_tags((string) $html, self::TAGS));
        if ($html === '') {
            return '';
        }

        $doc = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8"?><div id="root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $doc->getElementById('root');
        if (! $root) {
            return e(strip_tags($html));
        }
        self::scrub($root);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }

        return $out;
    }

    private static function scrub(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }
            self::scrubAttributes($child);
            self::scrub($child);
        }
    }

    private static function scrubAttributes(DOMElement $el): void
    {
        $allowed = self::ATTRS[$el->tagName] ?? [];
        foreach (iterator_to_array($el->attributes) as $attr) {
            if (! in_array($attr->name, $allowed, true)) {
                $el->removeAttribute($attr->name);
            }
        }

        foreach (['href', 'src'] as $url) {
            if ($el->hasAttribute($url) && ! self::isSafeUrl($el->getAttribute($url))) {
                $el->removeAttribute($url);
            }
        }

        if ($el->tagName === 'a' && $el->hasAttribute('href')) {
            $el->setAttribute('rel', 'noopener noreferrer nofollow');
        }
        if ($el->tagName === 'img') {
            $el->setAttribute('loading', 'lazy');
            if (! $el->hasAttribute('alt')) {
                $el->setAttribute('alt', '');
            }
        }
    }

    private static function isSafeUrl(string $url): bool
    {
        $url = trim(preg_replace('/[\x00-\x20]+/', '', $url) ?? '');

        return (bool) preg_match('#^(https?://|mailto:|tel:|/(?![/\\\\])|\#)#i', $url);
    }
}
