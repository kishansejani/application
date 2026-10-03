<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Usage: ->middleware('permission:manage_products')
     * Several permissions may be passed (any one grants access): permission:manage_orders,manage_invoices
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('admin.login');
        }

        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission)) {
                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to perform this action.'], 403);
        }

        abort(403, 'You do not have permission to access this section.');
    }
}
