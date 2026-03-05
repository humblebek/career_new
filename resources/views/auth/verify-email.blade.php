<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Email Verification') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100">
                <div class="p-8 text-center">
                    <!-- Icon -->
                    <div class="w-20 h-20 bg-gradient-to-r from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>

                    <h1 class="text-3xl font-bold text-gray-900 mb-4">Verify Your Email</h1>

                    <p class="text-gray-600 text-lg mb-8 leading-relaxed">
                        Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you?
                        If you didn't receive the email, we will gladly send you another.
                    </p>

                    @if (session('status') == 'verification-link-sent')
                        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 font-medium">
                            A new verification link has been sent to your email address.
                        </div>
                    @endif

                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <form method="POST" action="{{ route('verification.send') }}">
                            @csrf
                            <button type="submit"
                                class="bg-primary-600 text-white px-8 py-3 rounded-xl font-semibold hover:bg-primary-700 transition-all duration-300 shadow-lg hover:shadow-xl">
                                Resend Verification Email
                            </button>
                        </form>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="text-gray-500 hover:text-gray-700 px-8 py-3 rounded-xl font-semibold border border-gray-300 hover:border-gray-400 transition-all duration-300">
                                Log Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

