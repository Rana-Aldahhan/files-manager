<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponser;
use Closure;
use Illuminate\Http\Request;

class JsonResponseConverter
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
        if ($response->getOriginalContent() instanceof \Exception)
            return $this->errorResponse($response->getOriginalContent()->getMessage(), $response->getOriginalContent()->getCode());
        return $this->successResponse($response->getOriginalContent());
    }
}