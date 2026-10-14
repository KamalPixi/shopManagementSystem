<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\SessionGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class RefreshRememberMeCookie
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $guard = auth()->guard('web');
        $user = $guard->user();

        if (!$user instanceof \Illuminate\Contracts\Auth\Authenticatable || !$user->getRememberToken()) {
            return $response;
        }

        $rememberedSession = (bool) session('auth.remember', false);
        $restoredFromRememberCookie = $guard instanceof SessionGuard && $guard->viaRemember();

        if (!$rememberedSession && !$restoredFromRememberCookie) {
            return $response;
        }

        // Preserve remember-me across ordinary session expiry and keep the
        // browser's rolling cookie alive while the user continues using the app.
        session(['auth.remember' => true]);
        $minutes = max(1, (int) config('auth.guards.web.remember', 576000));
        $value = implode('|', [
            $user->getAuthIdentifier(),
            $user->getRememberToken(),
            $user->getAuthPassword(),
        ]);
        $sessionConfig = config('session');

        Cookie::queue(cookie()->make(
            $guard->getRecallerName(),
            $value,
            $minutes,
            $sessionConfig['path'] ?? '/',
            $sessionConfig['domain'] ?? null,
            $sessionConfig['secure'] ?? false,
            $sessionConfig['http_only'] ?? true,
            false,
            $sessionConfig['same_site'] ?? 'lax',
        ));

        return $response;
    }
}
