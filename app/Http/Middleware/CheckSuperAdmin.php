<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->role !== 'super_admin') {
            return response()->json(['message' => 'Unauthorized. Only Super Admins can access this route.'], 403);
        }

        return $next($request);
    }
}

