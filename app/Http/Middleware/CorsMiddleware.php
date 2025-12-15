<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $allowedOrigins = [
            'http://localhost:3000',
            'http://127.0.0.1:3000',
            'http://localhost:8000',
            'http://127.0.0.1:8000',
            'http://localhost',
            'http://127.0.0.1',
            'http://localhost/gestionAcademica/front-end',
            'http://127.0.0.1/gestionAcademica/front-end',
            'http://localhost/gestionAcademica',
            'http://127.0.0.1/gestionAcademica',
        ];
        
        // For development, allow any origin
        if (app()->environment('local')) {
            $origin = $request->header('Origin');
            if ($origin) {
                $allowedOrigins[] = $origin;
            }
        }

        $origin = $request->header('Origin');
        
        // For development, allow any origin but only if it's in the allowed list
        $origin = in_array($origin, $allowedOrigins) ? $origin : null;
        
        // If no valid origin, return a 403 response for non-OPTIONS requests
        if (!$origin && !$request->isMethod('OPTIONS')) {
            return response('Not allowed', 403);
        }
        
        $headers = [
            'Access-Control-Allow-Origin'      => $origin,
            'Access-Control-Allow-Methods'     => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
            'Access-Control-Allow-Headers'     => 'Content-Type, Authorization, X-Requested-With, X-XSRF-TOKEN, x-csrf-token, Accept',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Expose-Headers'    => 'Authorization, XSRF-TOKEN, x-csrf-token',
            'Vary'                             => 'Origin',
        ];

        if ($request->isMethod('OPTIONS')) {
            return response()->json(['status' => 'success'], 200, $headers);
        }

        $response = $next($request);
        
        foreach ($headers as $key => $value) {
            $response->headers->set($key, $value);
        }

        return $response;
    }
}
