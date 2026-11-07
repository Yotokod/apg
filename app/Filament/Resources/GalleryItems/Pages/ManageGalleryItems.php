<?php

namespace App\Filament\Resources\GalleryItems\Pages;

use App\Filament\Resources\GalleryItems\GalleryItemResource;
use App\Models\GalleryItem;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Grid;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;

class ManageGalleryItems extends ManageRecords
{
    protected static string $resource = GalleryItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->bulkUploadAction(),
            Action::make('featuredFirst')->label('Réordonner : mettre en avant en premier')->icon(Heroicon::OutlinedBarsArrowUp)->color('gray')
                ->requiresConfirmation()->modalDescription('Les photos « À la une » passent en tête, l’ordre relatif est conservé.')
                ->action(fn () => $this->putFeaturedFirst()),
            CreateAction::make()->label('Ajouter une photo'),
        ];
    }

    private function bulkUploadAction(): Action
    {
        return Action::make('bulkUpload')->label('Ajouter plusieurs photos')->icon(Heroicon::OutlinedPhoto)
            ->modalHeading('Ajouter plusieurs photos')->modalWidth('3xl')
            ->schema([
                FileUpload::make('images')->label('Photos (JPEG, PNG, WebP · 6 Mo max · 30 max)')->multiple()->image()
                    ->acceptedFileTypes(GalleryItemResource::ACCEPTED_TYPES)->maxSize(GalleryItemResource::MAX_KB)->maxFiles(GalleryItemResource::MAX_FILES)
                    ->reorderable()->appendFiles()->panelLayout('grid')
                    ->disk('public')->directory('gallery')->visibility('public')->required(),
                Grid::make(2)->schema([
                    ...GalleryItemResource::categoryFields(),
                    TextInput::make('title_fr')->label('Préfixe du titre (FR, optionnel)')->maxLength(120)->placeholder('ex. Ferme APG'),
                    TextInput::make('title_en')->label('Préfixe du titre (EN, optionnel)')->maxLength(120)->placeholder('e.g. APG farm'),
                    Toggle::make('is_featured')->label('À la une'),
                    Toggle::make('is_active')->label('Actif')->default(true),
                ]),
            ])
            ->action(function (array $data) {
                $count = $this->createItems($data);
                Notification::make()->title($count.' photo(s) ajoutée(s)')->body('Pensez à compléter le texte alternatif.')->success()->send();
            });
    }

    /** One GalleryItem per uploaded file, appended after the current last position (observer builds thumbnails). */
    private function createItems(array $data): int
    {
        $files = array_values((array) $data['images']);
        $start = (int) GalleryItem::max('position') + 1;
        $many = count($files) > 1;

        DB::transaction(function () use ($files, $data, $start, $many) {
            foreach ($files as $i => $path) {
                $title = array_filter([
                    'fr' => $this->numbered($data['title_fr'] ?? null, $i, $many),
                    'en' => $this->numbered($data['title_en'] ?? null, $i, $many),
                ]);
                GalleryItem::create([
                    'image' => $path,
                    'title' => $title ?: null,
                    'alt' => $title ?: null,
                    'category' => $data['category'],
                    'is_featured' => (bool) ($data['is_featured'] ?? false),
                    'is_active' => (bool) ($data['is_active'] ?? true),
                    'position' => $start + $i,
                ]);
            }
        });

        return count($files);
    }

    private function numbered(?string $prefix, int $i, bool $many): ?string
    {
        $prefix = trim((string) $prefix);

        return $prefix === '' ? null : ($many ? $prefix.' '.($i + 1) : $prefix);
    }

    private function putFeaturedFirst(): void
    {
        DB::transaction(function () {
            GalleryItem::orderByDesc('is_featured')->orderBy('position')->orderBy('id')->get(['id'])
                ->each(fn (GalleryItem $item, int $i) => GalleryItem::whereKey($item->id)->update(['position' => $i]));
        });
        Notification::make()->title('Ordre mis à jour')->success()->send();
    }
}
