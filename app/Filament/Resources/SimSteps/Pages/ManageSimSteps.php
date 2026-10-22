<?php

namespace App\Filament\Resources\SimSteps\Pages;

use App\Filament\Resources\SimSteps\SimStepResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageSimSteps extends ManageRecords
{
    protected static string $resource = SimStepResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
