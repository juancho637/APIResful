<?php

namespace App\Http\Middleware;

use Closure;

class signatureMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param string $header
     * @return mixed
     */
    public function handle($request, Closure $next, $header = 'X-Name')
    {
        $response = $next($request);
        $response->headers->set($header, config('app.name'));

        return $response;
    }
}
