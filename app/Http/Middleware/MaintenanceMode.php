<?php

namespace App\Http\Middleware;

use App\Support\Identity;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Admin-controlled maintenance: visitors get a branded 503, admins and the login/admin paths keep working. */
class MaintenanceMode
{
    private const OPEN_PATHS = ['admin', 'admin/*', 'connexion', 'livewire-*', 'lang/*', 'up', 'storage/*', 'build/*', 'images/*'];

    public function handle(Request $request, Closure $next): Response
    {
        if (! Identity::enabled('maintenance.enabled') || $request->is(...self::OPEN_PATHS) || $this->isAdmin($request)) {
            return $next($request);
        }

        return response()->view('maintenance', ['message' => site('maintenance.message') ?: __('identity.maintenance.message')], 503)
            ->header('Retry-After', '3600')
            ->header('Cache-Control', 'no-store');
    }

    private function isAdmin(Request $request): bool
    {
        return in_array($request->user()?->role, ['admin', 'superadmin'], true);
    }
}
