<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Allow any active user whose role grants admin-panel access
     * (super admin, admin, or a custom staff role that has permissions).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('admin.login')->with('error', 'Please login to access the admin panel.');
        }

        $user = Auth::user();

        if (!$user->isAdmin()) {
            return redirect()->route('admin.login')->with('error', 'Access denied. You do not have administrator permissions.');
        }

        if ($user->is_active === false) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')->with('error', 'Your account has been deactivated. Please contact the super administrator.');
        }

        return $next($request);
    }
}
