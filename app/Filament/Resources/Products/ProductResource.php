<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\ManageProducts;
use App\Models\Product;
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

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
    Section::make('Nom / Name')->columns(2)->schema([TextInput::make('name.fr')->label('FR')->required(), TextInput::make('name.en')->label('EN')])->columnSpanFull(),
    Section::make('Description')->columns(2)->schema([Textarea::make('description.fr')->label('FR'), Textarea::make('description.en')->label('EN')])->columnSpanFull(),
    TextInput::make('slug')->required()->unique(ignoreRecord: true),
    Select::make('category')->options(['poulet' => 'Poulet', 'poussin' => 'Poussin', 'oeuf' => 'Œuf', 'caille' => 'Caille', 'pintade' => 'Pintade'])->required(),
    TextInput::make('price')->numeric()->required()->suffix('FCFA'),
    TextInput::make('stock')->numeric()->default(0),
    FileUpload::make('image')->image()->directory('products'),
    Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([TextColumn::make('name.fr')->label('Nom')->searchable(), TextColumn::make('category')->badge(), TextColumn::make('price')->numeric(), TextColumn::make('stock'), ToggleColumn::make('is_active')])
            ->defaultSort('id', 'desc')
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageProducts::route('/')];
    }
}
