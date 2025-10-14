<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Exception;
use App\Models\User;

class SocialAuthController extends Controller
{
    public function redirectToProvider($provider)
    {
        try {
            return Socialite::driver($provider)->redirect();
        } catch (Exception $e) {
            return redirect('/login')->with('error', 'Socialite authentication failed: ' . $e->getMessage());
        }
    }

    public function handleProviderCallback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();

            // Find or create user
            $user = User::where('email', $socialUser->getEmail())->first();

            if ($user) {
                // If user exists but has no provider info, update it
                $user->update([
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                ]);
            } else {
                // Otherwise create a new user
                $user = User::create([
                    'name' => $socialUser->getName() ?? $socialUser->getNickname(),
                    'email' => $socialUser->getEmail(),
                    'email_verified_at' => now(),
                    'password' => bcrypt(Str::random(12)),
                    'role' => 'user', // or assign based on your logic
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                ]);
            }

            Auth::login($user);

            // Redirect to respective Filament dashboards
            if ($user->role === 'admin') {
            return redirect()->route('filament.admin.pages.dashboard');
        } else {
            return redirect()->route('filament.user.pages.dashboard');
        }

        } catch (InvalidStateException $e) {
            // Specific handling for state exceptions
            return redirect('/login')->with('error', 'Session expired. Please try logging in again.');
        } catch (Exception $e) {
            return redirect('/login')->with('error', 'Authentication failed: ' . $e->getMessage());
        }
    }
}