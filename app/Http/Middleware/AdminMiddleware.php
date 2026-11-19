<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // not logged in -> admin login page
        if (! $user) {
            return redirect()->route('admin.login');
        }

        // logged in, but not an admin -> forbidden
        if (! $user->is_admin) {
            abort(403, 'You do not have access to the admin area.');
        }

        return $next($request);
    }
}
