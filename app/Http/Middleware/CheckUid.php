<?php

namespace App\Http\Middleware;

use Closure;

class CheckUid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        echo date('Y-m-d');echo '</br>';
        if(empty($_COOKIE['uid'])){
            echo 'No UID';echo '</br>';
        }
        return $next($request);
    }
}
