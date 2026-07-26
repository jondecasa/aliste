<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (InvalidStateException) {
            // Ocurre si el enlace de Google ha caducado, se ha reutilizado
            // una URL antigua o alguien visita /auth/google/callback
            // directamente sin pasar antes por /auth/google/redirigir. No es
            // un fallo de la aplicación, así que se redirige con un aviso en
            // vez de dejar que reviente como una excepción no controlada.
            return redirect()->route('login')->with('status', 'El enlace de acceso con Google ha caducado. Vuelve a intentarlo.');
        }

        $user = User::firstWhere('google_id', $googleUser->getId());

        if (! $user) {
            $user = User::firstWhere('email', $googleUser->getEmail());
        }

        if ($user) {
            if (! $user->google_id) {
                $user->forceFill(['google_id' => $googleUser->getId()])->save();
            }
        } else {
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'password' => Str::password(32),
                'email_verified_at' => now(),
            ]);
        }

        Auth::login($user, remember: true);

        Session::regenerate();

        return redirect()->intended(route('profile', absolute: false));
    }
}
