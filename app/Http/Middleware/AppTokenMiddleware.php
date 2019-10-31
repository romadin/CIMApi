<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 19-3-2019
 * Time: 14:59
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AppTokenMiddleware
{

    public function handle(Request $request, Closure $next)
    {
        if ( $request->input('appToken') === env('APP_KEY') ) {
            return $next($request);
        }
        return response($request->input('appToken'), 403);

    }

}
