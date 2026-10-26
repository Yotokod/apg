<?php

namespace App\Filament\Resources\GalleryItems;

use App\Filament\Resources\GalleryItems\Pages\ManageGalleryItems;
use App\Models\GalleryItem;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GalleryItemResource extends Resource
{
    protected static ?string $model = GalleryItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $modelLabel = 'image';

    protected static ?string $pluralModelLabel = 'Galerie';

    protected static ?string $navigationLabel = 'Galerie';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            FileUpload::make('image')->label('Image')->image()->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])->maxSize(4096)->disk('public')->directory('gallery')->required()->columnSpanFull(),
            Section::make('Titre')->columns(2)->schema([TextInput::make('title.fr')->label('FR'), TextInput::make('title.en')->label('EN')])->columnSpanFull(),
            Section::make('Texte alternatif')->columns(2)->schema([TextInput::make('alt.fr')->label('FR'), TextInput::make('alt.en')->label('EN')])->columnSpanFull(),
            Select::make('category')->label('Catégorie')->options(GalleryItem::CATEGORIES)->required()->default('autre'),
            TextInput::make('position')->label('Position')->numeric()->default(0),
            Toggle::make('is_featured')->label('À la une'),
            Toggle::make('is_active')->label('Actif')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')->label('Image')->disk('public')->square(),
                TextColumn::make('title.fr')->label('Titre')->searchable()->limit(40),
                TextColumn::make('category')->label('Catégorie')->badge()->formatStateUsing(fn ($state) => GalleryItem::CATEGORIES[$state] ?? $state),
                ToggleColumn::make('is_featured')->label('À la une'),
                ToggleColumn::make('is_active')->label('Actif'),
            ])
            ->filters([SelectFilter::make('category')->label('Catégorie')->options(GalleryItem::CATEGORIES)])
            ->reorderable('position')
            ->defaultSort('position')
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageGalleryItems::route('/')];
    }
}
