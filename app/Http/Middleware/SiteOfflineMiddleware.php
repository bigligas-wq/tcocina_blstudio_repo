<?php

namespace App\Http\Middleware;

use App\Models\BusinessSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SiteOfflineMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}