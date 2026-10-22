<?php

namespace App\Filament\Resources\QuizNodes;

use App\Filament\Resources\QuizNodes\Pages\ManageQuizNodes;
use App\Models\QuizNode;
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

class QuizNodeResource extends Resource
{
    protected static ?string $model = QuizNode::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static ?string $navigationLabel = 'Quiz (étapes)';
    protected static ?string $modelLabel = 'Quiz (étapes)';
    protected static ?string $pluralModelLabel = 'Quiz (étapes)';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
    Select::make('lesson_id')->label('Leçon')->options(fn () => \App\Models\Lesson::all()->mapWithKeys(fn ($l) => [$l->id => $l->tr('title')]))->searchable()->required(),
    Section::make('Question')->columns(2)->schema([TextInput::make('question.fr')->label('FR')->required(), TextInput::make('question.en')->label('EN')])->columnSpanFull(),
    Section::make('Explication (affichée après la réponse)')->columns(2)->schema([Textarea::make('explanation.fr')->label('FR'), Textarea::make('explanation.en')->label('EN')])->columnSpanFull(),
    Toggle::make('is_start')->label('Question de départ'),
    Repeater::make('options')->relationship()->columnSpanFull()->schema([
      TextInput::make('label.fr')->label('Réponse FR')->required(), TextInput::make('label.en')->label('Answer EN'),
      Toggle::make('is_correct')->label('Bonne réponse'),
      Select::make('next_node_id')->label('Chemin : question suivante (vide = fin)')->options(fn () => \App\Models\QuizNode::all()->mapWithKeys(fn ($n) => [$n->id => '#'.$n->id.' '.\Illuminate\Support\Str::limit($n->tr('question'), 50)]))->searchable(),
    ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([TextColumn::make('id'), TextColumn::make('lesson_id')->label('Leçon')->formatStateUsing(fn ($state) => \App\Models\Lesson::find($state)?->tr('title')), TextColumn::make('question.fr')->limit(60), IconColumn::make('is_start')->boolean()])
            ->defaultSort('id', 'desc')
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageQuizNodes::route('/')];
    }
}
