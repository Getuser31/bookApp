<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $role = $user->role->name;
        if ($user && $role !== 'Admin') {
            // User role is not admin
            // Redirect to home page with some text in session
            return redirect()->route('book.index')->with('error', 'You do not have admin access');
        }
        return $next($request);
    }
}
