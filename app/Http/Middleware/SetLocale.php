<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->session()->get('locale', config('app.locale'));
        app()->setLocale(in_array($locale, config('site_identity.locales')) ? $locale : 'fr');

        return $next($request);
    }
}
