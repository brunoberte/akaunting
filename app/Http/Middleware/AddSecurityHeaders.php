<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddSecurityHeaders
{
    public function handle(Request $request, \Closure $next): Response
    {
        $response = $next($request);
        return $response->withHeaders([
            'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
            'Content-Security-Policy'   => "upgrade-insecure-requests",
            'X-Content-Type-Options'    => 'nosniff'
        ]);
    }
}
