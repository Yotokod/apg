# Security and code review: APG (Laravel 12, Livewire 4, Filament 5)

Read-only review of app/, routes/, resources/views/, database/seeders, bootstrap/app.php, config/site_identity.php.
Only findings verified in code are listed. `composer audit` was not run (no composer.phar available); run it in CI.
Paths are relative to the `apg/` project root.

Counts: Critical 1, High 5, Medium 9, Low 9.

---

## CRITICAL

### C1. Seeder always creates a known superadmin (admin@apg.test / password)
- File: `database/seeders/DatabaseSeeder.php`, first statement of `run()`: `User::firstOrCreate(['email'=>'admin@apg.test'], [... 'password'=>'password', 'role'=>'superadmin'])`. There is no `APP_ENV` guard.
- Scenario: `php artisan db:seed` in production (needed to load the catalogue) creates a superadmin with a public password. Anyone who opens `/admin` takes over the site: payment keys, the AI key, all users.
- Fix:
```php
if (app()->environment(['local', 'testing'])) {
    User::firstOrCreate(['email' => 'admin@apg.test'], [...]);
} else {
    $pwd = env('ADMIN_INITIAL_PASSWORD') ?: throw new RuntimeException('Set ADMIN_INITIAL_PASSWORD');
    User::firstOrCreate(['email' => env('ADMIN_EMAIL')], ['name'=>'Super Admin','password'=>$pwd,'role'=>'superadmin']);
}
```
  Also ship `.env.example` with `APP_ENV=production`, `APP_DEBUG=false`.

---

## HIGH

### H1. The `admin` role can escalate to superadmin and read payment secrets (no per-resource authorization)
- Files: `app/Models/User.php` (`canAccessPanel` allows admin and superadmin); `app/Filament/Resources/Users/UserResource.php` (Select `role` offers `superadmin`); `app/Filament/Resources/PaymentGateways/PaymentGatewayResource.php` (`KeyValue::make('config')`). No `canViewAny` / `canAccess` / policy exists under `app/Filament` except `SiteSettings::canAccess`.
- Scenario: a plain `admin` (support staff) edits their own user, sets role to `superadmin`, then opens Settings (AI key) and reads the decrypted gateway `secret_key` / `private_key` in the KeyValue form. They can also set orders to `paid` and delete users.
- Fix:
```php
// UserResource, PaymentGatewayResource
public static function canViewAny(): bool { return auth()->user()?->role === 'superadmin'; }
```
  Also hide or limit the role Select unless the actor is superadmin, add a `UserPolicy`, and render gateway secrets as `TextInput::password()->revealable()` fields instead of a KeyValue that echoes everything.

### H2. ChatWidget: `$sid` is a public property, so any visitor can read any chat session and bypass the rate limit
- File: `app/Livewire/ChatWidget.php` (`public string $sid`, used in `ChatMessage::where('session_id', $this->sid)` and in the `chat:` limiter key).
- Scenario: a client sends a Livewire update setting `sid` to another visitor's session id; the widget renders that conversation (names, phones, order details people type). Setting a fresh random `sid` on every call also resets the 15 msg/min limiter, so an attacker can hit the paid Anthropic API (`ChatBot::reply`) without limit and fill `chat_messages`.
- Fix:
```php
use Livewire\Attributes\Locked;
#[Locked] public string $sid = '';
// limiter key: 'chat:'.request()->ip().'|'.session()->getId()
```
  Better: drop the property and call `session()->getId()` inside each method. The id rotates on login, so keep a separate random `chat_token` in the session.

### H3. Cart/Checkout accepts a disabled payment gateway; `orderId` and `gateway` are tamperable public properties
- File: `app/Livewire/Cart.php` (`$orderId`, `$gateway`, `checkout()`, `render()`).
  - `'gateway' => 'required|exists:payment_gateways,code'` never checks `enabled = true` (only the rendered list is filtered).
  - `public ?int $orderId` is read back with `Order::find($this->orderId)` in `render()`: a visitor sets any id and the confirmation card shows that order's id and total (IDOR, sales volume enumeration).
- Scenario: a customer submits a gateway the admin disabled (for example `paypal` with empty keys). `PaymentManager::initiate` swallows the failure and returns null, so a pending order is stored with a method the shop does not offer.
- Fix:
```php
#[Locked] public ?int $orderId = null;
'gateway' => ['required', Rule::exists('payment_gateways', 'code')->where('enabled', true)],
// render: only show orders created in this session
$order = $this->orderId && in_array($this->orderId, session('my_orders', [])) ? Order::find($this->orderId) : null;
// checkout(): session()->push('my_orders', $order->id);
```

### H4. Quiz component: tamperable `lessonId` / `nodeId` / `correct` / `path` bypass enrollment and forge progress
- File: `app/Livewire/Quiz.php` (public `lessonId`, `nodeId`, `picked`, `path`, `correct`). `LessonPlayer::mount` checks a paid enrollment, but the nested `Quiz` trusts its own client-writable state.
- Scenario: a logged-in user without enrollment opens a free lesson, then sends a Livewire update with `nodeId` of a paid course's node: `render()` returns its question, options and explanation (`QuizNode::find($this->nodeId)`, no lesson or ownership check). They can also set `lessonId` to any lesson and `correct`/`path` to get 100 percent: `LessonProgress::updateOrCreate` then records a forged score and completion for a lesson they never took.
- Fix:
```php
#[Locked] public int $lessonId; #[Locked] public ?int $nodeId = null;
#[Locked] public array $path = []; #[Locked] public int $correct = 0;
// pick(): QuizNode::where('lesson_id', $this->lessonId)->findOrFail($this->nodeId)
// next(): next_node_id must belong to the same lesson_id
// mount(): re-check the paid enrollment for $lessonId
```

### H5. No rate limit on registration; login limiter easily bypassed; webhook unauthenticated and CSRF-exempt
- Files: `app/Livewire/Register.php` (no throttle, `phone` unvalidated, no `max` on name/email, `password` min:8 only, instant login with remember=true); `app/Livewire/Login.php` (limiter key `login:<email>|<ip>`: rotating emails from one IP gives 5 tries per email, so password spraying is unlimited); `bootstrap/app.php` + `routes/web.php` (`paiement/webhook/*` exempt from CSRF, no signature, no throttle, logs each request).
- Scenario: scripts create thousands of users (DB bloat, squatting real customers' emails) and spray one common password across many accounts. Today the webhook does nothing; once someone adds "mark paid" logic it is forgeable (see M3).
- Fix:
```php
// Login: add a per-IP key, for example RateLimiter::tooManyAttempts('login-ip:'.request()->ip(), 20)
// Register: per-IP limiter 5/min and
'name'=>'required|string|min:2|max:100','phone'=>'nullable|string|max:30',
'email'=>'required|email:rfc|max:190|unique:users,email','password'=>['required', Password::min(10)->uncompromised()]
// webhook route: ->middleware('throttle:60,1') and verify the gateway HMAC before touching an Order
```

---

## MEDIUM

### M1. AI API key stored in plaintext in `settings` and in the shared cache
- Files: `app/Filament/Pages/SiteSettings.php` (`Setting::updateOrCreate(['key'=>'ai_api_key'], ['value'=>$v])`), `app/Support/helpers.php` (whole table cached by `Cache::rememberForever('settings.all')` in the DB `cache` table), `app/Services/ChatBot.php`.
- Scenario: any DB dump, backup or SQL read exposes the Anthropic key. `PaymentGateway.config` is `encrypted:array` but this key is not.
- Fix: encrypt on save and decrypt in `ChatBot` (`Crypt::encryptString`), or read it from `.env` via `config('services.anthropic.key')`, and exclude secret keys from the `settings.all` cache.

### M2. Checkout: no stock check or decrement, no transaction, no idempotency
- File: `app/Livewire/Cart.php` `checkout()`. Totals are correctly recomputed server-side (`CartStore::total`), but stock is never re-checked or decremented and `Order::create` plus `items()->create` are not in a transaction.
- Scenario: overselling (100 buyers of 1 unit); a failure between order and lines leaves an empty order; a double click can create two orders before `session()->forget('cart')`.
- Fix: `DB::transaction`, `Product::lockForUpdate()`, `decrement('stock', qty)` with abort when insufficient, clear the cart inside the transaction.

### M3. Nothing verifies a payment; webhook only logs
- File: `routes/web.php` (`payment.return` redirects only, which is correct; `payment.webhook` logs `array_keys(request()->all())` and returns ok). `PaymentManager` has no verification method. Orders become `paid` only by hand in admin.
- Scenario: functional gap now; when "paid" logic is added following this pattern by trusting `request()->input('status')`, anyone can mark any order paid. Meanwhile unauthenticated POSTs write log lines without limit.
- Fix: re-fetch the transaction server-side with the stored `payment_ref`, compare amount and currency with `orders.total`, verify HMAC headers, be idempotent, and throttle the route.

### M4. Hosted-checkout URL not pinned to the gateway host; amount rewritten silently
- Files: `app/Livewire/Cart.php` (`redirect()->away($url)`), `app/Services/PaymentManager.php` (`cinetpay`: `ceil($o->total/5)*5`; `paypal`: fixed 600 XOF/USD).
- Scenario: the URL comes from the gateway API response, so no open redirect today, but a misconfigured or compromised endpoint can send customers anywhere. CinetPay charges up to 4 FCFA more than the stored order total and the difference is not recorded; the PayPal rate is hard-coded.
- Fix: allow-list `parse_url($url, PHP_URL_HOST)` per gateway, store the charged amount, move the rate to settings.

### M5. FileUpload accepts SVG and unbounded sizes; Lottie JSON served from the app origin
- Files: `PostResource.php`, `GalleryItemResource.php`, `ProductResource.php` (`->image()` allows `image/svg+xml`, no `maxSize`), `CourseResource.php` (`motion_file` any `application/json` on the public disk).
- Scenario: an admin (or an escalated one, see H1) uploads an SVG containing `<script>`; opened directly from `/storage/...` it runs on the site origin (stored XSS). Lottie files can contain expressions evaluated by lottie-web.
- Fix:
```php
FileUpload::make('image')->image()->acceptedFileTypes(['image/jpeg','image/png','image/webp'])->maxSize(4096)->disk('public')
```

### M6. Missing CSP and HSTS; cookie flags and debug depend on env
- Files: `app/Http/Middleware/SecurityHeaders.php`, `config/session.php` (`secure` from unset `SESSION_SECURE_COOKIE`, `encrypt` false), `.env.example` and `.env` (`APP_ENV=local`, `APP_DEBUG=true`).
- Scenario: nothing limits injected script if any XSS slips in; no HSTS allows downgrade on first visit; `APP_DEBUG=true` in production leaks stack traces and env values. `Permissions-Policy` grants `camera=(self)` though no feature uses it.
- Fix: add `Strict-Transport-Security` (HTTPS only), a CSP (`object-src 'none'; base-uri 'self'; frame-src` limited to video hosts), set `SESSION_SECURE_COOKIE=true`, `SESSION_ENCRYPT=true`, `APP_DEBUG=false` in production, drop `camera=(self)`.

### M7. `site()` hits the cache table on every call (N queries per page)
- File: `app/Support/helpers.php`. With `CACHE_STORE=database`, each `Cache::rememberForever` is a SELECT. `site()` is called dozens of times per page (layout, footer, views).
- Fix:
```php
static $memo = null;
$memo ??= Cache::rememberForever('settings.all', fn () => ...);
```
  or use the file/redis cache store.

### M8. Chat widget polls every 10 s on every page; chat table grows unbounded
- Files: `resources/views/livewire/chat-widget.blade.php:1` (`wire:poll.10s` on the root), `app/Livewire/ChatWidget.php`.
- Scenario: each open tab sends a Livewire request every 10 s (DB session write plus message query): 1000 visitors equal about 100 req/s of polling. Bots can fill `chat_messages` (see H2).
- Fix: poll only while the panel is open (`wire:poll.10s` on the message box under `x-show`, or `wire:poll.visible`), prune old chats with a scheduled command.

### M9. Images served at original size (190 MB, 467 files, 47 over 1 MB), no thumbnails
- Files: `storage/app/public/wp-media`; `Catalog::imageUrl` and `BlogShow::coverUrl` return originals; gallery and product cards render them directly.
- Scenario: a 24-item gallery page downloads tens of MB on mobile connections; poor Core Web Vitals.
- Fix: generate WebP thumbnails on import and upload, serve `srcset`, keep `loading="lazy"` and explicit width/height.

---

## LOW

### L1. Contact form logs full PII and never delivers the message
- File: `app/Livewire/Contact.php` (`Log::info('Contact message', $data)` writes name, email, phone, message to `storage/logs`; no mail or table; `MAIL_MAILER=log`). The honeypot and the rate limiter also run after validation, so invalid posts are not limited.
- Fix: persist in a `contact_messages` table or send mail, log only an id, and move limiter and honeypot before `validate()`.

### L2. Contact `subject` pre-fill conflicts with its validation
- Files: `app/Livewire/CourseShow.php` redirects to `contact?subject=<course title>`; `Contact.php` validates `subject` as `in:order,delivery,courses,other`. A paid-course enquiry arrives with a value that fails validation and blocks sending. Fix: pass `subject=courses` and put the title in `message`.

### L3. Blog sanitizer: protocol-relative and backslash URLs pass `isSafeUrl`
- File: `app/Support/SafeHtml.php`, regex `#^(https?:|mailto:|tel:|/|\#)#i`. `//evil.example/x` and `/\evil.example` begin with `/` and are accepted for `href` and `src`. Not script execution: `javascript:`, `data:`, `vbscript:` are rejected after whitespace/control stripping, `style` and `on*` attributes are removed, entities are decoded by the DOM before the check, SVG and unlisted tags are stripped by `strip_tags`. It allows off-site links and tracking images.
- Fix: `'#^(https?://|mailto:|tel:|/(?![/\\\\])|\#)#i'`.

### L4. LIKE search quirks (functional, not injectable: all values are bound)
- Files: `app/Livewire/BlogIndex.php` uses `addcslashes(..., '%_\\')`, but SQLite `LIKE` has no default escape char, so `%` and `_` still act as wildcards. It searches the raw JSON column, stored with `\uXXXX` escapes, so accented queries ("élevage") never match and a query such as "fr" matches every row (JSON keys). `Catalog::likePatterns` handles unicode escapes but strips `%_\` instead of escaping and has the same key-match issue.
- Fix: search `json_extract(title, '$.fr')` with `like ? escape '\'`, or a `Translatable` search scope.

### L5. Service worker caches authenticated pages; CDN assets without SRI
- Files: `public/sw.js` (caches every GET, including `/compte`, `/lecon/*`, `/admin/*`, and serves them offline); `resources/views/components/layouts/app.blade.php:47-48` (Font Awesome from cdnjs and Google Fonts, no `integrity`/`crossorigin`).
- Scenario: on a shared computer, a logged-out user can open cached account or lesson pages.
- Fix: restrict the SW to static paths (`/build/`, `/images/`, `/fonts/`), skip `/admin`, `/compte`, `/lecon`; add SRI or self-host.

### L6. Admin-supplied iframe/video URLs not restricted
- Files: `resources/views/livewire/lesson-player.blade.php` (`<iframe src="{{ $lesson->motion_url }}">`, `video_url`), `CourseResource.php` (`->url()` only). Escaping is correct but the scheme and host are not restricted and the iframe has no `sandbox`/`referrerpolicy`.
- Fix: `->rule('starts_with:https://')`, a host allow-list (YouTube/Vimeo), and `sandbox="allow-scripts allow-same-origin allow-presentation" referrerpolicy="strict-origin-when-cross-origin"`.

### L7. Mass assignment open on every model (`$guarded = []`)
- Files: all `app/Models/*.php` except `User` (`$fillable` includes `role`). No user input reaches `create()`/`update()` unfiltered today (Register passes validated data; Cart builds explicit arrays), so it is latent: a future `create($request->all())` or `fill()` could set `price`, `status`, `paid`, `user_id`, `role`.
- Fix: explicit `$fillable` on Order, Enrollment, Product, Course, PaymentGateway, Setting; remove `role` from `User::$fillable` and set it explicitly.

### L8. Simulator state is client-writable
- File: `app/Livewire/Simulator.php` (`state`, `index`, `history` public). A user can set any cash or bird count. No persistence, so no security impact; use `#[Locked]` or session-backed state if scores are ever stored or shared.

### L9. Hygiene
- `Login.php` always passes `remember=true`; make it opt-in.
- No email verification (`MustVerifyEmail` commented out in `User.php`): users can register with someone else's address.
- Newsletter `unique` message reveals whether an address is already subscribed.
- `app/Livewire/Checkout.php` and its view appear to be unused scaffolding; remove.
- `/lang/{locale}` is a state-changing GET (harmless; locale is allow-listed).

---

## Checked and found OK
- Blade output: the only raw `{!! !!}` are JSON-LD (`blog-show`, `faq`, `product-show`; encoded with `JSON_HEX_TAG|JSON_HEX_AMP`), the sanitized blog body, and `nl2br(e(...))` for lesson/product text. No `x-html`, `innerHTML` or `@json` into attributes. The lightbox only sets `<img :src>`.
- Lesson access: `LessonPlayer::mount` requires a paid enrollment and the route has `auth`. Free-course enrolment only when `price == 0`. `Account` lists only the current user's orders and enrolments.
- `SiteSettings::canAccess` is superadmin-only and `save()` writes only form-state keys.
- Cart prices are never taken from the client: `CartStore` re-reads active products and prices and clamps quantities to 1..99.
- `PaymentGateway.config` is `encrypted:array`. Livewire model properties (`Course`, `Product`, `Lesson`) are id-only snapshots.
- `session()->regenerate()` on login and register; logout invalidates the session; Filament login is throttled.
- SQL: all values bound; `selectRaw`/`orderByRaw` use constant strings.

## Dependency and database notes
- `composer.json`: laravel/framework ^12, livewire ^4.4, filament ^5.10, php ^8.2. Run `composer audit` and `npm audit` before release (not run here).
- SQLite does not index foreign keys automatically. Add indexes on `orders.user_id`, `order_items.order_id`, `order_items.product_id`, `products(is_active, category)`, `posts(is_active, published_at)`, `gallery_items(is_active, category, position)` (used by `Catalog::soldUnits`, Shop and Gallery queries).
