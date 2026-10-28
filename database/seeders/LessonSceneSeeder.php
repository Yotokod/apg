<?php

namespace Database\Seeders;

use App\Models\Lesson;
use Illuminate\Database\Seeder;

/**
 * Demo animated-scene lessons (lessons.scenes). Idempotent: only overwrites the `scenes` column
 * of lessons found by their French title. Facts come from docs/SIMULATOR-SPEC.md (generic
 * references or APG data, never guarantees).
 */
class LessonSceneSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->scenesByTitle() as $title => $scenes) {
            Lesson::query()->get()
                ->filter(fn (Lesson $l) => ($l->title['fr'] ?? null) === $title)
                ->each(fn (Lesson $l) => $l->update(['scenes' => $scenes]));
        }
    }

    private function scene(string $illustration, array $title, array $caption, array $pointsFr, array $pointsEn, int $duration = 8, ?string $icon = null): array
    {
        return [
            'illustration' => $illustration, 'icon' => $icon, 'title' => $title, 'caption' => $caption,
            'points' => ['fr' => $pointsFr, 'en' => $pointsEn], 'duration' => $duration,
        ];
    }

    private function scenesByTitle(): array
    {
        return [
            'Logement' => [
                $this->scene('density',
                    ['fr' => 'La bonne densité', 'en' => 'The right stocking density'],
                    ['fr' => 'Repère générique : environ 4 sujets adultes par m² de zone utile. 500 sujets → 125 m², 1 000 → 250 m².', 'en' => 'Generic benchmark: about 4 adult birds per m² of usable area. 500 birds → 125 m², 1,000 → 250 m².'],
                    ['Surface utile hors magasin, sas et local technique', 'La densité dépend du système, du climat et de la ventilation', 'Poussinière : 20 à 25 poussins/m²'],
                    ['Usable area excludes storage, airlock and technical room', 'Density depends on the system, climate and ventilation', 'Brooding area: 20 to 25 chicks/m²'],
                    9),
                $this->scene('farm-house',
                    ['fr' => 'Un bâtiment sain', 'en' => 'A healthy poultry house'],
                    ['fr' => 'Site drainé et non inondable, toiture solide, litière sèche et accès à l\'eau.', 'en' => 'Well-drained, flood-free site, solid roof, dry litter and access to water.'],
                    ['Ventilation sans courant d\'air direct sur les animaux', 'Litière toujours sèche', 'Accès facile à l\'eau et à la chaleur'],
                    ['Ventilation without direct draughts on the birds', 'Litter always dry', 'Easy access to water and heat'],
                    8),
                $this->scene('biosecurity',
                    ['fr' => 'La biosécurité', 'en' => 'Biosecurity'],
                    ['fr' => 'Protégez le bâtiment des rongeurs et des oiseaux sauvages, et limitez les contacts entre lots et visiteurs.', 'en' => 'Protect the house from rodents and wild birds, and limit contact between flocks and visitors.'],
                    ['Barrières anti-rongeurs et anti-oiseaux sauvages', 'Limiter les visiteurs et les contacts entre lots', 'Signes graves : appeler immédiatement le vétérinaire'],
                    ['Rodent and wild-bird barriers', 'Limit visitors and contact between flocks', 'Severe signs: call the veterinarian immediately'],
                    8),
            ],
            'Les paramètres de température et d\'humidité' => [
                $this->scene('temperature',
                    ['fr' => 'La température semaine par semaine', 'en' => 'Temperature week by week'],
                    ['fr' => 'Mesurée à hauteur des poussins : 32–35 °C la 1re semaine, puis environ 3 °C de moins chaque semaine jusqu\'à 23–26 °C en semaine 4.', 'en' => 'Measured at chick height: 32–35 °C in week 1, then about 3 °C less each week down to 23–26 °C in week 4.'],
                    ['S1 : 32–35 °C · S2 : 29–32 °C', 'S3 : 26–29 °C · S4 : 23–26 °C', 'Repères génériques, à adapter au bâtiment'],
                    ['W1: 32–35 °C · W2: 29–32 °C', 'W3: 26–29 °C · W4: 23–26 °C', 'Generic benchmarks, adapt to your house'],
                    10),
                $this->scene('chick-growth',
                    ['fr' => 'Lire le comportement', 'en' => 'Read the behaviour'],
                    ['fr' => 'Les poussins vous disent s\'ils ont froid ou chaud : observez leur répartition plusieurs fois par jour.', 'en' => 'Chicks tell you if they are cold or hot: watch how they spread out several times a day.'],
                    ['Regroupés sous la chaleur : ils ont froid', 'Éloignés, haletants, ailes écartées : ils ont chaud', 'Répartis et actifs : tout va bien'],
                    ['Huddled under the heat: they are cold', 'Far away, panting, wings spread: they are hot', 'Spread out and active: all good'],
                    9),
                $this->scene('',
                    ['fr' => 'Chauffage et ventilation', 'en' => 'Heating and ventilation'],
                    ['fr' => 'Un chauffage à combustion produit du monoxyde de carbone : ventilez toujours, sans courant d\'air direct.', 'en' => 'Combustion heaters produce carbon monoxide: always ventilate, without direct draughts.'],
                    ['Zone de démarrage pendant environ 3 semaines', 'Transfert dès S4 selon plumage, température et comportement'],
                    ['Brooding zone for about 3 weeks', 'Move from W4 depending on feathering, temperature and behaviour'],
                    8, 'fa-wind'),
            ],
            'L\'alimentation et l\'abreuvoir des poussins dès leur arrivée' => [
                $this->scene('',
                    ['fr' => 'Contrôles à l\'arrivée', 'en' => 'Checks on arrival'],
                    ['fr' => 'Dès l\'arrivée, vérifiez l\'état des poussins, retirez les faibles ou morts et enregistrez les pertes.', 'en' => 'On arrival, check the chicks, remove weak or dead ones and record the losses.'],
                    ['État général du lot', 'Enregistrer les pertes', 'Plan sanitaire avec un professionnel, pas de médicaments systématiques'],
                    ['Overall condition of the flock', 'Record the losses', 'Health plan with a professional, no routine medication'],
                    8, 'fa-clipboard-check'),
                $this->scene('feeding',
                    ['fr' => 'Eau propre et aliment tout de suite', 'en' => 'Clean water and feed right away'],
                    ['fr' => 'Les poussins doivent trouver immédiatement de l\'eau propre et de l\'aliment.', 'en' => 'Chicks must find clean water and feed immediately.'],
                    ['Eau propre disponible en permanence', 'Aliment accessible dès l\'arrivée'],
                    ['Clean water always available', 'Feed accessible from arrival'],
                    7),
                $this->scene('feed-calc',
                    ['fr' => 'Calculer l\'aliment', 'en' => 'Work out the feed'],
                    ['fr' => 'Aliment du jour = sujets vivants × ration ÷ 1 000. Exemple : 500 × 25 g = 12,5 kg/jour, soit 87,5 kg sur 7 jours, donc 2 sacs de 50 kg.', 'en' => 'Daily feed = live birds × ration ÷ 1,000. Example: 500 × 25 g = 12.5 kg/day, i.e. 87.5 kg over 7 days, so 2 bags of 50 kg.'],
                    ['Ration de 25 g ou 30 g : hypothèses à comparer', 'Sacs arrondis à l\'entier supérieur', 'Les sujets morts ne consomment plus'],
                    ['25 g or 30 g ration: assumptions to compare', 'Bags rounded up to the next whole bag', 'Dead birds no longer eat'],
                    10),
            ],
        ];
    }
}
