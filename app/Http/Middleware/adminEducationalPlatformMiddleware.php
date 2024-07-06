<?php

namespace App\Http\Middleware;

use Closure;
use App\Traits\SendResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class adminEducationalPlatformMiddleware
{
    use SendResponse;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->user()->user_type == 0 || auth()->user()->user_type == 1) {
            return $next($request);
        } else {
            return $this->send_response(401, 'غير مصرح لك بالدخول', [], null, null, null);
        }
    }
}
