<?php

namespace App\Http\Middleware\User;

use Closure;
use Illuminate\Http\Request;
use App\Models\Admin\BasicSettings;

class SMSVerificationGuard
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user           = auth()->user();
        $basic_settings = BasicSettings::first();
        if ($basic_settings->sms_verification == true) {
            if ($user->sms_verified == false && $user->full_mobile != null) return smsVerificationTemplate($user);
        }
        return $next($request);
    }
}
