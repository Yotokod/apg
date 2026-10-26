<?php

use App\Livewire;
use Illuminate\Support\Facades\Route;

// Static pages & blog (workstream F2, see docs/ROADMAP.md)
Route::get('/contact', Livewire\Contact::class)->name('contact');
Route::get('/a-propos', Livewire\About::class)->name('about');
Route::get('/faq', Livewire\Faq::class)->name('faq');
Route::get('/cgv', Livewire\Legal::class)->defaults('type', 'cgv')->name('cgv');
Route::get('/confidentialite', Livewire\Legal::class)->defaults('type', 'privacy')->name('privacy');
Route::get('/mentions-legales', Livewire\Legal::class)->defaults('type', 'mentions')->name('legal');
Route::get('/blog', Livewire\BlogIndex::class)->name('blog');
Route::get('/blog/{slug}', Livewire\BlogShow::class)->name('blog.show');
