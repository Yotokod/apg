<?php

namespace App\Filament\Resources\SimParams\Pages;

use App\Filament\Resources\SimParams\SimParamResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageSimParams extends ManageRecords
{
    protected static string $resource = SimParamResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
