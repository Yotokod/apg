<?php

namespace App\Filament\Resources\Plans;

use App\Filament\Resources\Plans\Pages\ManagePlans;
use App\Models\Plan;
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

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $navigationLabel = 'Plans d abonnement (mode marketplace)';
    protected static ?string $modelLabel = 'Plans d abonnement (mode marketplace)';
    protected static ?string $pluralModelLabel = 'Plans d abonnement (mode marketplace)';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
    Section::make('Nom / Name')->columns(2)->schema([TextInput::make('name.fr')->label('FR')->required(), TextInput::make('name.en')->label('EN')])->columnSpanFull(),
    TextInput::make('price')->numeric()->required(), Select::make('period')->options(['month' => 'Mois', 'year' => 'An']),
    TagsInput::make('features')->columnSpanFull(), Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([TextColumn::make('name.fr'), TextColumn::make('price')->numeric(), TextColumn::make('period'), ToggleColumn::make('is_active')])
            ->defaultSort('id', 'desc')
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManagePlans::route('/')];
    }
}
