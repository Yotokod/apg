<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Admin-editable identity & content settings (allow-list, validation, storage, public helpers).
 * Values live in the `settings` table and are read through site(); an absent row means "use the default".
 */
class Identity
{
    public const LOCALES = ['fr', 'en'];

    public const SECRET_KEYS = ['ai_api_key'];

    /** Toggles: stored as '1'/'0' only when they differ from the default. */
    public const BOOL_DEFAULTS = ['announcement.enabled' => true, 'cert.enabled' => true, 'maintenance.enabled' => false];

    /** Admin-authored HTML (rendered publicly through richText()). */
    public const RICH_PREFIXES = ['about.story', 'legal.'];

    public const LEGAL_TYPES = ['cgv', 'privacy', 'mentions'];

    private const FILE_RULE = ['nullable', 'string', 'max:255', 'not_regex:/\.\./', 'regex:#^(identity/[\w.-]+|/images/[\w./-]+)\.(png|jpe?g|webp)$#i'];

    /** @return array<string, list<string>> key => validation rules (every non-secret editable key) */
    public static function rules(): array
    {
        $text = fn (int $max) => ['nullable', 'string', "max:$max"];
        $url = ['nullable', 'url:http,https', 'max:500'];
        $hex = ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'];
        $bool = ['nullable', 'in:0,1'];

        return [
            'name' => $text(120), 'short_name' => $text(30),
            ...self::localized('tagline', $text(160)),
            'logo' => self::FILE_RULE, 'favicon' => self::FILE_RULE,
            'primary_color' => $hex, 'accent_color' => $hex,

            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[+0-9 ().-]{6,30}$/'],
            'whatsapp' => ['nullable', 'regex:/^\d{8,15}$/'],
            'email' => ['nullable', 'email', 'max:190'],
            'address' => $text(255), 'cities' => $text(255),
            ...self::localized('hours', $text(500)),
            'maps_url' => $url,

            ...collect(['youtube', 'tiktok', 'facebook', 'instagram', 'linkedin', 'twitter'])->mapWithKeys(fn ($s) => ["socials.$s" => $url])->all(),

            ...self::localized('about.story', $text(20000)),
            ...self::localized('about.mission', $text(1000)),
            'announcement.enabled' => $bool,
            ...self::localized('announcement.text', $text(160)),
            'announcement.link' => ['nullable', 'string', 'max:500', 'regex:#^(https?://[^\s<>"]+|/(?![/\\\\])[^\s<>"]*)$#i'],
            ...self::localized('footer.pitch', $text(500)),

            ...collect(self::LEGAL_TYPES)->flatMap(fn ($t) => self::localized("legal.$t", $text(60000)))->all(),

            ...self::localized('seo.description', $text(300)),
            'seo.og_image' => self::FILE_RULE,
            'seo.ga_id' => ['nullable', 'regex:/^(G|UA|AW|GT)-[A-Z0-9-]{4,20}$/'],
            'seo.pixel_id' => ['nullable', 'regex:/^\d{5,20}$/'],

            'org.legal_name' => $text(190), 'org.director_name' => $text(120),
            ...self::localized('org.director_title', $text(120)),
            'org.signature' => self::FILE_RULE, 'org.stamp' => self::FILE_RULE,
            'cert.enabled' => $bool,
            ...self::localized('cert.title', $text(160)),
            ...self::localized('cert.text', $text(2000)),
            ...self::localized('cert.footer', $text(500)),
            'cert.number_prefix' => ['nullable', 'regex:/^[A-Za-z0-9-]{1,12}$/'],
            'cert.min_progress' => ['nullable', 'integer', 'between:1,100'],

            'app_mode' => ['nullable', 'in:one_market,marketplace'],
            'home_page' => ['nullable', 'in:landing,home,shop,courses'],
            'currency' => $text(10),
            'ai_model' => ['nullable', 'regex:/^[A-Za-z0-9._:-]{1,100}$/'],
            'maintenance.enabled' => $bool,
            ...self::localized('maintenance.message', $text(500)),
        ];
    }

    /** @return list<string> */
    public static function keys(): array
    {
        return array_keys(self::rules());
    }

    /** Raw stored values for every allowed key (absent = null). @return array<string, ?string> */
    public static function stored(): array
    {
        $overrides = site_overrides();

        return collect(self::keys())->mapWithKeys(fn ($k) => [$k => $overrides[$k] ?? null])->all();
    }

    /** Validate + persist a flat key => value map. Unknown keys are refused; empty values delete the override. */
    public static function store(array $values): void
    {
        $unknown = array_diff(array_keys($values), self::keys());
        if ($unknown) {
            throw ValidationException::withMessages(['settings' => __('identity.admin.unknown_keys', ['keys' => implode(', ', $unknown)])]);
        }

        $values = collect($values)->map(fn ($v, $k) => self::normalize($k, $v))->all();
        Validator::make(Arr::undot($values), Arr::only(self::rules(), array_keys($values)))->validate();

        foreach ($values as $key => $value) {
            if ($value === null) {
                Setting::where('key', $key)->delete();
            } else {
                Setting::updateOrCreate(['key' => $key], ['value' => $value, 'group' => strtok($key, '.')]);
            }
        }
        site_flush();
    }

    /** @return array{format: string, version: int, settings: array<string,string>} */
    public static function export(): array
    {
        return ['format' => 'apg-identity', 'version' => 1, 'settings' => array_filter(self::stored(), fn ($v) => $v !== null)];
    }

    public static function import(array $payload): int
    {
        $settings = $payload['settings'] ?? $payload;
        if (! is_array($settings) || array_is_list($settings) && $settings !== []) {
            throw ValidationException::withMessages(['settings' => __('identity.admin.invalid_json')]);
        }
        foreach ($settings as $v) {
            if (! is_null($v) && ! is_scalar($v)) {
                throw ValidationException::withMessages(['settings' => __('identity.admin.invalid_json')]);
            }
        }
        self::store($settings);

        return count($settings);
    }

    public static function enabled(string $key): bool
    {
        $value = site_overrides()[$key] ?? null;

        return $value === null || $value === '' ? self::BOOL_DEFAULTS[$key] : $value === '1';
    }

    /** Stored value for the current locale, falling back to French; null when none. */
    public static function localizedOverride(string $key): ?string
    {
        $overrides = site_overrides();
        foreach (array_unique([app()->getLocale(), 'fr']) as $locale) {
            if (filled($overrides["$key.$locale"] ?? null)) {
                return $overrides["$key.$locale"];
            }
        }

        return null;
    }

    /** Sanitised admin HTML (script/style bodies dropped, then tag/attribute allow-list). */
    public static function richText(?string $html): string
    {
        $html = preg_replace('#<(script|style|iframe|object|embed|template)\b[^>]*>.*?</\1\s*>#is', '', (string) $html) ?? '';

        return SafeHtml::clean($html);
    }

    public static function isBlankHtml(?string $html): bool
    {
        return trim(html_entity_decode(strip_tags((string) $html, '<img>'), ENT_QUOTES | ENT_HTML5), " \t\n\r\0\x0B\u{A0}") === '';
    }

    /** CSS custom-property overrides for the admin colours, or null when none is set. */
    public static function themeCss(): ?string
    {
        $overrides = site_overrides();
        $vars = [];
        $primary = self::hex($overrides['primary_color'] ?? null);
        if ($primary) {
            $vars += ['--color-brand-500' => self::mix($primary, 255, 0.15), '--color-brand-600' => $primary,
                '--color-brand-700' => self::mix($primary, 0, 0.15), '--color-brand-800' => self::mix($primary, 0, 0.3)];
        }
        $accent = self::hex($overrides['accent_color'] ?? null);
        if ($accent) {
            $vars += ['--color-ink-900' => $accent, '--color-ink-800' => self::mix($accent, 255, 0.1)];
        }

        return $vars ? ':root{'.collect($vars)->map(fn ($v, $k) => "$k:$v")->implode(';').'}' : null;
    }

    public static function safeLink(?string $url): ?string
    {
        return $url && preg_match('#^(https?://|/(?![/\\\\]))#i', $url) && ! preg_match('/[\s<>"]/', $url) ? $url : null;
    }

    private static function normalize(string $key, mixed $value): ?string
    {
        if (array_key_exists($key, self::BOOL_DEFAULTS)) {
            if ($value === null || $value === '') {
                return null;
            }
            $bool = filter_var($value, FILTER_VALIDATE_BOOL);

            return $bool === self::BOOL_DEFAULTS[$key] ? null : ($bool ? '1' : '0');
        }
        $value = is_array($value) ? null : trim((string) $value);
        if ($value !== null && self::isRich($key) && self::isBlankHtml($value)) {
            return null;
        }

        return $value === '' ? null : $value;
    }

    private static function isRich(string $key): bool
    {
        return collect(self::RICH_PREFIXES)->contains(fn ($p) => str_starts_with($key, $p));
    }

    /** @return array<string, list<string>> */
    private static function localized(string $key, array $rules): array
    {
        return collect(self::LOCALES)->mapWithKeys(fn ($l) => ["$key.$l" => $rules])->all();
    }

    private static function hex(?string $value): ?string
    {
        return $value && preg_match('/^#[0-9A-Fa-f]{6}$/', $value) ? strtolower($value) : null;
    }

    /** Blend a hex colour toward black (0) or white (255) by $amount (0..1). */
    private static function mix(string $hex, int $target, float $amount): string
    {
        $channels = array_map(fn ($c) => (int) round(hexdec($c) + ($target - hexdec($c)) * $amount), str_split(substr($hex, 1), 2));

        return '#'.implode('', array_map(fn ($c) => str_pad(dechex($c), 2, '0', STR_PAD_LEFT), $channels));
    }
}
