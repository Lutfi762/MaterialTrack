<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Paksa HTTPS di produksi (301). Di belakang proxy, isSecure() membaca
 * X-Forwarded-Proto — wajib set TRUSTED_PROXIES di .env produksi.
 */
class HttpsRedirect
{
    public function handle(Request $request, Closure $next): Response
    {
        $appUrl = (string) config('app.url');

        // Enforce HTTPS hanya bila deployment memang HTTPS (APP_URL=https://…).
        if (app()->environment('production')
            && ! app()->runningInConsole()
            && str_starts_with($appUrl, 'https://')
            && ! $request->isSecure()
        ) {
            return redirect()->secure($request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
