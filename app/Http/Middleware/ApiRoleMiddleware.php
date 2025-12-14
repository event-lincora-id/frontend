<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ApiRoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role)
    {
        $user = Session::get('user');
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu');
        }

        $userRole = is_array($user) ? ($user['role'] ?? null) : ($user->role ?? null);
        
        // Check if user has required role
        if ($role === 'super_admin' && $userRole !== 'super_admin') {
            abort(403, 'Unauthorized access. Super admin role required.');
        }
        
        if ($role === 'admin' && !in_array($userRole, ['admin', 'super_admin'])) {
            abort(403, 'Unauthorized access. Admin role required.');
        }
        
        if ($role === 'participant' && $userRole !== 'participant') {
            abort(403, 'Unauthorized access. Participant role required.');
        }

        return $next($request);
    }
}