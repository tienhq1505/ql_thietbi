<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckDepartmentScope
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->hasRole('using_dept')) {
            if (! $user->department_id) {
                abort(403, 'Tài khoản thuộc Khoa sử dụng chưa được gán khoa/phòng.');
            }
            // Bind scoped department_id into request attributes for easy retrieval
            $request->attributes->set('scoped_department_id', $user->department_id);
        }

        return $next($request);
    }
}
