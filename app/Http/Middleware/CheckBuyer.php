<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckBuyer
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->role !== 'buyer') {
            return response()->json(['message' => 'Unauthorized. Only Buyers can access this route.'], 403);
        }

        return $next($request);
    }
}
