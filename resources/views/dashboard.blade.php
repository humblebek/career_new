<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Welcome Section -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl mb-8 border border-gray-100">
            <div class="p-8">
                <div class="flex items-center">
                    <div class="w-16 h-16 bg-gradient-to-r from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center mr-4">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Welcome back, {{ Auth::user()->name }}!</h3>
                        <p class="text-gray-600">Discover your ideal career path through our comprehensive assessment tests.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Available Tests Section -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl mb-8 border border-gray-100">
            <div class="p-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-6">Available Career Tests</h3>

                @if($availableTests->count() > 0)
                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($availableTests as $test)
                            <div class="border border-gray-200 rounded-2xl p-6 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 bg-gradient-to-br from-white to-gray-50">
                                <div class="w-12 h-12 bg-gradient-to-r from-primary-500 to-primary-600 rounded-xl flex items-center justify-center mb-4">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <h4 class="text-xl font-bold text-gray-900 mb-3">{{ $test->title }}</h4>
                                <p class="text-gray-600 mb-6 leading-relaxed">{{ Str::limit($test->description, 100) }}</p>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">{{ $test->duration_minutes }} minutes</span>
                                    <form method="POST" action="{{ route('test.start', $test) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="bg-primary-600 text-white px-6 py-2 rounded-lg text-sm font-semibold hover:bg-primary-700 transition-all duration-300 shadow-lg hover:shadow-xl">
                                            Start Test
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <p class="text-gray-500 text-lg">No career tests are currently available. Please check back later.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Test History Section -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100">
            <div class="p-8">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-bold text-gray-900">Your Test History</h3>
                    <a href="{{ route('test.history') }}" class="text-primary-600 hover:text-primary-800 text-sm font-semibold flex items-center">
                        View All
                        <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    </a>
                </div>

                @if($testAttempts->count() > 0)
                    <div class="space-y-4">
                        @foreach($testAttempts->take(5) as $attempt)
                            <div class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition-all duration-300 bg-gradient-to-r from-white to-gray-50">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 bg-gradient-to-r from-primary-500 to-primary-600 rounded-xl flex items-center justify-center mr-4">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="text-lg font-bold text-gray-900">{{ $attempt->careerTest->title }}</h4>
                                            <p class="text-sm text-gray-600">
                                                Completed: {{ $attempt->completed_at ? $attempt->completed_at->format('M d, Y') : 'In Progress' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        @if($attempt->status === 'completed' && $attempt->careerResult)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                                                {{ $attempt->careerResult->match_percentage }}% Match
                                            </span>
                                            <div class="mt-2">
                                                <a href="{{ route('test.result', $attempt) }}" class="text-primary-600 hover:text-primary-800 text-sm font-semibold">
                                                    View Results
                                                </a>
                                            </div>
                                        @elseif($attempt->status === 'in_progress')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800">
                                                In Progress
                                            </span>
                                            <div class="mt-2">
                                                <a href="{{ route('test.take', $attempt) }}" class="text-primary-600 hover:text-primary-800 text-sm font-semibold">
                                                    Continue Test
                                                </a>
                                            </div>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-800">
                                                {{ ucfirst($attempt->status) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <p class="text-gray-500 text-lg">You haven't taken any tests yet. Start with one of the available tests above!</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
</x-app-layout>
