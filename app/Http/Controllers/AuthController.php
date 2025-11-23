<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        return redirect()->route('products.search')->with('success', 'Compte créé avec succès !');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Debug: Log login attempt
        \Log::info('🔐 Login attempt', ['email' => $credentials['email']]);
        
        // Check if user exists
        $user = \App\Models\User::where('email', $credentials['email'])->first();
        if (!$user) {
            \Log::error('❌ User not found', ['email' => $credentials['email']]);
            return back()->withErrors([
                'email' => 'Utilisateur introuvable. Email: ' . $credentials['email'],
            ])->onlyInput('email');
        }
        
        // Check password
        if (!\Hash::check($credentials['password'], $user->password)) {
            \Log::error('❌ Invalid password', ['email' => $credentials['email']]);
            return back()->withErrors([
                'email' => 'Mot de passe incorrect pour: ' . $credentials['email'],
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            \Log::info('✅ Login successful', ['email' => $credentials['email']]);
            return redirect()->intended(route('products.search'));
        }

        \Log::error('❌ Auth::attempt failed', ['email' => $credentials['email']]);
        return back()->withErrors([
            'email' => 'Échec de l\'authentification pour: ' . $credentials['email'],
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('products.search');
    }

    // Méthodes pour la réinitialisation de mot de passe

    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
                    ? back()->with(['status' => 'Nous avons envoyé par email votre lien de réinitialisation de mot de passe !'])
                    : back()->withErrors(['email' => 'Nous ne trouvons pas d\'utilisateur avec cette adresse email.']);
    }

    public function showResetPasswordForm(Request $request, $token = null)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();
            }
        );

        return $status === Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('status', 'Votre mot de passe a été réinitialisé avec succès !')
                    : back()->withErrors(['email' => 'Ce token de réinitialisation de mot de passe est invalide.']);
    }
} 