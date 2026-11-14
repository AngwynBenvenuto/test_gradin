<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Auth;
use \Closure;
use \Cete\Libraries\CTUtils;

class RedirectIfNotAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = 'admin')
    {
        if(!Auth::guard($guard)->check()){
            if ($request->routeIs('admin.login')) {
                return $next($request);
            }
            $nextUrl = $request->fullUrl();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message'  => 'Session expired',
                    'redirect' => route('admin.login', [
                        'next' => CTUtils::base64url_encode($nextUrl)
                    ])
                ], 401);
            }
            return redirect()->route('admin.login', [
                'next' => CTUtils::base64url_encode($nextUrl)
            ]);
            // $url_next = CTUtils::base64url_encode($request->fullUrl());
            // session(['url.intended' => $url_next]);
            // return redirect()->route('admin.login', ['next' => $url_next]);
        }
        return $next($request);
    }
}
