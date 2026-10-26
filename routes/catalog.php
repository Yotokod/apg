<?php

use App\Livewire\ProductShow;
use Illuminate\Support\Facades\Route;

Route::get('/produit/{slug}', ProductShow::class)->name('product.show');
