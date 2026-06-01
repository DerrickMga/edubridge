<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ReferralCredit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReferralController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (! $user->referral_code) {
            $user->update(['referral_code' => self::generateCodeFor($user->name)]);
        }

        $referrals = $user->referrals()->latest()->take(50)->get();
        $credits = $user->referralCredits()->with('referred', 'payment')->latest()->take(50)->get();
        $totalEarned = (float) $user->referralCredits()->whereIn('status', ['credited', 'paid'])->sum('amount');
        $shareUrl = route('register', ['ref' => $user->referral_code]);

        return view('student.referrals.index', compact('referrals', 'credits', 'totalEarned', 'shareUrl'));
    }

    public static function generateCodeFor(string $name): string
    {
        $base = Str::upper(Str::substr(Str::slug($name, ''), 0, 4)) ?: 'USR';
        do {
            $code = $base.Str::upper(Str::random(4));
        } while (\App\Models\User::where('referral_code', $code)->exists());
        return $code;
    }
}
