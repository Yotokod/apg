# APG — Roadmap technique (Lead dev / chef technique)

Objectif : plateforme compacte et pro, inspirée de raw-to-pro.koderla.com (architecture/contenu), Coursera (formation) et de sites e-commerce, adaptée à Africa Poulets Goliath. Primaire orange `#D9480F`, secondaire encre `#0B0B2B`, fond blanc.

## Équipe (sous-agents ↔ skills)
| Pôle | Skills | Périmètre exclusif (fichiers) |
|---|---|---|
| R — Recherche UX/contenu | ui-ux-pro-max | `docs/DESIGN-BRIEF.md` |
| D — Data & backend | senior-backend, senior-architect, clean-code | migrations, models, seeders, `app/Filament/**` (galerie, blog), `database/seeders/data/**` |
| F4 — Illustrations motion | ui-ux-pro-max, senior-frontend | `resources/views/components/illustrations/**`, `resources/css/illustrations.css` |
| F1 — Layout, Accueil, Galerie | senior-frontend, tailwind-patterns, ui-ux-pro-max | `layouts/app.blade.php`, `Home*`, `Gallery*`, `routes/gallery.php`, `lang/{fr,en}.json` |
| F2 — Pages statiques & blog | senior-frontend, seo-optimizer | `About/Faq/Legal/Blog*/Contact*`, `routes/pages.php`, `lang/*/pages.php` |
| F3 — Boutique & Formations | senior-frontend, senior-backend | `Shop*/Product*/Courses*/CourseShow*/Cart*`, `routes/catalog.php`, `lang/*/catalog.php` |
| QA — Sécurité & revue | senior-security, code-reviewer | lecture seule puis correctifs ciblés |

## Phases
1. **Recherche + données** (R, D, F4 en parallèle) — médiathèque WP téléchargée (464 images → `storage/app/public/wp-media`, manifest.json), brief design, table galerie, blog importé, illustrations animées.
2. **Interfaces** (F1, F2, F3 en parallèle) — footer riche, accueil type landing pro (hero, widgets, bande confiance, catégories, produits, formations, simulateur, galerie, blog, témoignages, FAQ, CTA), galerie + section accueil, pages À propos / FAQ / CGV / confidentialité / blog, fiche produit, catalogue filtrable, page formation type Coursera.
3. **Intégration & qualité** — build, tests, revue sécurité (CSRF, XSS, uploads, IDOR), revue de code, QA navigateur responsive (375/768/1024/1440), a11y, performance (lazy images).

## Règles communes
FR/EN obligatoires (aucun texte en dur), identité via `site()`, icônes Font Awesome, mouvement via `data-reveal/data-stagger/data-count/data-lottie` (`resources/js/motion.js`), respect `prefers-reduced-motion`, pas d'emoji-icônes, cibles tactiles ≥ 44 px, contraste ≥ 4.5:1, images `loading="lazy"` + `alt`.
