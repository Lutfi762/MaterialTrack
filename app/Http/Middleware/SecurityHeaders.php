<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
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

        // Vite dev-server sedang jalan (ada file public/hot saat `npm run dev`):
        // izinkan origin + websocket HMR — terlepas dari APP_ENV (bisa production di lokal).
        // Origin diambil dari isi public/hot supaya port custom ikut.
        if (Vite::isRunningHot()) {
            $hotUrl = trim((string) (file_get_contents(public_path('hot')) ?: ''));
            $viteDev = filter_var($hotUrl, FILTER_VALIDATE_URL)
                ? $hotUrl . ' ' . preg_replace('#^http#', 'ws', $hotUrl)
                : '';
            // Fallback port default 5173 (host mana pun)
            $viteDev .= ' http://127.0.0.1:5173 http://localhost:5173 ws://127.0.0.1:5173 ws://localhost:5173';
            $viteDev = trim($viteDev);
            if ($viteDev !== '') {
                $csp[1] .= ' ' . $viteDev; // script-src
                $csp[5] .= ' ' . $viteDev; // connect-src
            }
        }

        $response->headers->set('Content-Security-Policy', implode('; ', $csp));

        // HSTS hanya boleh dikirim di respons yang memang HTTPS — kalau dikirim lewat HTTP
        // (mis. dev server lokal dengan APP_ENV=production), browser akan memaksa upgrade
        // origin ini ke HTTPS selama max-age walau server tidak punya TLS → situs jadi
        // unreachable ("ERR_SSL_PROTOCOL_ERROR" / gagal konek total).
        if ($isProduction && $request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
