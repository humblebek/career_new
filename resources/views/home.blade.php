@extends('layouts.guest')

@section('title', 'CareerPath - Discover Your Future Career')

@section('content')
<div class="min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white/90 backdrop-blur-md shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center">
                        <div class="w-8 h-8 bg-gradient-to-r from-primary-500 to-primary-600 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <span class="text-xl font-bold text-gray-900">CareerPath</span>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium transition-colors">Dashboard</a>
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="text-gray-700 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium transition-colors">Admin</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="bg-primary-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium transition-colors">Sign In</a>
                        <a href="{{ route('register') }}" class="bg-primary-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors">Sign Up</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-primary-50 via-white to-primary-50"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="text-center">
                <div class="inline-flex items-center px-4 py-2 rounded-full bg-primary-100 text-primary-800 text-sm font-medium mb-8">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Trusted by 10,000+ students
                </div>
                <h1 class="text-5xl md:text-7xl font-bold text-gray-900 mb-6 leading-tight">
                    Discover Your
                    <span class="bg-gradient-to-r from-primary-600 to-primary-800 bg-clip-text text-transparent">Future Career</span>
                </h1>
                <p class="text-xl text-gray-600 mb-12 max-w-3xl mx-auto leading-relaxed">
                    Take our comprehensive career assessment tests to discover your ideal career path.
                    Get personalized recommendations based on your interests, skills, and personality.
                </p>
                @guest
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="{{ route('register') }}" class="bg-primary-600 text-white px-8 py-4 rounded-xl text-lg font-semibold hover:bg-primary-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                            Get Started Free
                        </a>
                        <a href="{{ route('login') }}" class="border-2 border-primary-600 text-primary-600 px-8 py-4 rounded-xl text-lg font-semibold hover:bg-primary-50 transition-all duration-300">
                            Sign In
                        </a>
                    </div>
                @else
                    <a href="{{ route('dashboard') }}" class="bg-primary-600 text-white px-8 py-4 rounded-xl text-lg font-semibold hover:bg-primary-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                        Go to Dashboard
                    </a>
                @endguest
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Why Choose CareerPath?</h2>
                <p class="text-xl text-gray-600">Comprehensive career assessment tools designed for students</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white rounded-2xl shadow-xl p-8 text-center card-hover border border-gray-100">
                    <div class="w-20 h-20 bg-gradient-to-r from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Comprehensive Tests</h3>
                    <p class="text-gray-600 leading-relaxed">Take detailed assessments covering multiple career domains and personality traits.</p>
                </div>

                <div class="bg-white rounded-2xl shadow-xl p-8 text-center card-hover border border-gray-100">
                    <div class="w-20 h-20 bg-gradient-to-r from-green-500 to-green-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Instant Results</h3>
                    <p class="text-gray-600 leading-relaxed">Get immediate career recommendations with detailed analysis and skill requirements.</p>
                </div>

                <div class="bg-white rounded-2xl shadow-xl p-8 text-center card-hover border border-gray-100">
                    <div class="w-20 h-20 bg-gradient-to-r from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Track Progress</h3>
                    <p class="text-gray-600 leading-relaxed">Monitor your career exploration journey and view your test history.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- How It Works Section -->
    <div class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">How It Works</h2>
                <p class="text-xl text-gray-600">Simple steps to discover your ideal career</p>
            </div>

            <div class="grid md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-2xl flex items-center justify-center mx-auto mb-6 text-2xl font-bold shadow-lg">1</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Sign Up</h3>
                    <p class="text-gray-600 leading-relaxed">Create your free account to get started</p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-2xl flex items-center justify-center mx-auto mb-6 text-2xl font-bold shadow-lg">2</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Take Tests</h3>
                    <p class="text-gray-600 leading-relaxed">Complete comprehensive career assessment tests</p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-2xl flex items-center justify-center mx-auto mb-6 text-2xl font-bold shadow-lg">3</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Get Results</h3>
                    <p class="text-gray-600 leading-relaxed">Receive personalized career recommendations</p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-2xl flex items-center justify-center mx-auto mb-6 text-2xl font-bold shadow-lg">4</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Plan Future</h3>
                    <p class="text-gray-600 leading-relaxed">Use insights to plan your educational and career path</p>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <!-- CTA Section -->
<div class="bg-indigo-600 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold text-white mb-4">Ready to Discover Your Career Path?</h2>
        <p class="text-xl text-indigo-100 mb-8">Join thousands of students who have found their ideal career through our platform.</p>
        @guest
            <a href="{{ route('register') }}" class="bg-white text-indigo-600 px-8 py-3 rounded-lg text-lg font-medium hover:bg-gray-100 transition duration-300">
                Start Your Journey Today
            </a>
        @else
            <a href="{{ route('dashboard') }}" class="bg-white text-indigo-600 px-8 py-3 rounded-lg text-lg font-medium hover:bg-gray-100 transition duration-300">
                Go to Dashboard
            </a>
        @endguest
    </div>
</div>

<!-- Footer -->
<div class="bg-gray-900 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3">
        <p class="text-gray-400 text-sm">&copy; {{ date('Y') }} {{ config('app.name', 'CareerPath') }}. All rights reserved.</p>
        <a href="{{ route('privacy-policy') }}" class="text-gray-400 hover:text-white text-sm underline transition">Privacy Policy</a>
    </div>
</div>

@endsection
