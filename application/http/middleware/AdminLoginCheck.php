<?php

namespace app\http\middleware;

class AdminLoginCheck
{
    public function handle($request, \Closure $next)
    {
        return $next($request);
    }
}
