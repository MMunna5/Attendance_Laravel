<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Equivalent to Django's @user_passes_test(is_admin) decorator
 * (is_admin() checked user.is_staff). Register as 'admin' in
 * bootstrap/app.php or app/Http/Kernel.php depending on your
 * Laravel version, then apply with ->middleware('admin') on routes.
 */
class EnsureIsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || !$request->user()->is_admin) {
            abort(403, 'Admin access required.');
        }
        return $next($request);
    }
}
