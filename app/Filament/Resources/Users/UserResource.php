<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $navigationLabel = 'Utilisateurs';
    protected static ?string $modelLabel = 'Utilisateurs';
    protected static ?string $pluralModelLabel = 'Utilisateurs';

    public static function canViewAny(): bool
    {
        return auth()->user()?->role === 'superadmin';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
    TextInput::make('name')->required(), TextInput::make('email')->email()->required()->unique(ignoreRecord: true), TextInput::make('phone'),
    Select::make('role')->options(['customer' => 'Client / élève', 'admin' => 'Admin', 'superadmin' => 'Superadmin'])->required(),
    TextInput::make('password')->password()->revealable()->dehydrated(fn ($state) => filled($state))->required(fn (string $operation) => $operation === 'create'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([TextColumn::make('name')->searchable(), TextColumn::make('email')->searchable(), TextColumn::make('role')->badge(), TextColumn::make('created_at')->dateTime()])
            ->defaultSort('id', 'desc')
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageUsers::route('/')];
    }
}
