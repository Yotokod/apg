<?php

namespace App\Filament\Resources\Courses;

use App\Filament\Resources\Courses\Pages\ManageCourses;
use App\Models\Course;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
    Section::make('Titre / Title')->columns(2)->schema([TextInput::make('title.fr')->label('FR')->required(), TextInput::make('title.en')->label('EN')])->columnSpanFull(),
    Section::make('Description')->columns(2)->schema([Textarea::make('description.fr')->label('FR'), Textarea::make('description.en')->label('EN')])->columnSpanFull(),
    TextInput::make('slug')->required()->unique(ignoreRecord: true),
    TextInput::make('price')->numeric()->default(0)->suffix('FCFA'),
    Toggle::make('is_active')->default(true),
    Repeater::make('modules')->relationship()->orderColumn('position')->collapsed()->columnSpanFull()
      ->itemLabel(fn (array $state) => $state['title']['fr'] ?? 'Module')
      ->schema([
        TextInput::make('title.fr')->label('Module FR')->required(), TextInput::make('title.en')->label('Module EN'),
        Repeater::make('lessons')->relationship()->orderColumn('position')->collapsed()
          ->itemLabel(fn (array $state) => $state['title']['fr'] ?? 'Leçon')
          ->schema([
            TextInput::make('title.fr')->label('Leçon FR')->required(), TextInput::make('title.en')->label('Lesson EN'),
            Textarea::make('content.fr')->label('Contenu FR')->rows(5), Textarea::make('content.en')->label('Content EN')->rows(5),
            TextInput::make('video_url')->label('Vidéo (embed URL)')->url(),
            FileUpload::make('motion_file')->label('Animation Lottie (.json)')->disk('public')->directory('motion')->acceptedFileTypes(['application/json']),
            TextInput::make('motion_url')->label('Motion design explicatif (optionnel, embed URL)')->url(),
          ]),
      ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([TextColumn::make('title.fr')->label('Titre')->searchable(), TextColumn::make('price')->numeric(), ToggleColumn::make('is_active')])
            ->defaultSort('id', 'desc')
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageCourses::route('/')];
    }
}
