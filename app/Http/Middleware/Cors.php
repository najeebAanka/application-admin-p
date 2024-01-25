<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Cors
{
    public function handle($request, Closure $next)
    {
        return $next($request);
//            ->header('Access-Control-Allow-Headers', 'Authorization')
//            ->header('Access-Control-Allow-Origin', 'localhost')
//            ->header('Access-Control-Allow-Methods', '*');
    }
}
