<?php

namespace App\Filament\Resources\Certificates;

use App\Filament\Resources\Certificates\Pages\ManageCertificates;
use App\Models\Certificate;
use App\Models\Course;
use App\Services\CertificateService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Issued course certificates: verification links, revoke/restore, re-issue with a corrected name. */
class CertificateResource extends Resource
{
    protected static ?string $model = Certificate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?string $navigationLabel = 'Certificats';

    protected static ?string $modelLabel = 'certificat';

    protected static ?string $pluralModelLabel = 'Certificats';

    public static function canViewAny(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'superadmin'], true);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('user:id,name,email'))
            ->columns([
                TextColumn::make('number')->label('Numéro')->searchable()->sortable()->copyable()->weight('bold'),
                TextColumn::make('student_name')->label('Élève')->searchable()->description(fn (Certificate $r) => $r->user?->email),
                TextColumn::make('course_title')->label('Formation')->limit(40)
                    ->formatStateUsing(fn (Certificate $r) => $r->tr('course_title'))
                    ->searchable(query: fn (Builder $query, string $search) => $query->where('course_title', 'like', '%'.$search.'%')),
                TextColumn::make('issued_at')->label('Délivré le')->date('d/m/Y')->sortable(),
                TextColumn::make('status')->label('Statut')->badge()
                    ->state(fn (Certificate $r) => $r->isRevoked() ? 'revoked' : 'valid')
                    ->formatStateUsing(fn (string $state) => $state === 'revoked' ? 'Révoqué' : 'Valide')
                    ->color(fn (string $state) => $state === 'revoked' ? 'danger' : 'success'),
                TextColumn::make('code')->label('Code')->toggleable(isToggledHiddenByDefault: true)->copyable(),
            ])
            ->filters([
                SelectFilter::make('course_id')->label('Formation')
                    ->options(fn () => Course::orderBy('id')->get()->mapWithKeys(fn (Course $c) => [$c->id => $c->tr('title')])->all()),
                TernaryFilter::make('revoked_at')->label('Révoqué')->nullable()->trueLabel('Révoqués')->falseLabel('Valides'),
            ])
            ->defaultSort('issued_at', 'desc')
            ->recordActions([
                Action::make('open')->label('Voir')->icon(Heroicon::OutlinedEye)
                    ->url(fn (Certificate $r) => $r->url())->openUrlInNewTab(),
                Action::make('revoke')->label('Révoquer')->icon(Heroicon::OutlinedNoSymbol)->color('danger')
                    ->visible(fn (Certificate $r) => ! $r->isRevoked())
                    ->requiresConfirmation()->modalDescription('La page publique affichera « Révoqué », sans signature ni cachet.')
                    ->action(fn (Certificate $r) => $r->update(['revoked_at' => now()])),
                Action::make('restore')->label('Rétablir')->icon(Heroicon::OutlinedArrowUturnLeft)->color('success')
                    ->visible(fn (Certificate $r) => $r->isRevoked())
                    ->requiresConfirmation()
                    ->action(fn (Certificate $r) => $r->update(['revoked_at' => null])),
                Action::make('reissue')->label('Ré-émettre')->icon(Heroicon::OutlinedPencilSquare)
                    ->modalHeading('Ré-émettre avec nom corrigé')
                    ->modalDescription('Même numéro et même lien de vérification ; le certificat redevient valide.')
                    ->fillForm(fn (Certificate $r) => ['student_name' => $r->student_name])
                    ->schema([TextInput::make('student_name')->label('Nom affiché')->required()->minLength(2)->maxLength(100)
                        ->regex("/^[\pL\pM\s.'’-]+$/u")])
                    ->action(function (Certificate $r, array $data) {
                        app(CertificateService::class)->reissue($r, $data['student_name']);
                        Notification::make()->title('Certificat ré-émis')->success()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageCertificates::route('/')];
    }
}
