<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class UpdateLastSeen
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if ($user) {
            // Throttle to once per 60s per user to avoid write storms.
            Cache::remember("presence:{$user->id}", 60, function () use ($user) {
                $user->forceFill([
                    'last_seen_at'        => now(),
                    'availability_status' => $user->availability_status === 'offline' ? 'online' : $user->availability_status,
                ])->saveQuietly();
                return true;
            });
        }
        return $next($request);
    }
}
