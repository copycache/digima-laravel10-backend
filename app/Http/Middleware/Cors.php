<?php

namespace App\Http\Middleware;

use Closure;

class Cors
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
        $trusted_domains = ["http://localhost:4200","http://localhost:4201","https://staging-app.iqonelitecorp.com","https://www.iqonelitecorp.com", "https://iqonelitecorp.com"];
         
    if(isset($request->server()['HTTP_ORIGIN'])) 
        {
            $origin = $request->server()['HTTP_ORIGIN'];

            if(in_array($origin, $trusted_domains)) 
            {
                header('Access-Control-Allow-Origin: ' . $origin);
                header('Access-Control-Allow-Headers: Origin, Content-Type, Authorization');
                header('Access-Control-Allow-Methods: GET,PUT,POST,DELETE,PATCH,OPTIONS');
            }
        }

        return $next($request);
    }
}

