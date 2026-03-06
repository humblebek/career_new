@extends('layouts.guest')

@section('title', 'Verify Secret Word - CareerPath')

@section('content')
<div class="min-h-screen gradient-bg flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Header -->
        <div class="text-center">
            <div class="flex justify-center">
                <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center shadow-lg">
                    <svg class="w-8 h-8 text-primary-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>
            <h2 class="mt-6 text-3xl font-bold text-white">Two-Factor Authentication</h2>
            <p class="mt-2 text-primary-100">Enter your secret word to complete sign in</p>
        </div>

        <!-- Secret Word Form -->
        <div class="glass-effect rounded-2xl p-8 shadow-2xl">
            <form method="POST" action="{{ route('secret-word.check') }}" class="space-y-6">
                @csrf

                <!-- Secret Word -->
                <div>
                    <label for="secret_word" class="block text-sm font-medium text-white mb-2">Secret Word</label>
                    <input id="secret_word"
                           name="secret_word"
                           type="password"
                           autocomplete="off"
                           required
                           autofocus
                           placeholder="Enter your secret word"
                           class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent transition-all">
                    @error('secret_word')
                        <p class="mt-1 text-sm text-red-300">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Info -->
                <div class="bg-white/10 rounded-lg p-4">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-primary-200 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                        <p class="text-sm text-white/80">
                            This is the secret word you set when you created your account. It serves as an additional layer of security.
                        </p>
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-primary-600 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white transition-all">
                        Verify & Sign In
                    </button>
                </div>

                <!-- Back to Login -->
                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-sm text-white/80 hover:text-white transition-colors">
                        ← Back to login
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

