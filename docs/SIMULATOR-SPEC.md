# Simulateur APG — digest du cahier des charges (V1 + V4, textes complets dans docs/source/*.txt)

Principe directeur du document : **aucune performance Goliath n'est annoncée comme garantie**. Chaque valeur a un statut : `apg` (donnée communiquée par APG), `generique` (repère technique FAO/WOAH), `hypothese` (par défaut modifiable, à valider). L'interface montre toujours le statut + la mention « estimation indicative, pas une garantie ». Les valeurs ne sont PAS codées en dur dans l'UI : table de paramètres administrable (clé, valeur, unité, statut, note).

## Parcours (3 objectifs + mixte)
1. **Chair** : démarrage → croissance (finition non systématique pour Goliath). Objectif APG ≈ 45 jours à 2 mois ; poids >1,5 kg vers 45 j (autre indication >1 kg) → afficher « objectif indicatif », fourchette 1–1,5 kg.
2. **Œufs de table** : femelles seules (pas de mâles), œufs commercialisables, pas de poussins.
3. **Reproduction** (œufs fécondés + poussins) : mâles + femelles, phases démarrage → croissance → ponte, incubation, éclosion.
4. Mixte (sous-lots explicites). Un œuf ne peut être compté qu'une fois : table / couvaison / vente fécondé (somme des affectations ≤ 100 %).

## Données réelles APG (statut `apg`)
- Commande minimale **500 poussins** ; saisies types 500, 1 000, 2 000, 3 000, 5 000, 10 000 et plus.
- Ration chair : hypothèses **25 g** ou **30 g** /sujet/jour (à comparer, pas une ration fixe). Reproducteurs : départ 25 g, +5 g/semaine (⇒ semaine 20 = 120 g ; l'objectif 140 g à S20 est NON cohérent → afficher comme hypothèse non validée, ne pas extrapoler).
- Poids reproducteurs : éviter l'engraissement, cible APG ≲ 5 kg (à valider mâle/femelle) → alerte si dépassé, jamais de restriction brutale.
- Objectif ponte « jusqu'à ~320 œufs/femelle » = objectif/hypothèse, jamais garantie ; calcul période par période, pas effectif × 320.
- Réforme comparée à 18 vs 24 mois (durée de ponte effective).
- Prix boutique APG réels : poussins Goliath carton de 50 = 45 000 FCFA (Bénin), 55 000 (Togo/Burkina), 65 000 (monde) ⇒ 900 / 1 100 / 1 300 FCFA le poussin ; poulet Goliath congelé 3 000 FCFA/kg ; caille (lot) 40 000 ; pintade (lot) 80 000.

## Repères génériques (statut `generique`, modifiables)
- Densité adultes 4 sujets/m² (500→125 m², 1 000→250, 2 000→500, 3 000→750, 5 000→1 250, 10 000→2 500) ; poussinière 20–25 poussins/m² (500→20 m² à 25/m², 1 000→40, 2 000→80, 3 000→120, 5 000→200, 10 000→400). Surfaces = zone utile hors magasin, sas, local technique ; densité dépend du système/climat/ventilation.
- Températures poussins (à hauteur des poussins) : S1 32–35 °C, S2 29–32, S3 26–29, S4 23–26. Comportement : regroupés sous la chaleur = froid ; éloignés/haletants/ailes écartées = chaud ; répartis et actifs = ok. Zone de démarrage ~3 semaines ; transfert dès S4 selon plumage/température/comportement. Chauffage à combustion : risque CO, ventilation.
- Contrôles à l'arrivée : état, faibles/morts, eau propre + aliment immédiat, enregistrer les pertes, plan sanitaire avec un professionnel (pas de médicaments systématiques).
- Incubation : 1 000 œufs chargés, fertilité 90 % ⇒ 900 fertiles ; éclosion 85 % **sur œufs fertiles** ⇒ 765 poussins ; si 85 % est défini sur œufs chargés ⇒ 850 (ne jamais appliquer les deux). Le dénominateur doit être nommé et visible.
- Ponte : œufs = femelles vivantes × taux de ponte × jours (ex. 400 × 80 % = 320/j) ; pertes (cassés/sales) séparées.

## Formules
- Aliment jour (kg) = vivants × ration(g) ÷ 1000 ; semaine = somme ; cumul. Sacs = ⌈kg ÷ poids sac⌉ (25 ou 50 kg) ; gaspillage = paramètre séparé. Test : 500 × 25 g = 12,5 kg/j ; 7 j = 87,5 kg ; sacs de 50 kg = 2.
- Mortalité = morts ÷ effectif exposé au début de période ; les morts ne consomment plus.
- Finance : CA = Σ quantités vendues × prix ; marge d'exploitation = recettes − dépenses d'exploitation ; résultat complet = marge − amortissements ; seuil de rentabilité = charges fixes ÷ taux de marge sur coûts variables (sinon « non atteint ») ; coût unitaire ; prix d'équilibre. Préciser ce qui est inclus/exclu (investissement initial, intérêts, impôts).
- 3 scénarios : prudent / intermédiaire / favorable ; sensibilité : prix aliment, ponte, fertilité, éclosion, mortalité.
- Garde-fous (tests Annexe A) : taux >100 %, sac 0 kg, mâles+femelles > effectif, allocation d'œufs >100 %, effectifs/prix négatifs ⇒ erreur bloquante avec message précis, jamais de résultat trompeur. Inconnue ≠ zéro (« estimation non calculable »).
- Arrondis : affichage (1 décimale), commande (sac entier supérieur), devis (FCFA entier).

## Sorties finales (rapport imprimable)
Effectif/objectif, besoins alimentaires par période + cumul, sacs + coût, surfaces/équipements, programme chauffage + contrôles, objectifs de croissance, ponte/fertilité/éclosion/poussins, budget/recettes/marge, risques à surveiller, hypothèses + statuts + date + version + avertissement. Imprimer via CSS @media print (PDF navigateur).

## Santé/biosécurité (docs/source/cahier-simulateur-v4.txt §15-18) pour astuces et popups pédagogiques
Site drainé non inondable, toiture, litière sèche, ventilation sans courant d'air direct, anti-rongeurs/oiseaux sauvages, accès eau/chaleur, limiter contacts entre lots/visiteurs ; maladies prioritaires : influenza aviaire, Newcastle, bronchite infectieuse, Gumboro, Marek, coccidiose, salmonelloses/pullorose. Pas de programme médicamenteux : renvoyer au vétérinaire local ; signes graves (respiratoires/neurologiques/mortalité brutale) = appel immédiat au vétérinaire/autorité.

## Décisions d'implémentation (simulateur v2.0 — histoire interactive)
- **Architecture** : moteur pur `resources/js/sim/engine.js` (formules ci-dessus + garde-fous, erreurs `SimError` traduites via `sim.errors.*`), graphe `resources/js/sim/story.js`, composant Alpine `simulator()` dans `resources/js/sim/player.js`. Livewire `Simulator` injecte seulement la config (`App\Support\SimulatorConfig`, `@js`). Tout est client (pas d'aller-retour serveur), partie sauvegardée en localStorage (`apg.sim.v2`, invalidée si l'admin modifie params/histoire).
- **Données** : table `sim_params` (clé, valeur, unité, statut apg|generique|hypothese, groupe, libellé/note fr/en) + graphe dans `sim_steps` (nœuds : chapitre, scène, type, suite) et `sim_choices` (arêtes : effets clé/valeur, suite, objectifs visibles, mauvaise décision + astuce). Source de seed unique : `database/seeders/data/simulator.json` (aussi utilisée par les tests JS). Seeder idempotent : ne réécrit jamais les valeurs éditées dans l'admin.
- **Effets** : `set.x`, `add.x`, `mul.x`, `flag` ; valeur `@cle` = paramètre. Suite : code de nœud ou `chair=growth;oeufs=laying;repro=laying`.
- **Mortalité** : % du cycle = scénario + pénalités de décisions, morts répartis linéairement ; les morts ne mangent ni ne pondent.
- **Ration** : chair 25/30 g constants ou progressive ; pondeuses/reproducteurs : progressive APG plafonnée à S20 (120 g, jamais 140 g), « à volonté » = +20 % (hypothèse).
- **Finance** : charges = poussins + aliment (sacs entiers achetés) + coûts/sujet + nettoyage + incubation + main-d'œuvre + frais exceptionnels ; amortissement bâtiment neuf au prorata de la durée du cycle ; seuil de rentabilité = charges fixes ÷ taux de marge sur coûts variables (« non atteint » sinon). Fins : catastrophe (mortalité ≥ 25 % ou foyer ignoré), perte (résultat < 0), marge serrée (< 10 % des recettes), réussite.
- **Valeurs par défaut** : seules les données APG (poussins 900/1 100/1 300 FCFA, congelé 3 000 FCFA/kg, rations 25/30 g, règle +5 g, 500 minimum, ~320 œufs, ≲ 5 kg, ~1,5 kg à 45 j) ont le statut `apg` ; tous les prix/taux économiques sont des `hypothese` modifiables (admin > Simulateur (paramètres), ou « Réglages avancés » côté visiteur, non persistés serveur).
- **Limites connues** : pas de parcours « mixte » ; la reproduction peut afficher des recettes de poussins élevées (débouchés non modélisés, signalé dans les risques) ; courbes de poids/ponte non signées (hypothèses).
