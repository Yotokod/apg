<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\PaymentGateway;
use App\Models\Plan;
use App\Models\Product;
use App\Models\QuizNode;
use App\Models\SimChoice;
use App\Models\SimStep;
use App\Models\User;
use App\Services\PaymentManager;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAdmin();

        foreach (array_keys(PaymentManager::GATEWAYS) as $i => $code) {
            [$name, $type] = PaymentManager::GATEWAYS[$code];
            PaymentGateway::firstOrCreate(['code' => $code], [
                'name' => $name, 'type' => $type, 'position' => $i, 'enabled' => $type === 'transfer',
                'instructions' => $type === 'transfer' ? [
                    'fr' => "1. Rendez-vous chez un agent $name.\n2. Envoyez le montant au nom du bénéficiaire communiqué par WhatsApp.\n3. Envoyez-nous la référence (MTCN / numéro de transfert) par WhatsApp.",
                    'en' => "1. Visit a $name agent.\n2. Send the amount to the beneficiary name given on WhatsApp.\n3. Send us the reference (MTCN / transfer number) on WhatsApp.",
                ] : null,
            ]);
        }

        $this->seedFromScrape();
        $this->seedProducts();
        $this->seedSimulator();
        $this->seedPlans();
        $this->call([ProductImageSeeder::class, GallerySeeder::class, PostSeeder::class]);
    }

    /** Demo admin only in local/testing; elsewhere ADMIN_EMAIL + ADMIN_INITIAL_PASSWORD are required. */
    private function seedAdmin(): void
    {
        if (app()->environment(['local', 'testing'])) {
            $email = 'admin@apg.test';
            $password = 'password';
        } else {
            $email = env('ADMIN_EMAIL');
            $password = env('ADMIN_INITIAL_PASSWORD');
            if (! $email || ! $password) {
                throw new \RuntimeException('Set ADMIN_EMAIL and ADMIN_INITIAL_PASSWORD to create the first superadmin.');
            }
        }
        User::unguarded(fn () => User::firstOrCreate(['email' => $email], ['name' => 'Super Admin', 'password' => $password, 'role' => 'superadmin']));
    }

    /** Import content extracted from the legacy WordPress site, if present. */
    private function seedFromScrape(): void
    {
        $file = __DIR__.'/data/apg_content.json';
        if (! is_file($file) || ! ($d = json_decode(file_get_contents($file), true))) {
            $this->seedDemoCourse();

            return;
        }
        foreach ($d['testimonials'] ?? [] as $t) {
            \App\Models\Testimonial::firstOrCreate(['name' => $t['name'], 'text' => $t['text']], ['country' => $t['country'] ?? null]);
        }
        foreach ($d['products'] ?? [] as $p) {
            Product::updateOrCreate(['slug' => $p['slug'] ?? Str::slug($p['name'])], [
                'name' => ['fr' => $p['name']], 'description' => ['fr' => $p['description_fr'] ?? ''],
                'category' => $p['category'] ?? 'poulet', 'price' => (int) ($p['price_fcfa'] ?? 0), 'stock' => 100,
            ]);
        }
        foreach ($d['courses'] ?? [] as $c) {
            $course = Course::updateOrCreate(['slug' => $c['slug'] ?? Str::slug($c['title'])], [
                'title' => ['fr' => $c['title']], 'description' => ['fr' => $c['description'] ?? ''], 'price' => (int) ($c['price'] ?? 0),
            ]);
            foreach ($c['modules'] ?? [] as $mi => $m) {
                $mod = $course->modules()->updateOrCreate(['position' => $mi], ['title' => ['fr' => $m['title']]]);
                foreach ($m['lessons'] ?? [] as $li => $l) {
                    $lesson = $mod->lessons()->updateOrCreate(['position' => $li], ['title' => ['fr' => $l['title']], 'content' => ['fr' => strip_tags($l['content_html_or_text'] ?? '')]]);
                    $this->seedQuiz($lesson, $l['quiz'] ?? []);
                }
            }
        }
        if (! Course::exists()) {
            $this->seedDemoCourse();
        }
    }

    private function seedQuiz(Lesson $lesson, array $questions): void
    {
        $lesson->nodes()->delete();
        $nodes = [];
        foreach ($questions as $i => $q) {
            $nodes[$i] = $lesson->nodes()->create(['question' => ['fr' => $q['question']], 'is_start' => $i === 0]);
        }
        foreach ($questions as $i => $q) {
            foreach ($q['options'] ?? [] as $oi => $label) {
                $nodes[$i]->options()->create([
                    'label' => ['fr' => $label], 'is_correct' => $oi === ($q['correct_index'] ?? 0),
                    'next_node_id' => $nodes[$i + 1]->id ?? null,
                ]);
            }
        }
    }

    private function seedDemoCourse(): void
    {
        $course = Course::firstOrCreate(['slug' => 'demarrer-elevage-goliath'], [
            'title' => ['fr' => 'Démarrer un élevage de poulets Goliath', 'en' => 'Start a Goliath chicken farm'],
            'description' => ['fr' => 'Les bases : bâtiment, alimentation, santé et rentabilité.', 'en' => 'Basics: housing, feeding, health and profitability.'],
            'price' => 0,
        ]);
        if ($course->modules()->exists()) {
            return;
        }
        $mod = $course->modules()->create(['title' => ['fr' => 'Module 1 : Les fondamentaux', 'en' => 'Module 1: Fundamentals'], 'position' => 0]);
        $lesson = $mod->lessons()->create(['position' => 0, 'title' => ['fr' => 'Choisir et préparer le bâtiment', 'en' => 'Choosing and preparing the house'],
            'content' => ['fr' => "Un bon bâtiment est ventilé, sec et protégé des prédateurs.\nPrévoir environ 8 à 10 sujets par m².", 'en' => "A good house is ventilated, dry and predator-proof.\nPlan about 8 to 10 birds per m²."]]);
        $a = $lesson->nodes()->create(['is_start' => true, 'question' => ['fr' => 'Quelle densité pour des poulets en bâtiment ?', 'en' => 'What stocking density for housed chickens?'],
            'explanation' => ['fr' => 'Trop dense = stress, maladies et mortalité.', 'en' => 'Too dense = stress, disease and mortality.']]);
        $b = $lesson->nodes()->create(['question' => ['fr' => 'Que contrôler en priorité chaque jour ?', 'en' => 'What to check first every day?']]);
        $a->options()->createMany([
            ['label' => ['fr' => '8 à 10 sujets/m²', 'en' => '8 to 10 birds/m²'], 'is_correct' => true, 'next_node_id' => $b->id],
            ['label' => ['fr' => '30 sujets/m²', 'en' => '30 birds/m²'], 'is_correct' => false, 'next_node_id' => $b->id],
        ]);
        $b->options()->createMany([
            ['label' => ['fr' => 'Eau, aliment et ventilation', 'en' => 'Water, feed and ventilation'], 'is_correct' => true],
            ['label' => ['fr' => 'La couleur du toit', 'en' => 'Roof color'], 'is_correct' => false],
        ]);
    }

    private function seedProducts(): void
    {
        if (Product::exists()) {
            return;
        }
        foreach ([
            ['Poussin Goliath (1 jour)', 'poussin', 1200, 'Poussin vigoureux, vacciné.'],
            ['Œufs fécondés Goliath (plateau)', 'oeuf', 6000, 'Plateau de 30 œufs à couver.'],
            ['Poulet Goliath adulte', 'poulet', 12000, 'Sujet de grande taille, prêt pour la vente ou la reproduction.'],
            ['Caille (couple)', 'caille', 3000, 'Couple reproducteur.'],
            ['Pintade', 'pintade', 5000, 'Sujet adulte.'],
        ] as [$n, $c, $p, $d]) {
            Product::create(['name' => ['fr' => $n], 'slug' => Str::slug($n), 'category' => $c, 'price' => $p, 'stock' => 100, 'description' => ['fr' => $d]]);
        }
    }

    private function seedPlans(): void
    {
        if (Plan::exists()) {
            return;
        }
        Plan::create(['name' => ['fr' => 'Starter', 'en' => 'Starter'], 'price' => 10000, 'features' => ['1 boutique', '10 produits']]);
        Plan::create(['name' => ['fr' => 'Pro', 'en' => 'Pro'], 'price' => 25000, 'features' => ['Produits illimités', 'Formations', 'Support prioritaire']]);
    }

    private function seedSimulator(): void
    {
        if (SimStep::exists()) {
            return;
        }
        $steps = [
            ['housing', 'Le bâtiment', 'Where will your birds live?', [
                ['Bâtiment ventilé, 8 sujets/m² (400 000 FCFA)', -400000, 0, 0, 0, false, null],
                ['Hangar de fortune surpeuplé (100 000 FCFA)', -100000, 0, 15, 0, true, 'Un bâtiment surpeuplé et mal ventilé augmente le stress, les maladies et la mortalité (+15 points). Mieux vaut investir dans un bâtiment adapté.'],
            ]],
            ['chicks', 'L’achat des poussins', 'How many chicks do you buy?', [
                ['200 poussins Goliath sélectionnés (240 000 FCFA)', -240000, 200, 0, 0, false, null],
                ['200 poussins non vaccinés pas chers (120 000 FCFA)', -120000, 200, 12, 0, true, 'Des poussins non vaccinés d’origine inconnue sont fragiles : la mortalité grimpe. L’économie initiale est vite perdue.'],
            ]],
            ['feed', 'L’alimentation', 'What feeding strategy?', [
                ['Aliment complet adapté à chaque phase (150 000 FCFA)', -150000, 0, 0, 10, false, null],
                ['Restes et maïs seul (30 000 FCFA)', -30000, 0, 10, -15, true, 'Une alimentation déséquilibrée ralentit la croissance : poids de vente plus faible et mortalité plus élevée.'],
            ]],
            ['health', 'La santé', 'How do you manage vaccination?', [
                ['Calendrier de vaccination suivi (40 000 FCFA)', -40000, 0, -3, 0, false, null],
                ['Aucune vaccination (0 FCFA)', 0, 0, 20, 0, true, 'Sans vaccination, une épidémie (Newcastle, Gumboro) peut décimer l’élevage. La prévention coûte bien moins cher.'],
            ]],
            ['sale', 'La vente', 'How do you sell?', [
                ['Vente directe aux clients et restaurants', 0, 0, 0, 15, false, null],
                ['Vente à un intermédiaire pressé (prix bradé)', 0, 0, 0, -20, true, 'Vendre en urgence à un intermédiaire réduit fortement votre marge. Préparez vos débouchés avant la fin du cycle.'],
            ]],
        ];
        foreach ($steps as $i => [$code, $title, $body, $choices]) {
            $s = SimStep::create(['code' => $code, 'position' => $i, 'title' => ['fr' => $title], 'body' => ['fr' => 'Choisissez avec soin : chaque décision a un impact.']]);
            foreach ($choices as [$label, $cash, $birds, $mort, $rev, $bad, $tip]) {
                SimChoice::create(['sim_step_id' => $s->id, 'label' => ['fr' => $label], 'cash' => $cash, 'birds' => $birds,
                    'mortality' => $mort, 'revenue_pct' => $rev, 'is_bad' => $bad, 'tip' => $tip ? ['fr' => $tip] : null]);
            }
        }
    }
}
