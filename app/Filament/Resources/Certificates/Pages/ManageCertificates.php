<?php

namespace App\Filament\Resources\Certificates\Pages;

use App\Filament\Resources\Certificates\CertificateResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;

class ManageCertificates extends ManageRecords
{
    protected static string $resource = CertificateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')->label('Aperçu du certificat')->icon(Heroicon::OutlinedEye)->color('gray')
                ->url(route('certificate.preview'))->openUrlInNewTab(),
        ];
    }
}
