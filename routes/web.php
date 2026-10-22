<?php

use App\Livewire;
use App\Models\Order;
use Illuminate\Support\Facades\Route;

// Start page is configurable by the superadmin (site_identity.home_page)
Route::get('/', fn () => redirect()->route(match (site('home_page')) { 'shop' => 'shop', 'courses' => 'courses', default => 'home' }));

Route::get('/accueil', Livewire\Home::class)->name('home');
Route::get('/boutique', Livewire\Shop::class)->name('shop');
Route::get('/panier', Livewire\Cart::class)->name('cart');
Route::get('/formations', Livewire\Courses::class)->name('courses');
Route::get('/formations/{slug}', Livewire\CourseShow::class)->name('courses.show');
Route::get('/lecon/{id}', Livewire\LessonPlayer::class)->middleware('auth')->name('lesson');
Route::get('/simulateur', Livewire\Simulator::class)->name('simulator');
Route::get('/contact', Livewire\Contact::class)->name('contact');
Route::get('/connexion', Livewire\Login::class)->middleware('guest')->name('login');
Route::get('/inscription', Livewire\Register::class)->middleware('guest')->name('register');
Route::get('/compte', Livewire\Account::class)->middleware('auth')->name('account');

Route::get('/lang/{locale}', function (string $locale) {
    abort_unless(in_array($locale, config('site_identity.locales')), 404);
    session(['locale' => $locale]);

    return back();
})->name('lang');

// Payment return: payment is confirmed server-side/by admin, never from the browser redirect alone.
Route::get('/paiement/retour/{order}', fn (Order $order) => redirect()->route('account')->with('status', 'payment_pending'))->name('payment.return');
Route::post('/paiement/webhook/{gateway}', function (string $gateway) {
    \Log::info("Webhook $gateway", request()->all());

    return response()->json(['ok' => true]); // verification + marking paid is done by admin until gateway keys are verified
})->name('payment.webhook');
