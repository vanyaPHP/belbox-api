<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Request;

final class CheckPermission
{
    public function handle(
        Request $request,
        \Closure $next,
        string $permission,
    ) {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        if ($user->hasPermission($permission)) {
            return $next($request);
        }

        abort(403, 'Unauthorized action.');
    }
}
