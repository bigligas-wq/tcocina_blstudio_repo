<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\GoogleWelcomeMail;
use App\Models\BusinessSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect(Request $request)
    {
        if ((bool) BusinessSetting::get('loyalty_offline', false)) {
            return redirect()->route('login')->withErrors([
                'email' => 'El inicio de sesión con Google no está disponible mientras el álbum está desactivado.',
            ]);
        }

        $returnTo = $request->query('return_to');

        if ($this->isSafeInternalReturnTo($returnTo)) {
            session(['auth.google.return_to' => $returnTo]);
        } else {
            session()->forget('auth.google.return_to');
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $exception) {
            return redirect()->route('login')->withErrors([
                'email' => 'No se pudo iniciar sesion con Google. Verifica la configuracion OAuth.',
            ]);
        }

        $user = User::where('google_id', $googleUser->id)
            ->orWhere('email', $googleUser->email)
            ->first();

        $isNewUser = false;

        if (!$user) {
            $user = User::create([
                'name' => $googleUser->name ?? 'Cliente',
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
                'avatar' => $googleUser->avatar,
                'password' => Hash::make(Str::random(40)),
                'role' => 'customer',
                'is_active' => true,
            ]);

            $isNewUser = true;
        } else {
            $user->update([
                'google_id' => $googleUser->id,
                'avatar' => $googleUser->avatar ?: $user->avatar,
                'name' => $user->name ?: ($googleUser->name ?? 'Cliente'),
            ]);
        }

        if ($isNewUser && config('loyalty.welcome_email.enabled')) {
            try {
                $welcomeMail = new GoogleWelcomeMail($user);

                if (config('loyalty.welcome_email.queue')) {
                    Mail::to($user->email)->queue($welcomeMail);
                } else {
                    Mail::to($user->email)->send($welcomeMail);
                }
            } catch (\Throwable $exception) {
                // No bloqueamos el login si el proveedor de correo tiene fallas.
                Log::warning('No se pudo enviar email de bienvenida Google.', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        Auth::login($user, true);

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === 'kitchen') {
            return redirect()->route('kitchen.index');
        }

        $returnTo = session()->pull('auth.google.return_to');
        if ($this->isSafeInternalReturnTo($returnTo)) {
            return redirect()->to($returnTo);
        }

        return redirect()->route('loyalty.dashboard');
    }

    private function isSafeInternalReturnTo(?string $url): bool
    {
        if (!$url) {
            return false;
        }

        $parsed = parse_url($url);
        if ($parsed === false) {
            return false;
        }

        if (isset($parsed['scheme']) && !in_array($parsed['scheme'], ['http', 'https'], true)) {
            return false;
        }

        if (isset($parsed['host'])) {
            $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
            if (!$appHost || strcasecmp((string) $parsed['host'], (string) $appHost) !== 0) {
                return false;
            }
        }

        return true;
    }
}
