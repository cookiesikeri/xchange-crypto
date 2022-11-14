<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DevManager
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

        $auth = $request->header('dev-auth');

        return $next($request);
//        switch (env('APP_ENV')) {
//            case 'local':
//                return $this->localStagingEnv($auth,$next,$request);
//                break;
//            case 'staging':
//                return $this->localStagingEnv($auth,$next,$request);
//                break;
//            case 'production':
//                return $this->prodEnv($auth,$next,$request);
//                break;
//        }


    }


}
