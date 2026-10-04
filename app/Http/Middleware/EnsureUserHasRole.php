<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     * @param  string  ...$roles  Role values the user must hold (any one is enough).
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        $allowed = array_filter(
            $roles,
            fn (string $role): bool => $user->hasAnyRole($role),
        );

        abort_if($allowed === [], Response::HTTP_FORBIDDEN);

        return $next($request);
    }
}
