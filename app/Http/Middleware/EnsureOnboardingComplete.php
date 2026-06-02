<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingComplete
{
    /**
     * Redirect newly registered users to the onboarding wizard
     * unless they are already on an onboarding/auth/asset route.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            $user &&
            ! $user->hasCompletedOnboarding() &&
            ! $request->routeIs('onboarding*') &&
            ! $request->routeIs('logout') &&
            ! $request->routeIs('verification*') &&
            ! $request->is('storage/*') &&
            ! $request->is('build/*')
        ) {
            return redirect()->route('onboarding', ['step' => 1]);
        }

        return $next($request);
    }
}
