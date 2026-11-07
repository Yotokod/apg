<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Support\Identity;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Superadmin back-office: identity, contact, socials, content, legal pages, SEO, certificate, system. */
class SiteSettings extends Page
{
    protected string $view = 'filament.pages.site-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Identité & contenu';

    protected static ?string $title = 'Identité & contenu';

    private const IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    private const LEGAL_PAGES = ['cgv' => ['Conditions générales de vente', 'cgv'], 'privacy' => ['Politique de confidentialité', 'privacy'], 'mentions' => ['Mentions légales', 'legal']];

    /** Placeholder defaults that live in translation files (key => translation key). */
    private const TRANSLATED_DEFAULTS = [
        'announcement.text' => 'site.ann1', 'footer.pitch' => 'home.lead', 'about.mission' => 'pages.about.mission_text',
        'about.story' => 'pages.about.story_1', 'seo.description' => 'site.meta_desc', 'maintenance.message' => 'identity.maintenance.message',
    ];

    private const PLACEHOLDERS = [
        'hours.fr' => 'Lun - Sam : 8h - 18h', 'hours.en' => 'Mon - Sat: 8am - 6pm', 'maps_url' => 'https://maps.app.goo.gl/…',
        'socials.twitter' => 'https://x.com/…', 'socials.instagram' => 'https://www.instagram.com/…', 'socials.linkedin' => 'https://www.linkedin.com/company/…',
        'announcement.link' => '/boutique ou https://…', 'seo.ga_id' => 'G-XXXXXXXXXX', 'seo.pixel_id' => '123456789012345',
        'org.legal_name' => 'Africa Poulets Goliath SARL', 'org.director_name' => 'Prénom Nom', 'org.director_title.fr' => 'Directeur général', 'org.director_title.en' => 'Managing Director',
        'cert.title.fr' => 'Certificat de réussite', 'cert.title.en' => 'Certificate of completion',
        'cert.text.fr' => 'Nous certifions que :name a suivi avec succès la formation « :course » (:hours h), le :date.',
        'cert.text.en' => 'This certifies that :name has successfully completed the course “:course” (:hours h) on :date.',
        'cert.footer.fr' => 'Vérifiez l’authenticité de ce certificat sur notre site.', 'cert.footer.en' => 'Verify this certificate on our website.',
        'cert.number_prefix' => 'APG', 'cert.min_progress' => '100', 'ai_model' => 'claude-haiku-5-5',
    ];

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'superadmin';
    }

    public function mount(): void
    {
        $this->fillForm();
    }

    public function form(Schema $schema): Schema
    {
        return $schema->statePath('data')->components([
            Tabs::make('settings')->persistTabInQueryString('onglet')->tabs([
                $this->identityTab(), $this->contactTab(), $this->socialsTab(), $this->contentTab(),
                $this->legalTab(), $this->seoTab(), $this->certificateTab(), $this->systemTab(),
            ]),
        ]);
    }

    public function save(): void
    {
        abort_unless(static::canAccess(), 403);
        $state = $this->form->getState();
        $apiKey = trim((string) Arr::pull($state, 'ai_api_key'));
        $values = array_merge(Arr::only(Arr::dot($state), Identity::keys()), $this->toggleValues($state));
        $this->keepLegacyFiles($values);

        try {
            Identity::store($values);
        } catch (ValidationException $e) {
            throw ValidationException::withMessages(collect($e->errors())->mapWithKeys(fn ($m, $k) => ["data.$k" => $m])->all());
        }
        if ($apiKey !== '') {
            Setting::updateOrCreate(['key' => 'ai_api_key'], ['value' => Crypt::encryptString($apiKey), 'group' => 'system']);
            site_flush();
        }

        $this->fillForm();
        Notification::make()->title('Paramètres enregistrés')->body('Le site public utilise désormais ces valeurs.')->success()->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('preview_home')->label('Accueil')->icon(Heroicon::OutlinedHome)->url(fn () => route('home'))->openUrlInNewTab(),
                Action::make('preview_about')->label('À propos')->icon(Heroicon::OutlinedInformationCircle)->url(fn () => route('about'))->openUrlInNewTab(),
                Action::make('preview_contact')->label('Contact')->icon(Heroicon::OutlinedEnvelope)->url(fn () => route('contact'))->openUrlInNewTab(),
            ])->label('Aperçu')->icon(Heroicon::OutlinedEye)->button()->color('gray'),
            ActionGroup::make([
                Action::make('export')->label('Exporter (JSON, sans secrets)')->icon(Heroicon::OutlinedArrowDownTray)->action(fn () => $this->exportConfig()),
                Action::make('import')->label('Importer un JSON')->icon(Heroicon::OutlinedArrowUpTray)
                    ->modalDescription('Seules les clés connues sont acceptées ; les secrets (clé IA) ne sont jamais importés. Une valeur vide rétablit la valeur par défaut.')
                    ->schema([
                        FileUpload::make('file')->label('Fichier de configuration')->acceptedFileTypes(['application/json', 'text/plain'])
                            ->maxSize(512)->storeFiles(false)->required(),
                    ])
                    ->modalSubmitActionLabel('Importer')
                    ->action(fn (array $data) => $this->importConfig($data['file'] ?? null)),
            ])->label('Exporter / importer la configuration')->icon(Heroicon::OutlinedArrowsRightLeft)->button()->color('gray'),
            Action::make('save')->label('Enregistrer')->icon(Heroicon::OutlinedCheck)->action('save')->keyBindings(['mod+s']),
        ];
    }

    private function fillForm(): void
    {
        $state = Identity::stored();
        foreach (SITE_FILE_KEYS as $key) {
            if (! str_starts_with((string) $state[$key], 'identity/')) {
                $state[$key] = null;
            }
        }
        foreach (array_keys(Identity::BOOL_DEFAULTS) as $key) {
            $state[$key] = Identity::enabled($key);
        }
        $state['ai_api_key'] = '';
        $this->form->fill(Arr::undot($state));
    }

    /** @return array<string, bool> */
    private function toggleValues(array $state): array
    {
        return collect(array_keys(Identity::BOOL_DEFAULTS))->mapWithKeys(fn ($k) => [$k => (bool) data_get($state, $k)])->all();
    }

    /** Files set before uploads existed (e.g. /images/logo-main.png) are kept when no new file is uploaded. */
    private function keepLegacyFiles(array &$values): void
    {
        $stored = Identity::stored();
        foreach (SITE_FILE_KEYS as $key) {
            if (blank($values[$key] ?? null) && filled($stored[$key]) && ! str_starts_with($stored[$key], 'identity/')) {
                unset($values[$key]);
            }
        }
    }

    private function exportConfig(): StreamedResponse
    {
        abort_unless(static::canAccess(), 403);
        $json = json_encode(Identity::export(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return response()->streamDownload(fn () => print ($json), 'identite-'.now()->format('Ymd-His').'.json', ['Content-Type' => 'application/json']);
    }

    private function importConfig(mixed $file): void
    {
        abort_unless(static::canAccess(), 403);
        $file = Arr::first(Arr::wrap($file));
        $payload = $file ? json_decode((string) $file->get(), true) : null;

        try {
            $count = is_array($payload) ? Identity::import($payload) : throw ValidationException::withMessages(['settings' => __('identity.admin.invalid_json')]);
        } catch (ValidationException $e) {
            Notification::make()->title('Import refusé')->body(collect($e->errors())->flatten()->first())->danger()->send();

            return;
        }
        $this->fillForm();
        Notification::make()->title('Configuration importée')->body("$count valeur(s) appliquée(s).")->success()->send();
    }

    private function identityTab(): Tab
    {
        return Tab::make('Identité')->icon(Heroicon::OutlinedIdentification)->schema([
            Section::make('Nom et slogan')->description('Affichés dans l’en-tête, le pied de page, les e-mails et les métadonnées.')->columns(2)->schema([
                $this->text('name', 'Nom de la plateforme', 'Nom complet de la société ou de la marque. Vide = nom par défaut.'),
                $this->text('short_name', 'Nom court', 'Sigle utilisé quand la place manque (ex. application mobile).'),
                ...$this->pair('tagline', 'Slogan', 'Phrase d’accroche courte (160 caractères max.).'),
            ]),
            Section::make('Logo, favicon et couleurs')->columns(2)->schema([
                $this->image('logo', 'Logo', 'PNG transparent recommandé, hauteur ≥ 80 px. Vide = logo intégré.'),
                $this->image('favicon', 'Favicon', 'Carré 512 × 512 px (PNG). Vide = favicon intégré.'),
                ColorPicker::make('primary_color')->label('Couleur principale')->hex()->placeholder($this->defaultFor('primary_color'))
                    ->helperText('Boutons et liens. Les nuances claires/foncées sont calculées automatiquement. Vide = orange APG.')->rules(Identity::rules()['primary_color']),
                ColorPicker::make('accent_color')->label('Couleur d’accent (encre)')->hex()->placeholder($this->defaultFor('accent_color'))
                    ->helperText('Bandeau d’annonce, pied de page et textes forts. Vide = bleu encre APG.')->rules(Identity::rules()['accent_color']),
            ]),
        ]);
    }

    private function contactTab(): Tab
    {
        return Tab::make('Contact')->icon(Heroicon::OutlinedPhone)->schema([
            Section::make('Coordonnées')->columns(2)->schema([
                $this->text('phone', 'Téléphone', 'Format international lisible, ex. +229 67 16 00 53.')->tel(),
                $this->text('whatsapp', 'Numéro WhatsApp', 'Chiffres uniquement avec indicatif pays, sans + ni espaces.'),
                $this->text('email', 'E-mail de contact', 'Adresse affichée publiquement et utilisée pour les réponses.')->email(),
                $this->text('address', 'Adresse', 'Adresse postale ou siège affiché sur les pages contact et légales.'),
                $this->text('cities', 'Villes desservies', 'Séparées par des virgules : chaque ville devient une étiquette.'),
                $this->text('maps_url', 'Lien Google Maps', 'Lien de partage Google Maps de votre site (optionnel).')->url(),
                ...$this->pair('hours', 'Horaires d’ouverture', 'Affichés sur la page contact et dans le pied de page.', 'textarea'),
            ]),
        ]);
    }

    private function socialsTab(): Tab
    {
        $networks = ['youtube' => 'YouTube', 'tiktok' => 'TikTok', 'facebook' => 'Facebook', 'instagram' => 'Instagram', 'linkedin' => 'LinkedIn', 'twitter' => 'X (Twitter)'];

        return Tab::make('Réseaux sociaux')->icon(Heroicon::OutlinedShare)->schema([
            Section::make('Profils publics')->description('Laissez vide pour masquer l’icône du réseau dans le pied de page.')->columns(2)->schema(
                collect($networks)->map(fn ($label, $n) => $this->text("socials.$n", $label, "URL complète de votre page $label (https://…).")->url())->values()->all()
            ),
        ]);
    }

    private function contentTab(): Tab
    {
        return Tab::make('Contenu')->icon(Heroicon::OutlinedDocumentText)->schema([
            Section::make('Bandeau d’annonce')->description('Barre fine au-dessus du menu, sur toutes les pages publiques.')->columns(2)->schema([
                Toggle::make('announcement.enabled')->label('Afficher le bandeau')->helperText('Désactivé = bandeau masqué partout.')->columnSpanFull(),
                ...$this->pair('announcement.text', 'Texte de l’annonce', 'Vide = message par défaut (livraison + WhatsApp en alternance).'),
                $this->text('announcement.link', 'Lien de l’annonce (optionnel)', 'Chemin interne (/boutique) ou URL https://. Rend le texte cliquable.'),
            ]),
            Section::make('Page À propos')->columns(2)->schema([
                ...$this->pair('about.story', 'Notre histoire', 'Remplace les 3 paragraphes par défaut. Vide = texte intégré.', 'rich'),
                ...$this->pair('about.mission', 'Notre mission', 'Une ou deux phrases. Vide = texte intégré.', 'textarea'),
            ]),
            Section::make('Pied de page')->columns(2)->schema([
                ...$this->pair('footer.pitch', 'Présentation courte', 'Texte sous le logo du pied de page.', 'textarea'),
            ]),
        ]);
    }

    private function legalTab(): Tab
    {
        return Tab::make('Pages légales')->icon(Heroicon::OutlinedScale)->schema(
            collect(self::LEGAL_PAGES)->map(fn ($page, $type) => Section::make($page[0])
                ->description('Vide = texte type intégré (avec la mention « brouillon à valider par un juriste »). La version FR sert de secours à l’EN.')
                ->collapsible()->columns(1)
                ->afterHeader([
                    Action::make("preview_$type")->label('Aperçu')->icon(Heroicon::OutlinedEye)->color('gray')->link()->url(fn () => route($page[1]))->openUrlInNewTab(),
                    Action::make("restore_$type")->label('Restaurer le texte par défaut')->icon(Heroicon::OutlinedArrowUturnLeft)->color('warning')->link()
                        ->requiresConfirmation()->modalDescription('Les versions FR et EN seront vidées. Enregistrez ensuite pour rétablir le texte intégré.')
                        ->action(function (Set $set) use ($type) {
                            $set("legal.$type.fr", null);
                            $set("legal.$type.en", null);
                        }),
                ])
                ->schema($this->pair("legal.$type", $page[0], 'Placeholders disponibles : :name :address :phone :email :cities.', 'rich'))
            )->values()->all()
        );
    }

    private function seoTab(): Tab
    {
        return Tab::make('SEO')->icon(Heroicon::OutlinedMagnifyingGlass)->schema([
            Section::make('Référencement')->columns(2)->schema([
                ...$this->pair('seo.description', 'Méta-description', '150 à 160 caractères idéalement. Utilisée quand une page n’a pas la sienne.', 'textarea'),
                $this->image('seo.og_image', 'Image de partage (Open Graph)', '1200 × 630 px, affichée lors des partages sur les réseaux. Vide = logo.'),
            ]),
            Section::make('Mesure d’audience (optionnel)')->columns(2)->schema([
                $this->text('seo.ga_id', 'Identifiant Google Analytics', 'Format G-XXXXXXXXXX. Vide = aucun script chargé.'),
                $this->text('seo.pixel_id', 'Identifiant Meta Pixel', 'Chiffres uniquement. Vide = aucun script chargé.'),
            ]),
        ]);
    }

    private function certificateTab(): Tab
    {
        return Tab::make('Certificat')->icon(Heroicon::OutlinedAcademicCap)->schema([
            Section::make('Organisme')->columns(2)->schema([
                $this->text('org.legal_name', 'Raison sociale', 'Nom légal imprimé sur les certificats.'),
                $this->text('org.director_name', 'Nom du signataire', 'Personne qui signe les certificats.'),
                ...$this->pair('org.director_title', 'Fonction du signataire', 'Ex. Directeur général.'),
                $this->image('org.signature', 'Signature', 'PNG transparent recommandé (fond détouré), environ 600 × 200 px.'),
                $this->image('org.stamp', 'Cachet de la société', 'PNG transparent recommandé, carré environ 400 × 400 px.'),
            ]),
            Section::make('Modèle de certificat')->columns(2)->schema([
                Toggle::make('cert.enabled')->label('Délivrer des certificats')->helperText('Désactivé = aucun certificat généré pour les apprenants.')->columnSpanFull(),
                ...$this->pair('cert.title', 'Titre', 'Grand titre en haut du certificat.'),
                ...$this->pair('cert.text', 'Texte principal', 'Placeholders : :name (apprenant), :course (formation), :date, :hours (durée).', 'textarea'),
                ...$this->pair('cert.footer', 'Pied du certificat', 'Mention légale ou de vérification en bas de page.', 'textarea'),
                $this->text('cert.number_prefix', 'Préfixe du numéro', 'Lettres, chiffres ou tirets (12 max.). Ex. APG-2026-0001.'),
                $this->text('cert.min_progress', 'Progression minimale (%)', 'Pourcentage de la formation à terminer pour obtenir le certificat.')->numeric()->minValue(1)->maxValue(100),
            ]),
        ]);
    }

    private function systemTab(): Tab
    {
        return Tab::make('Système')->icon(Heroicon::OutlinedCog6Tooth)->schema([
            Section::make('Mode et page de départ')->columns(2)->schema([
                Select::make('app_mode')->label('Mode de la plateforme')->placeholder('One market (par défaut)')->helperText('One market : une seule boutique. Marketplace : plans d’abonnement.')
                    ->options(['one_market' => 'One market (licence à vie, 1 client)', 'marketplace' => 'Marketplace (plans d’abonnement)']),
                Select::make('home_page')->label('Page de départ')->placeholder('Landing page (par défaut)')->helperText('Page ouverte quand un visiteur arrive sur la racine du site.')
                    ->options(['landing' => 'Landing page', 'shop' => 'Boutique', 'courses' => 'Formations']),
                $this->text('currency', 'Devise', 'Symbole affiché après les prix.'),
            ]),
            Section::make('Assistant IA')->columns(2)->schema([
                TextInput::make('ai_api_key')->label('Clé API (Anthropic)')->password()->revealable()->autocomplete('new-password')
                    ->placeholder(filled(site_overrides()['ai_api_key'] ?? null) ? '•••••••• (clé enregistrée)' : 'sk-ant-…')
                    ->helperText('Stockée chiffrée et jamais réaffichée. Laisser vide pour conserver la clé actuelle.')->maxLength(300),
                $this->text('ai_model', 'Modèle', 'Identifiant du modèle utilisé par le chat automatique.'),
            ]),
            Section::make('Mode maintenance')->description('Les visiteurs voient une page 503 ; les administrateurs connectés naviguent normalement.')->columns(2)->schema([
                Toggle::make('maintenance.enabled')->label('Activer la maintenance')->helperText('/admin et /connexion restent accessibles pour pouvoir le désactiver.')->columnSpanFull(),
                ...$this->pair('maintenance.message', 'Message affiché', 'Vide = message par défaut.', 'textarea'),
            ]),
        ]);
    }

    private function text(string $key, string $label, string $hint): TextInput
    {
        return TextInput::make($key)->label($label)->helperText($hint)->placeholder($this->defaultFor($key))->rules(Identity::rules()[$key]);
    }

    private function image(string $key, string $label, string $hint): FileUpload
    {
        return FileUpload::make($key)->label($label)->helperText($hint.' JPEG, PNG ou WebP, 2 Mo max.')
            ->image()->acceptedFileTypes(self::IMAGE_TYPES)->maxSize(2048)
            ->disk('public')->directory('identity')->visibility('public')->imagePreviewHeight('120');
    }

    /** FR + EN variants of one key. @return list<\Filament\Forms\Components\Field> */
    private function pair(string $key, string $label, string $hint, string $type = 'text'): array
    {
        return collect(Identity::LOCALES)->map(function ($locale) use ($key, $label, $hint, $type) {
            $name = "$key.$locale";
            $field = match ($type) {
                'rich' => RichEditor::make($name)->fileAttachments(false)
                    ->toolbarButtons([['bold', 'italic', 'link'], ['h2', 'h3'], ['bulletList', 'orderedList', 'blockquote'], ['undo', 'redo']]),
                'textarea' => Textarea::make($name)->rows(3)->rules(Identity::rules()[$name]),
                default => TextInput::make($name)->rules(Identity::rules()[$name]),
            };

            return $field->label($label.' ('.strtoupper($locale).')')->helperText($hint)
                ->placeholder($this->defaultFor($name) ?? ($type === 'rich' ? 'Vide = texte intégré par défaut' : null))
                ->columnSpan($type === 'rich' ? 'full' : 1);
        })->all();
    }

    private function defaultFor(string $key): ?string
    {
        if (isset(self::PLACEHOLDERS[$key])) {
            return self::PLACEHOLDERS[$key];
        }
        [$base, $locale] = preg_match('/^(.+)\.(fr|en)$/', $key, $m) ? [$m[1], $m[2]] : [$key, 'fr'];
        if (isset(self::TRANSLATED_DEFAULTS[$base])) {
            $text = __(self::TRANSLATED_DEFAULTS[$base], ['name' => config('site_identity.name'), 'phone' => config('site_identity.phone')], $locale);

            return Str::limit(strip_tags($text), 110);
        }
        $value = config("site_identity.$base");
        $value = is_array($value) ? ($value[$locale] ?? null) : $value;

        return is_scalar($value) && $value !== '' ? (string) $value : null;
    }
}
