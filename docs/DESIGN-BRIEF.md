# APG — Design Brief (UX/contenu)

Sources étudiées (synthèse, pas de copie) : raw-to-pro.koderla.com (hero + filtres + stats + confiance, explorer par domaine, "à la une", explication en étapes, espaces par audience, partenaires ; sous-pages : découvrir, comment ça marche, recruteur, partenaires), Coursera (catalogue : filtres Sujet/Durée/Niveau/Langue + tri, cartes avec fournisseur/note/niveau/durée/badges, pagination numérotée, blocs promo inline ; page cours : fil d'Ariane, CTA inscription + nb d'inscrits, bandeau stats, onglets d'ancres, syllabus en accordéons, formateur, avis avec répartition d'étoiles, FAQ, cours liés), Jumia (barre utilitaire, recherche, filtres latéraux, puces de filtres actifs mobile, badges promo/stock/livraison, paiement à la livraison, prix barré + % remise, texte SEO + FAQ, footer paiements, chat WhatsApp).
Identité : orange `#D9480F`, encre `#0B0B2B`, fond blanc. Tout texte via FR/EN, identité via `site()`.

## 1. Sitemap
- `/` Accueil
- `/boutique` (catégories : Poussins, Œufs, Poulets Goliath, Cailles, Pintades) → `/boutique/{categorie}` → `/produit/{slug}`
- `/panier`, `/commande` (livraison + paiement + récap)
- `/formations` → `/formations/{slug}` (+ `/mes-formations` espace apprenant)
- `/galerie`, `/a-propos`, `/faq`, `/contact`
- `/blog` → `/blog/{slug}` (+ catégories/tags)
- `/cgv`, `/confidentialite`, `/mentions-legales`, `/livraison`
- Compte : connexion, inscription, commandes, suivi.

## 2. Navigation
- **Barre d'annonce** (40 px, fermable) : "Livraison par avion et taxi vers tout le Bénin" / "Commande WhatsApp +229 67 16 00 53". Rotation 5 s.
- **Navbar card** (64 px desktop, 56 mobile, sticky, arrondi, ombre légère) : logo | Accueil · Boutique (méga-menu 5 catégories + icônes) · Formations · Galerie · Blog · À propos | recherche (icône → overlay) · FR/EN · thème · panier (badge) · compte · CTA orange "Commander" .
- Mobile : burger → tiroir plein hauteur, accordéons ; barre d'actions basse optionnelle (Accueil, Boutique, Panier, WhatsApp).
- Fil d'Ariane sur toutes les pages sauf accueil (dans le hero compact).

## 3. Pages (ordre des sections)

### Accueil (hero 350–400 px max, élastique)
1. Hero : titre "Élevage de volailles d'élite au Bénin", sous-titre (poussins, œufs, Poulet Goliath, formations), 2 CTA ("Voir la boutique", "Nous écrire sur WhatsApp"), mini-barre de recherche produit + sélecteur catégorie (idée filtre-hero Raw to Pro), carte produit vedette flottante.
2. Bande de confiance (4 items) : Livraison avion/taxi · Élevage suivi · Paiement à la livraison / Mobile Money (à valider) · Support WhatsApp.
3. Compteurs animés (chiffres réels fournis par le client uniquement : années d'expérience, clients, formations) — sinon masquer.
4. Tuiles catégories (5, icône + image + nb produits).
5. Produits populaires (carrousel/grille 4 col, cartes prix).
6. Poulet Goliath mis en avant (bloc 2 col : image + atouts : croissance, rusticité, poids — uniquement faits validés ; CTA fiche).
7. Formations (3 cartes : niveau, durée, prix, badge) + CTA "Voir toutes les formations".
8. Comment commander en 4 étapes (choisir → confirmer WhatsApp/panier → paiement → livraison avion/taxi).
9. Zones de livraison : Porto-Novo, Cotonou, Parakou (+ expéditions par avion/taxi), carte/liste de villes.
10. Simulateur (coût/rentabilité d'un lot — optionnel, ROADMAP).
11. Galerie (6 vignettes mosaïque + lien).
12. Blog (3 derniers articles).
13. Témoignages (slider, note, ville, photo).
14. FAQ (accordéon 5 questions) + CTA final bandeau orange "Parlons de votre projet d'élevage".

### Boutique
Hero compact 60–150 px (titre, fil d'Ariane, nb produits). Barre filtres/tri sticky : chips catégories, tri (popularité, nouveautés, prix ↑/↓), recherche. Sidebar (desktop) / tiroir (mobile) : catégorie, prix (slider), disponibilité, âge/poids, livraison. Puces filtres actifs + "Tout effacer". Grille 4/3/2 col. Cartes : image 4:3, badge (Nouveau, Promo -%, Stock faible, Rupture), nom (truncate 2 lignes), unité/prix, bouton "Ajouter" + icône WhatsApp. Pagination numérotée + skeletons. Bloc texte SEO + FAQ en pied.

### Fiche produit
Fil d'Ariane ; galerie (vignettes + zoom) | titre, note (étoiles + nb avis), prix (barré + % si promo), stock, sélecteur quantité/variante (lot de 10, 50...), CTA "Ajouter au panier" + "Commander via WhatsApp" ; puces avantages (livraison, paiement, conseils). Livraison : estimateur par ville (Porto-Novo/Cotonou/Parakou/autre). Onglets/ancres : Description · Caractéristiques · Conseils d'élevage · Livraison · Avis. Produits liés, formation liée ("Apprenez à élever ce volatile"). Barre d'achat sticky mobile (prix + bouton).

### Formations (catalogue)
Hero compact ("Apprenez l'élevage de volailles") + stats. Filtres (Niveau, Espèce, Durée, Langue, Gratuit/payant) + tri ; cartes style Coursera : image, titre, formateur, niveau, durée, nb leçons, note, nb apprenants, prix, badges (Populaire, Débutant, Nouveau). Bloc "Quel est votre objectif ?" (démarrer un élevage / améliorer / vendre). Parcours recommandés. Pagination, FAQ, CTA.

### Page formation
Hero : fil d'Ariane, titre, promesse, formateur, note, nb inscrits, CTA "S'inscrire" + prix ; carte d'achat sticky (prix, inclus : vidéos, supports PDF, certificat, accès à vie, support WhatsApp). Bandeau stats (niveau, durée, leçons, langue). Onglets d'ancres : Présentation · Ce que vous apprendrez (liste 6–8 acquis ✓) · Programme (accordéons modules : nb leçons, durée) · Formateur · Avis (répartition étoiles) · FAQ. Prérequis, public cible. Formations liées. Progression apprenant : barre de progression, leçons cochées, "Reprendre".

### Galerie
Hero compact ; filtres chips (Poussins, Goliath, Élevage, Livraison, Formations) ; mosaïque masonry lazy ; lightbox (navigation clavier, légende, partage) ; pagination/"Voir plus"; CTA WhatsApp.

### À propos
Hero ; histoire & mission ; chiffres ; valeurs (3–4 cartes) ; savoir-faire (poulet Goliath, sélection, suivi sanitaire) ; équipe ; implantation (Porto-Novo, Cotonou, Parakou) ; process livraison ; partenaires ; CTA contact.

### FAQ
Hero + recherche ; onglets catégories (Commande, Livraison, Produits, Formations, Paiement) ; accordéons (un ouvert) ; "Pas trouvé ?" carte contact WhatsApp. Schéma FAQPage JSON-LD.

### Contact
Hero ; 2 colonnes : formulaire (nom, téléphone, e-mail, sujet, message, consentement, anti-spam honeypot, toast succès) | cartes : WhatsApp +229 67 16 00 53, téléphone, e-mail, horaires, adresses des sites (Porto-Novo/Cotonou/Parakou), carte, réseaux. Boutons rapides WhatsApp.

### Blog
Hero ; article vedette ; filtres catégories + recherche ; grille 3 col (image, catégorie, titre, extrait, date, temps de lecture) ; pagination ; sidebar : populaires, newsletter. Article : sommaire, partage, auteur, articles liés, CTA produit/formation.

### CGV / Confidentialité / Mentions
Hero minimal ; sommaire collant à gauche ; sections numérotées ; date de mise à jour ; contact DPO/support. Contenu rédigé depuis `site()` (raison sociale, adresse).

## 4. Footer (fond encre, 4–5 colonnes)
1. Marque : logo, 2 lignes de présentation, réseaux (Facebook, Instagram, YouTube, TikTok, WhatsApp).
2. Boutique : 5 catégories, promos, suivi commande.
3. Apprendre : formations, blog, galerie, FAQ.
4. Société : à propos, contact, livraison, retours.
5. Contact : WhatsApp +229 67 16 00 53, e-mail, villes (Porto-Novo, Cotonou, Parakou), horaires.
Rangée newsletter (champ + bouton + consentement). Rangée moyens de paiement (Mobile Money, cash à la livraison, carte — seulement ceux réellement actifs). Barre légale : © année · CGV · Confidentialité · Mentions · sélecteur langue/thème · "retour en haut".

## 5. Widgets réutilisables
Barre d'annonce · fil d'Ariane · bande de confiance · compteurs (`data-count`) · tuiles catégories · carte produit / carte prix · carte formation · badges (Nouveau, Promo, Stock faible, Populaire, Niveau) · filtres + tri + chips actifs · pagination · accordéon FAQ · slider témoignages · étoiles/avis + répartition · onglets d'ancres sticky · étapes numérotées · estimateur livraison · barre de progression formation · stepper checkout · toasts · skeletons · lightbox · newsletter · bouton WhatsApp flottant (chat bas-droite, se replie après 5 s sans scroll, alterne visible/caché toutes les 5 s, caché pendant le scroll) · retour en haut · bannière cookies (refuser par défaut).

## 6. Densité & mise en page
- Conteneur max 1200 px, gouttière 16 px mobile / 24 px desktop. Échelle d'espacement 4-8-12-16-24-32 ; sections py 32–48 px (jamais > 64).
- Titres : H1 28–36 px, H2 22–26 px, corps 14–15 px, interlignage 1.5. Rayons 12 px cartes, 8 px champs ; ombres douces ; bordures 1 px grises.
- Cartes produit : 4 col ≥1024, 3 ≥768, 2 mobile ; image 4:3 ; padding 12 px ; bouton h 40 px (cible ≥44 px tactile). Cartes formation : 3 col, image 16:9.
- Hero landing ≤400 px ; autres pages 60–150 px (motif/dégradé orange→encre, titre + fil d'Ariane + widget).
- Contraste ≥4.5:1 ; texte orange sur blanc uniquement en gras ≥18 px ; boutons primaires orange texte blanc.
- Aucun élément masqué en responsive ; `truncate` sur titres ; images `loading="lazy"` + `alt`; `prefers-reduced-motion` respecté.
- Micro-interactions : reveal au scroll, hover lift 2 px, 200 ms.

## 7. Ton & wording CTA
Direct, rassurant, local : "Commander", "Ajouter au panier", "Écrire sur WhatsApp", "Voir la formation", "S'inscrire", "Demander un devis", "Suivre ma commande". Éviter superlatifs non prouvés ; chiffres/avis/prix uniquement réels (sinon placeholders admin éditables).
