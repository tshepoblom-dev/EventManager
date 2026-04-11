<?php
// app/Http/Middleware/RoleMiddleware.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        if (! $request->user() || ! $request->user()->hasAnyRole($roles)) {
            abort(403, 'Unauthorized.');
        }
        return $next($request);
    }
}