<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Security headers HTTP: CSP (strict), anti-framing, anti-MIME-sniffing,
 * referrer policy, permissions policy, dan HSTS (khusus produksi).
 *
 * Catatan:
 *  - 'unsafe-eval'  : vue-i18n & apexcharts melakukan kompilasi saat runtime.
 *  - 'unsafe-inline' style: bindings :style Vue + Tailwind.
 *  - CDN bootstrap/font-awesome di-allowlist, bukan wildcard.
 *  - Ziggy routes TIDAK lagi inline (@routes dihapus) -> script-src tanpa 'unsafe-inline'.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $isProduction = app()->environment('production');

        // Jangan bocorkan versi PHP server
        $response->headers->remove('X-Powered-By');

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=(), display-capture=()');

        $response->headers->set('Content-Security-Policy', implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-eval' https://cdn.jsdelivr.net",
            "style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net",
            "img-src 'self' data: blob:",
            "font-src 'self' data: https://cdnjs.cloudflare.com",
            "connect-src 'self'",
            "object-src 'none'",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ]));

        if ($isProduction) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
