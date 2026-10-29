# Feuille de route de test (chemins multiples)

Légende : ✅ automatisé (PHP/JS, rejoué à chaque `php artisan test` / `npm run test:js`) · 🖐 vérifié à la main dans le navigateur le 29/10/2026 · ⏳ à faire.

## A. Parcours client — boutique de poussins
| # | Chemin | Statut |
|---|---|---|
| A1 | Accueil → catégorie → fiche produit → panier → virement (Western Union/Ria) → confirmation | ✅ CatalogTest / ApgTest |
| A2 | Filtres (catégorie, prix, stock), tri, recherche accentuée, pagination | ✅ CatalogTest |
| A3 | Panier : quantité 1..99, produit inconnu/inactif ignoré, rupture refusée | ✅ CatalogTest / SecurityTest |
| A4 | Moyen de paiement désactivé refusé ; stock décrémenté ; pas de survente | ✅ SecurityTest |
| A5 | Paiement en ligne (FedaPay/CinetPay/PayPal) avec vraies clés bac à sable | ⏳ (clés requises) |
| A6 | Commande depuis mobile 320/375 px sans débordement | 🖐 |

## B. Simulateur (histoire interactive)
| # | Chemin | Statut |
|---|---|---|
| B1 | Chair → bonnes décisions → « Lot réussi » (marge ≈ 40 %, 3 scénarios) | 🖐 |
| B2 | Œufs de table → « Marge serrée » | 🖐 |
| B3 | Reproduction → mauvaises décisions → popup risque (choisir autrement / retour / continuer) | 🖐 (popup affiché et fermé) |
| B4 | Formules du cahier des charges (Annexe A : 12,5 kg/j, 87,5 kg, 2 sacs, 765 vs 850 poussins, 320 œufs, 125 m², garde-fous) | ✅ 21 tests JS |
| B5 | Retour arrière, Rejouer, reprise après rechargement (localStorage) | ✅/🖐 |
| B6 | 375 px et 320 px : aucun défilement horizontal, HUD lisible | 🖐 |
| B7 | Rapport imprimable (@media print) | ⏳ impression réelle à tester |

## C. Formations
| # | Chemin | Statut |
|---|---|---|
| C1 | Visiteur → catalogue → page formation → inscription gratuite → leçon | ✅ ApgTest / CatalogTest |
| C2 | Non inscrit → accès leçon refusé (403) | ✅ SecurityTest |
| C3 | Leçon : animation Lottie / scènes intégrées / vidéo embarquée (sandbox) | ✅ LessonMotionTest, 🖐 /lecon/11 |
| C4 | Quiz parcours : bonne réponse → suite ; mauvaise → explication ; retour ; fin + score + leçon à revoir | ✅ LessonMotionTest, 🖐 /lecon/14 |
| C5 | Quiz : état verrouillé (impossible de tricher via Livewire) | ✅ SecurityTest / LessonMotionTest |
| C6 | Progression sauvegardée, bouton « terminée » | ✅ |
| C7 | Contenu réel des leçons WordPress reformulé + scènes + quiz personnalisés | ⏳ **bloqué : contenu protégé (connexion WordPress requise)** |

## D. Pages et contenu
| # | Chemin | Statut |
|---|---|---|
| D1 | FAQ : recherche, filtres, accordéon, JSON-LD | ✅ PagesTest |
| D2 | Galerie : filtres, charger plus, visionneuse clavier | ✅ HomeGalleryTest |
| D3 | Blog : liste, recherche, article, assainissement HTML | ✅ PagesTest |
| D4 | Contact : validation, honeypot, limite, enregistrement en base | ✅ PagesTest / ApgTest |
| D5 | Newsletter : validation, unicité, limite | ✅ HomeGalleryTest |
| D6 | FR/EN : toutes clés présentes dans les deux langues | ✅ CatalogTest |

## E. Sécurité et admin
| # | Chemin | Statut |
|---|---|---|
| E1 | Admin réservé aux rôles admin/superadmin ; utilisateurs & paiements superadmin | ✅ SecurityTest |
| E2 | En-têtes de sécurité, limites de tentatives (connexion, inscription, chat, contact) | ✅ |
| E3 | `composer audit`, `npm audit` : 0 faille connue | ✅ 29/10/2026 |

## F. Smoke test routes (29/10/2026)
`/accueil /boutique /produit/* /formations /formations/{slug} /simulateur /galerie /blog /faq /a-propos /contact /cgv /confidentialite /mentions-legales /panier /connexion /inscription /admin/login` → HTTP 200 sans erreur ; `/compte /lecon/* /admin` → redirection connexion (302).
