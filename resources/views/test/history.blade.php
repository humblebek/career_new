<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Test History
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Your Career Test History</h3>
                            <p class="text-gray-600 mt-1">View all your completed career assessments and results.</p>
                        </div>
                        <a href="{{ route('dashboard') }}"
                           class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700 transition duration-300">
                            Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <!-- Test History List -->
            @if($testAttempts->count() > 0)
                <div class="space-y-6">
                    @foreach($testAttempts as $attempt)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <h4 class="text-lg font-semibold text-gray-900">{{ $attempt->careerTest->title }}</h4>
                                        <p class="text-gray-600 mt-1">{{ $attempt->careerTest->description }}</p>

                                        <div class="flex items-center space-x-6 mt-3 text-sm text-gray-500">
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                </svg>
                                                Completed: {{ $attempt->completed_at->format('M d, Y') }}
                                            </div>
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                </svg>
                                                Duration: {{ $attempt->careerTest->duration_minutes }} minutes
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center space-x-4">
                                        @if($attempt->careerResult)
                                            <div class="text-right">
                                                <div class="text-2xl font-bold text-indigo-600">{{ $attempt->careerResult->match_percentage }}%</div>
                                                <div class="text-sm text-gray-600">Match</div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-lg font-semibold text-gray-900">{{ $attempt->careerResult->career_title }}</div>
                                                <div class="text-sm text-gray-600">Recommended Career</div>
                                            </div>
                                        @endif

                                        <div class="flex flex-col space-y-2">
                                            @if($attempt->careerResult)
                                                <a href="{{ route('test.result', $attempt) }}"
                                                   class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700 transition duration-300 text-center">
                                                    View Results
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                @if($attempt->careerResult)
                                    <div class="mt-4 pt-4 border-t border-gray-200">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <h5 class="text-sm font-medium text-gray-900">Career Description</h5>
                                                <p class="text-sm text-gray-600 mt-1">{{ Str::limit($attempt->careerResult->career_description, 150) }}</p>
                                            </div>
                                            <div class="ml-4">
                                                <h5 class="text-sm font-medium text-gray-900">Key Skills</h5>
                                                <div class="flex flex-wrap gap-1 mt-1">
                                                    @foreach(array_slice($attempt->careerResult->career_skills, 0, 3) as $skill)
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                            {{ $skill }}
                                                        </span>
                                                    @endforeach
                                                    @if(count($attempt->careerResult->career_skills) > 3)
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                            +{{ count($attempt->careerResult->career_skills) - 3 }} more
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $testAttempts->links() }}
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-12 text-center">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">No Test History</h3>
                        <p class="text-gray-600 mb-6">You haven't completed any career tests yet. Start your career exploration journey today!</p>
                        <a href="{{ route('dashboard') }}"
                           class="bg-indigo-600 text-white px-6 py-3 rounded-md text-sm font-medium hover:bg-indigo-700 transition duration-300">
                            Take Your First Test
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
