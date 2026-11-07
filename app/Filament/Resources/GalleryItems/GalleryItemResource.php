<?php

namespace App\Filament\Resources\GalleryItems;

use App\Filament\Resources\GalleryItems\Pages\ManageGalleryItems;
use App\Models\GalleryItem;
use App\Support\SiteData;
use BackedEnum;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class GalleryItemResource extends Resource
{
    public const ACCEPTED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    public const MAX_KB = 6144;

    public const MAX_FILES = 30;

    public const PER_PAGE = 24;

    protected static ?string $model = GalleryItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $modelLabel = 'image';

    protected static ?string $pluralModelLabel = 'Galerie';

    protected static ?string $navigationLabel = 'Galerie';

    public static function canViewAny(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'superadmin'], true);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            FileUpload::make('image')->label('Image')->image()->acceptedFileTypes(self::ACCEPTED_TYPES)->maxSize(self::MAX_KB)
                ->disk('public')->directory('gallery')->visibility('public')->imageEditor()->required()->columnSpanFull(),
            Section::make('Titre')->columns(2)->schema([TextInput::make('title.fr')->label('FR')->maxLength(150), TextInput::make('title.en')->label('EN')->maxLength(150)])->columnSpanFull(),
            Section::make('Texte alternatif')->description('Décrit l’image pour les lecteurs d’écran et le référencement (ex. « Poussins Goliath de 3 jours sous l’éleveuse »).')
                ->columns(2)->schema([TextInput::make('alt.fr')->label('FR')->maxLength(200), TextInput::make('alt.en')->label('EN')->maxLength(200)])->columnSpanFull(),
            ...self::categoryFields(),
            TextInput::make('position')->label('Position')->numeric()->default(0),
            Toggle::make('is_featured')->label('À la une'),
            Toggle::make('is_active')->label('Actif')->default(true),
        ]);
    }

    /** Category select (known + used categories) and a free field that creates a new one. */
    public static function categoryFields(): array
    {
        return [
            Select::make('category')->label('Catégorie')->required()->default('autre')->searchable()
                ->options(fn (?string $state) => GalleryItem::categoryOptions('fr') + ($state ? [$state => GalleryItem::categoryLabel($state, 'fr')] : [])),
            TextInput::make('new_category')->label('… ou nouvelle catégorie')->placeholder('ex. Basse-cour')->maxLength(40)
                ->dehydrated(false)->live(onBlur: true)
                ->afterStateUpdated(fn (?string $state, Set $set) => filled($state) ? $set('category', Str::slug($state)) : null),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Thumbnail URL built from the current host (WebP when available), independent of APP_URL.
                ImageColumn::make('image')->label('Image')->state(fn (GalleryItem $r) => SiteData::imageUrl($r->image))->square()->imageSize(64),
                TextColumn::make('title.fr')->label('Titre')->searchable()->limit(40)->placeholder('Sans titre')
                    ->description(fn (GalleryItem $r) => $r->title['en'] ?? null),
                IconColumn::make('has_alt')->label('Alt')->alignCenter()
                    ->state(fn (GalleryItem $r) => filled($r->alt['fr'] ?? null))
                    ->icon(fn (bool $state) => $state ? Heroicon::OutlinedCheckCircle : Heroicon::OutlinedExclamationTriangle)
                    ->color(fn (bool $state) => $state ? 'success' : 'warning')
                    ->tooltip(fn (bool $state) => $state ? 'Texte alternatif renseigné' : 'Texte alternatif manquant : ajoutez-le pour l’accessibilité'),
                SelectColumn::make('category')->label('Catégorie')->options(fn () => GalleryItem::categoryOptions('fr'))->selectablePlaceholder(false),
                ToggleColumn::make('is_featured')->label('À la une'),
                ToggleColumn::make('is_active')->label('Actif'),
                TextColumn::make('position')->label('Pos.')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')->label('Catégorie')->options(fn () => GalleryItem::categoryOptions('fr')),
                TernaryFilter::make('is_featured')->label('À la une'),
                TernaryFilter::make('is_active')->label('Actif'),
                Filter::make('missing_alt')->label('Sans texte alternatif')->toggle()
                    ->query(fn (Builder $query) => $query->where(fn (Builder $w) => $w->whereNull('alt')->orWhereNull('alt->fr')->orWhere('alt->fr', ''))),
            ])
            ->reorderable('position')
            ->defaultSort('position')
            ->paginated([12, self::PER_PAGE, 48, 96])
            ->defaultPaginationPageOption(self::PER_PAGE)
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([
                self::bulkUpdate('activate', 'Activer', Heroicon::OutlinedEye, ['is_active' => true]),
                self::bulkUpdate('deactivate', 'Désactiver', Heroicon::OutlinedEyeSlash, ['is_active' => false]),
                self::bulkUpdate('feature', 'Mettre en avant', Heroicon::OutlinedStar, ['is_featured' => true]),
                self::bulkUpdate('unfeature', 'Retirer de la une', Heroicon::OutlinedMinusCircle, ['is_featured' => false]),
                BulkAction::make('changeCategory')->label('Changer de catégorie')->icon(Heroicon::OutlinedTag)
                    ->schema(self::categoryFields())
                    ->action(fn (Collection $records, array $data) => $records->each->update(['category' => $data['category']]))
                    ->deselectRecordsAfterCompletion(),
                // Deletes one by one so the observer removes each uploaded file + thumbnail.
                BulkAction::make('delete')->label('Supprimer')->icon(Heroicon::OutlinedTrash)->color('danger')
                    ->requiresConfirmation()->modalDescription('Les fichiers envoyés dans la galerie seront aussi supprimés.')
                    ->action(fn (Collection $records) => $records->each->delete())
                    ->deselectRecordsAfterCompletion(),
            ])]);
    }

    private static function bulkUpdate(string $name, string $label, Heroicon $icon, array $values): BulkAction
    {
        return BulkAction::make($name)->label($label)->icon($icon)
            ->action(fn (Collection $records) => $records->each->update($values))
            ->deselectRecordsAfterCompletion();
    }

    public static function getPages(): array
    {
        return ['index' => ManageGalleryItems::route('/')];
    }
}
