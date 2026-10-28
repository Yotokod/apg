<?php

namespace App\Filament\Resources\SimSteps;

use App\Filament\Resources\SimSteps\Pages\ManageSimSteps;
use App\Models\SimStep;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/** Simulator story graph: nodes (scene + narration) and their choices (edges with effects). */
class SimStepResource extends Resource
{
    protected static ?string $model = SimStep::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'Simulateur (histoire)';

    protected static ?string $modelLabel = 'Scène du simulateur';

    protected static ?string $pluralModelLabel = 'Simulateur (histoire)';

    private const NEXT_HELP = 'Code de la scène suivante, ou par objectif : chair=growth;oeufs=laying;repro=laying';

    private const EFFECTS_HELP = 'Clés : set.champ, add.champ, mul.champ, flag. Valeur « @cle_parametre » = valeur d’un paramètre. Champs : mortalityExtra, extraPerBird, extraFixed, weightFactor, layingFactor, hatchExtra, priceFactor, feedPriceFactor, eggLossExtra, ration, density, buildMode, saleDay, weightKg, reformMonths, goal.';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')->required()->unique(ignoreRecord: true)->alphaDash(),
            TextInput::make('position')->numeric()->default(0)->helperText('La scène en position la plus basse est le départ.'),
            Select::make('chapter')->label('Chapitre')->options(array_combine(range(1, 8), range(1, 8)))->required(),
            Select::make('scene')->label('Scène animée')->options(array_combine(SimStep::SCENES, SimStep::SCENES))->required(),
            Select::make('kind')->label('Type (contrôles interactifs)')->options(array_combine(SimStep::KINDS, SimStep::KINDS))->required(),
            TextInput::make('icon')->label('Icône Font Awesome')->placeholder('fa-egg'),
            TextInput::make('next')->label('Suite par défaut')->helperText(self::NEXT_HELP)->columnSpanFull(),
            Section::make('Titre')->columns(2)->columnSpanFull()->schema([TextInput::make('title.fr')->label('FR')->required(), TextInput::make('title.en')->label('EN')]),
            Section::make('Narration')->columns(2)->columnSpanFull()->schema([Textarea::make('body.fr')->label('FR'), Textarea::make('body.en')->label('EN')]),
            Repeater::make('choices')->relationship()->orderColumn('position')->columnSpanFull()->columns(2)
                ->itemLabel(fn (array $state) => $state['label']['fr'] ?? null)->collapsed()
                ->schema([
                    TextInput::make('code')->required()->alphaDash(),
                    TextInput::make('icon')->placeholder('fa-check'),
                    TextInput::make('label.fr')->label('Choix FR')->required(), TextInput::make('label.en')->label('Choice EN'),
                    TextInput::make('hint.fr')->label('Conséquence FR'), TextInput::make('hint.en')->label('Hint EN'),
                    TextInput::make('next')->label('Scène suivante (sinon suite par défaut)')->helperText(self::NEXT_HELP),
                    CheckboxList::make('goals')->label('Visible pour (vide = tous)')->options(['chair' => 'Chair', 'oeufs' => 'Œufs', 'repro' => 'Reproduction'])->columns(3),
                    KeyValue::make('effects')->label('Effets')->helperText(self::EFFECTS_HELP)->columnSpanFull(),
                    Toggle::make('is_bad')->label('Mauvaise décision (pop-up)'),
                    Textarea::make('tip.fr')->label('Explication / astuce FR'), Textarea::make('tip.en')->label('Tip EN'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('position')->sortable(),
                TextColumn::make('chapter')->label('Ch.')->sortable(),
                TextColumn::make('code')->searchable(),
                TextColumn::make('title.fr')->label('Titre'),
                TextColumn::make('scene')->badge(),
                TextColumn::make('next')->label('Suite'),
            ])
            ->defaultSort('position')
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageSimSteps::route('/')];
    }
}
