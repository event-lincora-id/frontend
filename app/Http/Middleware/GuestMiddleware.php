<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class GuestMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Session::has('api_token') && Session::has('user')) {
            $user = Session::get('user');
            $role = is_array($user) ? ($user['role'] ?? 'participant') : ($user->role ?? 'participant');
            
            if (in_array($role, ['admin', 'super_admin'])) {
                return redirect()->route('admin.dashboard');
            }
            
            return redirect()->route('participant.dashboard');
        }

        return $next($request);
    }
}