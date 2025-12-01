<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasAdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Non connecté → redirection login
        if (! $user) {
            return redirect()->route('filament.admin.auth.login');
            // adapte le nom de route si ton panel a un id différent
        }

        // Connecté mais pas admin → 403
        if (! $user->hasRole('admin')) {
            abort(403, 'Accès réservé aux administrateurs.');
        }

        return $next($request);
    }
}
