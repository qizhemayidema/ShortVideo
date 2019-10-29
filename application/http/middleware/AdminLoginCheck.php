<?php

namespace app\http\middleware;

use think\facade\Session;

class AdminLoginCheck
{
    public function handle($request, \Closure $next)
    {
        if (!Session::get('admin')){
            return redirect('/admin/login');
        }
        return $next($request);
    }
}
