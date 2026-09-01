<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Jika user sudah login, lanjutkan
        if (auth()->check()) {
            return $next($request);
        }

        // Jika belum login, redirect ke login
        return redirect()->route('login')->with('warning', 'Silakan login terlebih dahulu');
    }
}
