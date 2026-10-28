<?php

namespace App\Filament\Resources\SimParams;

use App\Filament\Resources\SimParams\Pages\ManageSimParams;
use App\Models\SimParam;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/** Simulator parameters: prices, rates, densities… editable without code; each value carries its status. */
class SimParamResource extends Resource
{
    protected static ?string $model = SimParam::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static ?string $navigationLabel = 'Simulateur (paramètres)';

    protected static ?string $modelLabel = 'Paramètre du simulateur';

    protected static ?string $pluralModelLabel = 'Simulateur (paramètres)';

    private const STATUS_LABELS = ['apg' => 'APG (donnée réelle)', 'generique' => 'Générique (FAO/OMSA)', 'hypothese' => 'Hypothèse modifiable'];

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('key')->label('Clé')->required()->unique(ignoreRecord: true)->alphaDash()
                ->helperText('Utilisée par le moteur (resources/js/sim/engine.js) : ne pas renommer une clé existante.'),
            TextInput::make('value')->label('Valeur')->numeric()->minValue(0)->required(),
            TextInput::make('unit')->label('Unité (clé de traduction sim.units.*)')->maxLength(32),
            Select::make('status')->label('Statut')->options(self::STATUS_LABELS)->required(),
            Select::make('group')->label('Groupe')->options(array_combine(SimParam::GROUPS, SimParam::GROUPS))->required(),
            TextInput::make('position')->numeric()->default(0),
            Section::make('Libellé')->columns(2)->columnSpanFull()->schema([TextInput::make('label.fr')->label('FR')->required(), TextInput::make('label.en')->label('EN')]),
            Section::make('Note / source')->columns(2)->columnSpanFull()->schema([Textarea::make('note.fr')->label('FR'), Textarea::make('note.en')->label('EN')]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('group')->label('Groupe')->badge()->sortable(),
                TextColumn::make('label.fr')->label('Paramètre')->description(fn (SimParam $p) => $p->key)->searchable(['key', 'label']),
                TextInputColumn::make('value')->label('Valeur')->type('number')->rules(['required', 'numeric', 'min:0']),
                TextColumn::make('unit')->label('Unité'),
                TextColumn::make('status')->label('Statut')->badge()
                    ->color(fn (string $state) => match ($state) { 'apg' => 'warning', 'generique' => 'info', default => 'gray' }),
            ])
            ->filters([
                SelectFilter::make('status')->options(self::STATUS_LABELS),
                SelectFilter::make('group')->options(array_combine(SimParam::GROUPS, SimParam::GROUPS)),
            ])
            ->defaultSort('position')
            ->paginated([25, 50, 100])
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageSimParams::route('/')];
    }
}
