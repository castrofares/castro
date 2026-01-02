<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckLocalAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->role !== 'local_admin') {
            return response()->json(['message' => 'Unauthorized. Only Local Admins can access this route.'], 403);
        }

        return $next($request);
    }
}

