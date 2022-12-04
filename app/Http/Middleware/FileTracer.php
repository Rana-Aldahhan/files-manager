<?php

namespace App\Http\Middleware;

use App\Interfaces\FileLogRepositoryInterface;
use Closure;
use Illuminate\Http\Request;

class FileTracer
{

    private FileLogRepositoryInterface $fileLogRepo;
    public function __construct(FileLogRepositoryInterface $fileLogRepo)
    {
        $this->fileLogRepo = $fileLogRepo;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $action)
    {
        $response = $next($request);
        $userid = auth()->id();
        collect($response->getOriginalContent())->values()->flatten()->map(function ($file) use ($action, $userid) {
            return $this->fileLogRepo->create([
                'file_id' => $file->id,
                'user_id' => $userid,
                'action' => $action
            ]);
        });
        return $response;

    }
}