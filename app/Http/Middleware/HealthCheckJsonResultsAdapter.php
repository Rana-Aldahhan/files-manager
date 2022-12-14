<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponser;
use Closure;
use Illuminate\Http\Request;

class HealthCheckJsonResultsAdapter
{
    use ApiResponser;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        $results = json_decode($response->getContent(), true);
        $checkResults = collect($results['checkResults'])->values();
        return response()->json([
            'data' => $checkResults,
        ], 200);
    }
}