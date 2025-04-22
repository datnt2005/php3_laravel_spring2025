<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Bạn cần đăng nhập trước.');
        }

        if (Auth::user()->role !== $role && Auth::user()->role !== 'admin') {
            abort(403, 'Thượng đế không có quyền truy cập.');
        }

        return $next($request);
    }
}
