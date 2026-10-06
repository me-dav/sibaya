<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pemakaian: ->middleware('role:admin') atau 'role:admin,siswa'
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $allowed = array_map(
            fn (string $role) => UserRole::from($role),
            $roles
        );

        if (! in_array($user->role, $allowed, true)) {
            abort(403, 'Sampeyan ora duwe akses menyang kaca iki.');
        }

        return $next($request);
    }
}
