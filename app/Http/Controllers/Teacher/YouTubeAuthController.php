<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\GoogleToken;
use App\Services\YouTubeService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class YouTubeAuthController extends Controller
{
    public function __construct(private readonly YouTubeService $youtube) {}

    public function redirect(Request $request)
    {
        if (! $this->youtube->isConfigured()) {
            return back()->withErrors(['youtube' => 'YouTube integration is not configured. Set GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET and GOOGLE_REDIRECT_URI.']);
        }

        $state = Str::random(40);
        $request->session()->put('youtube_oauth_state', $state);

        return redirect()->away($this->youtube->authorizationUrl($state));
    }

    public function callback(Request $request)
    {
        $expectedState = $request->session()->pull('youtube_oauth_state');
        if (! $expectedState || $request->query('state') !== $expectedState) {
            return redirect()->route('teacher.dashboard')->withErrors(['youtube' => 'OAuth state mismatch — please try again.']);
        }

        if ($error = $request->query('error')) {
            return redirect()->route('teacher.dashboard')->withErrors(['youtube' => 'Google returned an error: '.$error]);
        }

        $code = (string) $request->query('code');
        if ($code === '') {
            return redirect()->route('teacher.dashboard')->withErrors(['youtube' => 'No authorization code received from Google.']);
        }

        try {
            $tokens = $this->youtube->exchangeCode($code);
            $token  = $this->youtube->storeTokensForUser($request->user(), $tokens);
        } catch (\Throwable $e) {
            return redirect()->route('teacher.dashboard')
                ->withErrors(['youtube' => 'Could not connect YouTube: '.$e->getMessage()]);
        }

        $channel = $token->channel_title ?: 'YouTube channel';

        return redirect()->route('teacher.dashboard')
            ->with('success', "Connected to {$channel}. Lesson recordings can now be uploaded.");
    }

    public function disconnect(Request $request)
    {
        GoogleToken::where('user_id', $request->user()->id)->delete();
        return back()->with('success', 'YouTube channel disconnected.');
    }
}
