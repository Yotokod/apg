<?php

use App\Http\Controllers\CertificateController;
use Illuminate\Support\Facades\Route;

Route::get('/certificat-apercu', [CertificateController::class, 'preview'])->middleware('auth')->name('certificate.preview');

Route::get('/certificat/{code}', [CertificateController::class, 'show'])
    ->where('code', '[A-Za-z0-9]{12}')
    ->middleware('throttle:30,1')
    ->name('certificate.show');
