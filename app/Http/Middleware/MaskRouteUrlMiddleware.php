<?php

namespace App\Http\Middleware;

use App\Support\RouteCipher;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MaskRouteUrlMiddleware
{
    /**
     * Handle an incoming request and redirect raw URLs to clean masked xxxx-xxxx-xxxx format.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only inspect GET requests
        if (! $request->isMethod('GET')) {
            return $next($request);
        }

        // Skip AJAX, JSON, downloads, and webhooks
        if ($request->expectsJson() || $request->ajax()) {
            return $next($request);
        }

        // Skip auth, API, system, media endpoints, and public views
        if ($request->is(
            'api/*',
            'webhook/*',
            'fonnte-webhook',
            'watzap-webhook',
            'baileys-session',
            'system/*',
            'media/*',
            'up',
            'login*',
            'register*',
            'logout*',
            'public-dashboard*',
            'display*',
            'live-dashboard*'
        )) {
            return $next($request);
        }

        // Skip download and export endpoints
        if ($request->is(
            '*/download*',
            '*/template*',
            '*/print*'
        )) {
            return $next($request);
        }

        // Check if the current request is already on a clean masked token without raw query params
        $path = trim($request->path(), '/');
        $tokenInPath = str_starts_with($path, 'r/') ? substr($path, 2) : $path;

        if (preg_match('/^[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}$/', $tokenInPath)) {
            // Already clean masked URL in xxxx-xxxx-xxxx format. If there are no raw query params, pass through
            if (empty($request->query())) {
                return $next($request);
            }
        }

        // If user is accessing a raw named route, resolve its masked xxxx-xxxx-xxxx URL
        if (auth()->check()) {
            $maskedUrl = RouteCipher::resolveCurrentEncryptedUrl($request);
            if ($maskedUrl !== null && $maskedUrl !== $request->fullUrl()) {
                return redirect()->to($maskedUrl, 302);
            }
        }

        return $next($request);
    }
}
