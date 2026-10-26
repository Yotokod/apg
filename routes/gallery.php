<?php

use App\Livewire\Gallery;
use Illuminate\Support\Facades\Route;

Route::get('/galerie', Gallery::class)->name('gallery');
