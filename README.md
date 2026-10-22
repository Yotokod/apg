# Africa Poulets Goliath (APG)

Laravel 12 · Livewire 4 · Filament 5 · Tailwind 4 · SQLite · PWA.

```bash
composer install && npm install && npm run build
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
php artisan serve          # site: /accueil   admin: /admin (admin@apg.test / password — change it)
php artisan test
```

- **Identity**: `config/site_identity.php` (overridable in Admin > Paramètres). Read via `site('key')`.
- **Modes**: `app_mode` = `one_market` | `marketplace` (plans shown on the landing page in marketplace mode).
- **i18n**: all UI strings in `lang/fr.json` / `lang/en.json`; content models store `{fr,en}` JSON.
- **Payments**: Admin > Moyens de paiement. FedaPay, CinetPay, PayPal create a hosted checkout; KKiaPay/Paydunya are config-only
  (widget/manual confirmation); transfers (Western Union, MoneyGram, Ria) show instructions + an OpenStreetMap agent search.
  Orders are marked paid manually by admin until gateway webhooks are verified with real keys.
- **Quiz**: Admin > Quiz (étapes): each answer can point to a different next question (branching path).
- **Simulator**: Admin > Simulateur (étapes); bad choices (`is_bad`) open an explanation popup with a back button.
- **Chat**: bottom-right widget; AI replies if `ai_api_key` is set (Admin > Paramètres), else FAQ fallback; agents reply from Admin > Chat.
- Legacy content import: place `database/seeders/data/apg_content.json` then `php artisan db:seed`.
