@extends('layouts.guest')

@section('title', 'Sign In - CareerPath')

@section('content')
<div class="min-h-screen gradient-bg flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Header -->
        <div class="text-center">
            <div class="flex justify-center">
                <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center shadow-lg">
                    <svg class="w-8 h-8 text-primary-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <h2 class="mt-6 text-3xl font-bold text-white">Welcome back</h2>
            <p class="mt-2 text-primary-100">Sign in to continue your career journey</p>
        </div>

        <!-- Login Form -->
        <div class="glass-effect rounded-2xl p-8 shadow-2xl">
            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-white mb-2">Email address</label>
                    <input id="email"
                           name="email"
                           type="email"
                           autocomplete="email"
                           required
                           value="{{ old('email') }}"
                           class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent transition-all">
                    @error('email')
                        <p class="mt-1 text-sm text-red-300">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-white mb-2">Password</label>
                    <input id="password"
                           name="password"
                           type="password"
                           autocomplete="current-password"
                           required
                           class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent transition-all">
                    @error('password')
                        <p class="mt-1 text-sm text-red-300">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember"
                               name="remember"
                               type="checkbox"
                               class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                        <label for="remember" class="ml-2 block text-sm text-white">Remember me</label>
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-primary-600 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white transition-all">
                        Sign in
                    </button>
                </div>

                <!-- Register Link -->
                <div class="text-center">
                    <p class="text-white/80">
                        Don't have an account?
                        <a href="{{ route('register') }}" class="font-medium text-white hover:text-primary-200 transition-colors">
                            Sign up here
                        </a>
                    </p>
                </div>
            </form>
        </div>

        <!-- Demo Credentials -->
{{--        <div class="glass-effect rounded-xl p-6">--}}
{{--            <h3 class="text-lg font-semibold text-white mb-3">Demo Credentials</h3>--}}
{{--            <div class="space-y-2 text-sm">--}}
{{--                <div class="flex justify-between">--}}
{{--                    <span class="text-white/80">Student:</span>--}}
{{--                    <span class="text-white">student@careerpath.com / password</span>--}}
{{--                </div>--}}
{{--                <div class="flex justify-between">--}}
{{--                    <span class="text-white/80">Secret word:</span>--}}
{{--                    <span class="text-white">secret</span>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
    </div>
</div>
@endsection
