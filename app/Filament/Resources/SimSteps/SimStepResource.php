<?php

namespace App\Filament\Resources\SimSteps;

use App\Filament\Resources\SimSteps\Pages\ManageSimSteps;
use App\Models\SimStep;
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

class SimStepResource extends Resource
{
    protected static ?string $model = SimStep::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'Simulateur (étapes)';
    protected static ?string $modelLabel = 'Simulateur (étapes)';
    protected static ?string $pluralModelLabel = 'Simulateur (étapes)';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
    TextInput::make('code')->required()->unique(ignoreRecord: true), TextInput::make('position')->numeric()->default(0),
    Section::make('Titre / Title')->columns(2)->schema([TextInput::make('title.fr')->label('FR')->required(), TextInput::make('title.en')->label('EN')])->columnSpanFull(),
    Section::make('Texte')->columns(2)->schema([Textarea::make('body.fr')->label('FR'), Textarea::make('body.en')->label('EN')])->columnSpanFull(),
    Repeater::make('choices')->relationship()->columnSpanFull()->schema([
      TextInput::make('label.fr')->label('Choix FR')->required(), TextInput::make('label.en')->label('Choice EN'),
      TextInput::make('cash')->numeric()->default(0)->label('Trésorerie (±FCFA)'), TextInput::make('birds')->numeric()->default(0)->label('Sujets'),
      TextInput::make('mortality')->numeric()->default(0)->label('Mortalité (pts)'), TextInput::make('revenue_pct')->numeric()->default(0)->label('Prix vente (pts %)'),
      Toggle::make('is_bad')->label('Mauvaise décision (popup)'),
      Textarea::make('tip.fr')->label('Explication/astuce FR'), Textarea::make('tip.en')->label('Tip EN'),
    ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([TextColumn::make('position')->sortable(), TextColumn::make('code'), TextColumn::make('title.fr')])
            ->defaultSort('id', 'desc')
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageSimSteps::route('/')];
    }
}
