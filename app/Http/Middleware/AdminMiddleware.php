<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Closure;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        \Log::info('AdminMiddleware check', [
            'url' => $request->url(),
            'method' => $request->method(),
            'authenticated' => auth()->check(),
            'user_role' => auth()->check() ? auth()->user()->role : 'not_authenticated'
        ]);

        if (!auth()->check() || !in_array(auth()->user()->role, ['admin', 'developer'], true)) {
            \Log::warning('AdminMiddleware: Access denied', [
                'url' => $request->url(),
                'user_id' => auth()->check() ? auth()->user()->id : 'not_authenticated',
                'user_role' => auth()->check() ? auth()->user()->role : 'not_authenticated'
            ]);
            abort(403, 'Acceso denegado. Se requieren permisos de administrador.');
        }

        return $next($request);
    }
}
