<?php

namespace App\Http\Middleware;

use App\Interfaces\LoggingRepositoryInterface;
use Carbon\Carbon;
use Closure;

use Illuminate\Support\Arr;

class Logging
{
    private LoggingRepositoryInterface $logRepo;

    public function __construct(LoggingRepositoryInterface $logRepo)
    {
        $this->logRepo = $logRepo;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {

        $response = $next($request);
        return $response;
    }
    public function terminate($request, $response)
    {
        if (env('REQUEST_LOGGING', false)) {
            $url = $request->fullUrl();
            $ip = $request->ip();
            $responseToRecord = json_encode([
                'status_code' => $response->getstatusCode(),
                'date' => Carbon::parse($response->getDate())->toString(),
                'response_content' => Arr::except(json_decode($response->getcontent(), true), 'token')
            ]);
            $this->logRepo->create([
                'ip' => $ip,
                'url' => $url,
                'request' => json_encode(Arr::except($request->server->all(), ['HTTP_AUTHORIZATION', 'DOCUMENT_ROOT', 'SCRIPT_FILENAME'])),
                'response' => $responseToRecord
            ]);
        }
    }
}







/*public function handle(Request $request, Closure $next)
{
$response = $next($request);
$log = [
];
return $response;
}*/