<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role'     => ['required', 'in:student,teacher'],
            'ref'      => ['nullable', 'string', 'max:20'],
        ]);

        $referrer = null;
        if ($ref = $request->input('ref')) {
            $referrer = User::where('referral_code', strtoupper($ref))->first();
        }

        $user = User::create([
            'name'           => $request->name,
            'email'          => $request->email,
            'password'       => Hash::make($request->password),
            'role'           => $request->role,
            'referred_by'    => $referrer?->id,
            'referral_code'  => \App\Http\Controllers\Student\ReferralController::generateCodeFor($request->name),
        ]);

        event(new Registered($user));

        $user->notify(new WelcomeNotification($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
