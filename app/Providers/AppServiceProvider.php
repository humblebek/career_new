<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // 5 attempts per minute keyed by email + IP — covers both brute-force and credential stuffing
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->input('email') . '|' . $request->ip())
                ->response(function () {
                    return back()
                        ->withErrors(['email' => 'Too many login attempts. Please wait 1 minute before trying again.'])
                        ->withInput();
                });
        });

        // 5 attempts per minute keyed by session user ID + IP
        RateLimiter::for('secret-word', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->session()->get('2fa_user_id', 'unknown') . '|' . $request->ip())
                ->response(function () {
                    return back()
                        ->withErrors(['secret_word' => 'Too many attempts. Please wait 1 minute before trying again.'])
                        ->withInput();
                });
        });
    }
}
