<?php

namespace App\Filament\Resources\Enrollments;

use App\Filament\Resources\Enrollments\Pages\ManageEnrollments;
use App\Models\Enrollment;
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

class EnrollmentResource extends Resource
{
    protected static ?string $model = Enrollment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserPlus;

    protected static ?string $navigationLabel = 'Inscriptions aux formations';
    protected static ?string $modelLabel = 'Inscriptions aux formations';
    protected static ?string $pluralModelLabel = 'Inscriptions aux formations';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
    Select::make('user_id')->label('Élève')->relationship('user', 'name')->searchable()->preload()->required(),
    Select::make('course_id')->label('Formation')->options(fn () => \App\Models\Course::all()->mapWithKeys(fn ($c) => [$c->id => $c->tr('title')]))->searchable()->required(),
    Toggle::make('paid')->label('Payée / accès accordé')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([TextColumn::make('user.name')->label('Élève')->searchable(), TextColumn::make('course_id')->label('Formation')->formatStateUsing(fn ($state) => \App\Models\Course::find($state)?->tr('title')), ToggleColumn::make('paid')])
            ->defaultSort('id', 'desc')
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageEnrollments::route('/')];
    }
}
