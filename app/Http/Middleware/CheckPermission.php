<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponse;
use Common;
use Closure;


class CheckPermission
{
    /**
     * Handle an incoming request.
     */
    use ApiResponse;
    protected $permission;

    public function __construct(Common $permission)
    {
        $this->permission = $permission;
    }

    public function handle($request, Closure $next, $permissions)
    {
        $prefix = request()->route()->getPrefix();
        $prefix = strpos($prefix, '/') ? explode('/', $prefix)[0] : str_replace('/', '', $prefix);

        $userId = ($prefix == config('adminPrefix')) ? auth('admin')->user()->id : auth()->user()->id;

        if ($this->permission->has_permission($userId, $permissions)) {
            return $next($request);
        } else {
            if (str_contains($prefix, 'apiv2')) {
                return $this->forbiddenResponse([], __("Unauthorized"));
            }
            return response()->view('admin.errors.404', [], 404);
        }
    }
}
