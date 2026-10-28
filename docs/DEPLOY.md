# Mise en production — checklist (Laravel 12, PHP ≥ 8.2, SQLite)

## 1. Serveur
- PHP 8.2+ avec extensions : `intl`, `gd` (WebP), `sqlite3`/`pdo_sqlite`, `mbstring`, `fileinfo`, `zip`, `curl`, `openssl`.
- Document root = `public/`. HTTPS obligatoire (HSTS envoyé automatiquement en HTTPS).
- Droits d'écriture : `storage/`, `bootstrap/cache/`, `database/` (fichier `database.sqlite`).

## 2. Installation
```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build          # ou envoyer public/build déjà compilé
cp .env.example .env && php artisan key:generate
```
`.env` production : `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://…`, `APP_LOCALE=fr`, `SESSION_SECURE_COOKIE=true`, `SESSION_ENCRYPT=true`, `ADMIN_EMAIL=…`, `ADMIN_INITIAL_PASSWORD=…` (mot de passe fort, à changer après la 1ʳᵉ connexion), `DB_CONNECTION=sqlite`, `MAIL_*` si e-mails.
```bash
touch database/database.sqlite
php artisan migrate --force
php artisan db:seed --force      # crée le superadmin depuis ADMIN_* (jamais admin@apg.test hors local), catalogue, galerie, simulateur
php artisan storage:link
php artisan media:thumbs         # miniatures WebP des médias WordPress
php artisan optimize             # config/route/view cache
```

## 3. Médias (non versionnés)
`storage/app/public/wp-media/` (≈190 Mo, téléchargés par `node scripts/wp-media-download.mjs`) et `storage/app/public/thumbs/` : à copier sur le serveur (rsync/zip) ou à régénérer. Les photos officielles APG sont versionnées dans `database/seeders/assets/apg` (copiées par `ApgPhotosSeeder`).

## 4. Après déploiement (Admin `/admin`, compte superadmin)
1. Paramètres : identité, WhatsApp, e-mail, clé IA (chiffrée).
2. Moyens de paiement : activer et saisir les clés (FedaPay / CinetPay / PayPal : tester en bac à sable ; KKiaPay/Paydunya = confirmation manuelle). Webhooks : la commande n'est marquée payée que manuellement tant que la vérification serveur n'est pas branchée.
3. Simulateur : valider les paramètres (statut APG / générique / hypothèse) avec le référent technique — voir `docs/SIMULATOR-SPEC.md`.
4. Galerie / Blog / Produits / Formations : relire, masquer les visuels hors sujet.
5. Pages légales (CGV, confidentialité, mentions) : faire valider par un juriste.
6. Tester une commande (virement), une inscription, un message de contact (Admin > Messages de contact).

## 5. Qualité
`php artisan test` · `npm run test:js` (moteur du simulateur) · `composer audit` · `npm audit` · revue : `docs/REVIEW.md`.
