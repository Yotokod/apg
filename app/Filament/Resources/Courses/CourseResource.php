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
            FileUpload::make('motion_file')->label('Animation Lottie (.json)')->disk('public')->directory('motion')->maxSize(2048)->acceptedFileTypes(['application/json']),
            TextInput::make('motion_url')->label('Motion design explicatif (optionnel, embed URL)')->url(),
            Repeater::make('scenes')->label(__('lesson.admin.scenes'))->helperText(__('lesson.admin.scenes_help'))
              ->collapsed()->reorderable()->defaultItems(0)->columns(2)->columnSpanFull()
              ->itemLabel(fn (array $state) => $state['title']['fr'] ?? __('lesson.admin.scene'))
              ->schema([
                Select::make('illustration')->label(__('lesson.admin.illustration'))
                  ->options(fn () => collect(\App\Livewire\LessonPlayer::SCENES)->keys()->mapWithKeys(fn ($k) => [$k => __('lesson.scene_keys.'.$k)])->all()),
                TextInput::make('icon')->label(__('lesson.admin.icon'))->regex('/^fa-[a-z0-9-]+$/')->maxLength(40),
                TextInput::make('title.fr')->label(__('lesson.admin.title_fr'))->required()->maxLength(120),
                TextInput::make('title.en')->label(__('lesson.admin.title_en'))->maxLength(120),
                Textarea::make('caption.fr')->label(__('lesson.admin.caption_fr'))->rows(3)->maxLength(600),
                Textarea::make('caption.en')->label(__('lesson.admin.caption_en'))->rows(3)->maxLength(600),
                TagsInput::make('points.fr')->label(__('lesson.admin.points_fr')),
                TagsInput::make('points.en')->label(__('lesson.admin.points_en')),
                TextInput::make('duration')->label(__('lesson.admin.duration'))->numeric()->minValue(3)->maxValue(30)->default(8),
              ]),
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
