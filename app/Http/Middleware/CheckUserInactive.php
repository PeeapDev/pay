<?php

namespace App\Http\Middleware;

use App\Http\Helpers\Common;
use App\Traits\ApiResponse;
use Closure;


class CheckUserInactive
{
    use ApiResponse;

    public function handle($request, Closure $next)
    {
        // if user inactive wouldn't be able to login
        $user = Common::getUserStatus(auth()->user()->status);

        if ($user == 'Inactive') {
            $prefix = str_replace('/', '', request()->route()->getPrefix());
            if (str_contains($prefix, 'apiv2')) {
                return $this->unauthorizedResponse([], __("Inactive"));
            }
            auth()->logout();
            Common::one_time_message('danger', __('Your account is inactivated. Please try again later!'));
            return redirect('/login');
        }
        return $next($request);
    }
}
