<?php

namespace App\Filament\Resources\Posts;

use App\Filament\Resources\Posts\Pages\ManagePosts;
use App\Models\Post;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static ?string $modelLabel = 'article';

    protected static ?string $pluralModelLabel = 'Articles';

    protected static ?string $navigationLabel = 'Blog';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Titre')->columns(2)->schema([TextInput::make('title.fr')->label('FR')->required(), TextInput::make('title.en')->label('EN')])->columnSpanFull(),
            TextInput::make('slug')->label('Slug')->required()->unique(ignoreRecord: true),
            TextInput::make('category')->label('Catégorie'),
            Section::make('Extrait')->columns(2)->schema([Textarea::make('excerpt.fr')->label('FR'), Textarea::make('excerpt.en')->label('EN')])->columnSpanFull(),
            RichEditor::make('body.fr')->label('Contenu (FR)')->columnSpanFull(),
            RichEditor::make('body.en')->label('Contenu (EN)')->columnSpanFull(),
            FileUpload::make('cover')->label('Image de couverture')->image()->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])->maxSize(4096)->disk('public')->directory('posts'),
            DateTimePicker::make('published_at')->label('Date de publication')->default(now()),
            Toggle::make('is_active')->label('Publié')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover')->label('Image')->disk('public')->square(),
                TextColumn::make('title.fr')->label('Titre')->searchable()->limit(50),
                TextColumn::make('category')->label('Catégorie')->badge(),
                TextColumn::make('published_at')->label('Publié le')->date('d/m/Y')->sortable(),
                ToggleColumn::make('is_active')->label('Publié'),
            ])
            ->defaultSort('published_at', 'desc')
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManagePosts::route('/')];
    }
}
