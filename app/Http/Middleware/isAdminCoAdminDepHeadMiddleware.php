<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class isAdminCoAdminDepHeadMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        //return $next($request);

        $user = Auth::user();
        
        if($user && ($user->role == 'admin' || $user->role == 'department head' || $user->role == 'co-admin'))
        {
            return $next($request);
        }
        else
        {
            return redirect()->route('index');
        }
    }
}
