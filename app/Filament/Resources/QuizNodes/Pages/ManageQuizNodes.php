<?php

namespace App\Filament\Resources\QuizNodes\Pages;

use App\Filament\Resources\QuizNodes\QuizNodeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageQuizNodes extends ManageRecords
{
    protected static string $resource = QuizNodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
