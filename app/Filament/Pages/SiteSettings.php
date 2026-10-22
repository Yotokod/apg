<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Cache;

/** Superadmin: identity, mode (one_market / marketplace), start page and AI keys. */
class SiteSettings extends Page
{
    protected string $view = 'filament.pages.site-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Paramètres';

    protected static ?string $title = 'Paramètres & identité';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'superadmin';
    }

    public function mount(): void
    {
        $keys = ['name', 'short_name', 'phone', 'whatsapp', 'email', 'cities', 'primary_color', 'accent_color', 'logo', 'app_mode', 'home_page', 'ai_api_key', 'ai_model'];
        $this->form->fill(collect($keys)->mapWithKeys(fn ($k) => [$k => site($k)])->all());
    }

    public function form(Schema $schema): Schema
    {
        return $schema->statePath('data')->components([
            Section::make('Identité')->columns(2)->schema([
                TextInput::make('name')->required(), TextInput::make('short_name'),
                TextInput::make('logo')->label('Logo (URL ou /images/logo.svg)'),
                TextInput::make('phone'), TextInput::make('whatsapp'), TextInput::make('email')->email(), TextInput::make('cities'),
                ColorPicker::make('primary_color'), ColorPicker::make('accent_color'),
            ]),
            Section::make('Mode & page de départ')->columns(2)->schema([
                Select::make('app_mode')->options(['one_market' => 'One market (licence à vie, 1 client)', 'marketplace' => 'Marketplace (plans d’abonnement)']),
                Select::make('home_page')->options(['home' => 'Landing page', 'shop' => 'Boutique', 'courses' => 'Formations']),
            ]),
            Section::make('Assistant IA')->columns(2)->schema([
                TextInput::make('ai_api_key')->password()->revealable()->label('Clé API (Anthropic)'),
                TextInput::make('ai_model')->placeholder('claude-haiku-5-5'),
            ]),
        ]);
    }

    public function save(): void
    {
        foreach ($this->form->getState() as $k => $v) {
            Setting::updateOrCreate(['key' => $k], ['value' => $v]);
        }
        Cache::forget('settings.all');
        Notification::make()->title('Enregistré')->success()->send();
    }

    protected function getHeaderActions(): array
    {
        return [Action::make('save')->label('Enregistrer')->action('save')];
    }
}
