<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    private const HEADERS = [
        'X-Frame-Options'           => 'SAMEORIGIN',
        'X-Content-Type-Options'    => 'nosniff',
        'X-XSS-Protection'          => '1; mode=block',
        'Referrer-Policy'           => 'strict-origin-when-cross-origin',
        'Permissions-Policy'        => 'camera=(), microphone=(), geolocation=()',
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        foreach (self::HEADERS as $header => $value) {
            $response->headers->set($header, $value);
        }

        if (!$response->headers->has('Content-Security-Policy')) {
            $isLocal = app()->environment('local', 'development');

            // Local dev: allow Vite HMR server and websockets
            $localSrc     = $isLocal ? ' http://localhost:* http://127.0.0.1:*' : '';
            $localWs      = $isLocal ? ' ws://localhost:* ws://127.0.0.1:*' : '';

            $response->headers->set(
                'Content-Security-Policy',
                "default-src 'self'{$localSrc}; "
                . "script-src 'self' 'unsafe-inline' 'unsafe-eval'{$localSrc} https://assets.calendly.com https://cdn.jsdelivr.net https://cdn.alpinejs.dev; "
                . "style-src 'self' 'unsafe-inline'{$localSrc} https://fonts.googleapis.com https://fonts.bunny.net; "
                . "font-src 'self' https://fonts.gstatic.com https://fonts.bunny.net; "
                . "img-src 'self' data: https: blob:; "
                . "media-src 'self' blob: https:; "
                . "frame-src https://calendly.com https://zoom.us https://meet.google.com https://www.youtube.com; "
                . "connect-src 'self'{$localSrc}{$localWs};"
            );
        }

        return $response;
    }
}
