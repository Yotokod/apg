<?php

namespace App\Filament\Resources\PaymentGateways;

use App\Filament\Resources\PaymentGateways\Pages\ManagePaymentGateways;
use App\Models\PaymentGateway;
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

class PaymentGatewayResource extends Resource
{
    protected static ?string $model = PaymentGateway::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $navigationLabel = 'Moyens de paiement';
    protected static ?string $modelLabel = 'Moyens de paiement';
    protected static ?string $pluralModelLabel = 'Moyens de paiement';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
    TextInput::make('name')->required(), TextInput::make('code')->disabled()->dehydrated(),
    Toggle::make('enabled')->label('Activé'),
    KeyValue::make('config')->label('Clés API (public_key, secret_key, site_id, sandbox…)')->columnSpanFull(),
    Textarea::make('instructions.fr')->label('Instructions FR (transferts)')->rows(4), Textarea::make('instructions.en')->label('Instructions EN')->rows(4),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([TextColumn::make('name'), TextColumn::make('type')->badge(), ToggleColumn::make('enabled')])
            ->defaultSort('id', 'desc')
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManagePaymentGateways::route('/')];
    }
}
