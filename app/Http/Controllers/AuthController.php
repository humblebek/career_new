<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Handle login request — step 1: validate email + password,
     * then redirect to secret word verification.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // Store user ID and remember preference in session for step 2
        $request->session()->put('2fa_user_id', $user->id);
        $request->session()->put('2fa_remember', $request->boolean('remember'));

        return redirect()->route('secret-word.verify');
    }

    /**
     * Show the secret word verification form (step 2).
     */
    public function showSecretWordForm(Request $request)
    {
        if (!$request->session()->has('2fa_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.secret-word-verify');
    }

    /**
     * Verify the secret word and complete login (step 2).
     */
    public function verifySecretWord(Request $request)
    {
        $request->validate([
            'secret_word' => 'required|string',
        ]);

        $userId = $request->session()->get('2fa_user_id');
        $remember = $request->session()->get('2fa_remember', false);

        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);

        if (!$user || !Hash::check($request->secret_word, $user->secret_word)) {
            throw ValidationException::withMessages([
                'secret_word' => 'The secret word is incorrect.',
            ]);
        }

        // Clear 2FA session data
        $request->session()->forget(['2fa_user_id', '2fa_remember']);

        // Complete login
        Auth::login($user, $remember);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Show the registration form.
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Handle registration request.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'secret_word' => 'required|string|min:3|max:50',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'secret_word' => Hash::make($request->secret_word),
            'role' => 'student',
        ]);

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
