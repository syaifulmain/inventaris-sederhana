<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 403,
                    'message' => 'Forbidden. Admin access required',
                    'data' => null,
                    'errors' => null
                ], 403);
            }

            abort(403, 'Forbidden. Admin access required');
        }

        return $next($request);
    }
}
