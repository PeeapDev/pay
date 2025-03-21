<?php

namespace App\Http\Middleware\Admin;

use Closure;
use Illuminate\Http\Request;

class ModuleSetting
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next,$access)
    {
        $permission = module_access($access);
        if($permission->status == 0){
            abort(404);
        }
        return $next($request);
    }
}
