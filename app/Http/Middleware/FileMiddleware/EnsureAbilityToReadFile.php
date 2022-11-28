<?php

namespace App\Http\Middleware\FileMiddleware;

use App\Traits\ApiResponser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAbilityToReadFile
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $file = $request->route('file');

        throw_if($file->status != "free" and $file->reserver_id != Auth::user()->id, \App\Exceptions\ReadFileException::class, "unable to read the file");
        //throw_if($response->getdata()->status != "free", \App\Exceptions\ReadFileException::class, "unable to read the file");
        return $next($request);
    }
}