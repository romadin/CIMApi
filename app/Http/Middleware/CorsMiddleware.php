<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 12-1-2019
 * Time: 17:30
 */

namespace App\Http\Middleware;


use Closure;
use Illuminate\Http\Request;

class CorsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        //Intercepts OPTIONS requests
        if ($request->isMethod('OPTIONS')) {
            $response = response('', 200);
        } else {
            // Pass the request to the next middleware
            $response = $next($request);
        }

        $response->header('Access-Control-Allow-Methods', 'HEAD, GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->header('Access-Control-Allow-Headers', $request->header('Access-Control-Request-Headers'));
        $response->header('Access-Control-Allow-Origin', '*');

        return $response;
    }

}