# Import des leçons WordPress — plan et état

## Constat (29/10/2026)
- Le contenu des leçons est **protégé** côté WordPress (« This content is protected, please login and enroll »). Les titres, sections, durées et URLs sont publics ; les textes ne sont lisibles qu'avec une session connectée.
- La base actuelle contient 10 formations, 48 sections, 113 leçons mais **les textes des leçons sont vides** (seuls les titres ont été importés lors du premier passage). Les quiz importés (98 questions) existent.
- Le site WP réutilise des modèles de démonstration LearnPress (slugs `prenatal-yoga`, `the-secrets-to-drawing`… contiennent du contenu avicole) : l'association titre ↔ cours doit se faire depuis l'admin WordPress.
- Sauvegardes : `../backups/database-*.sqlite` + tag git `backup-before-lessons-*`. Rien n'a été modifié dans les données.

## Pipeline prévu (dès qu'une session WordPress est ouverte dans le navigateur intégré)
1. **Récolte** (lecture seule) : dans le navigateur connecté, parcourir chaque URL de leçon/quiz de `database/seeders/data/wp_curriculum.json` et extraire le contenu (`.content-item-description`) → JSON brut (hors git).
2. **Reformulation** (sous-agents par module) : texte simplifié FR+EN (phrases courtes, points clés, vocabulaire de l'éleveur), sans ajout de faits non présents dans la source ; 3 à 5 scènes de motion design par leçon (illustrations existantes) ; quiz personnalisé (3–5 nœuds, explications, chemins de remédiation).
3. **Import idempotent** : `WpLessonsSeeder` met à jour les leçons par (cours, position) sans rien supprimer ; anciens quiz conservés tant qu'un nouveau n'est pas fourni.
4. **Tests** : leçon par leçon (rendu, scènes, quiz branche bonne/mauvaise, mobile).
