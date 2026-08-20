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

        $csp = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-eval' https://cdn.jsdelivr.net",
            "style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net",
            "img-src 'self' data: blob:",
            "font-src 'self' data: https://cdnjs.cloudflare.com",
            "connect-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
            "object-src 'none'",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ];

        // Dev (Vite dev-server): izinkan origin-nya + websocket HMR.
        // Hanya di-nonaktifkan produksi — prod tetap strict.
        if (! $isProduction) {
            $viteDev = 'http://127.0.0.1:5173 http://localhost:5173 ws://127.0.0.1:5173 ws://localhost:5173';
            $csp[1] .= " $viteDev"; // script-src
            $csp[5] .= " $viteDev"; // connect-src
        }

        $response->headers->set('Content-Security-Policy', implode('; ', $csp));

        if ($isProduction) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
