<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Test Results') }} - {{ $testAttempt->careerTest->title }}
        </h2>
    </x-slot>

    <div class="py-12">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
        <!-- Results Header -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl mb-8 border border-gray-100">
            <div class="p-8">
                <div class="text-center">
                    <h1 class="text-4xl font-bold text-gray-900 mb-4">Test Results</h1>
                    <p class="text-xl text-gray-600">{{ $testAttempt->careerTest->title }}</p>
                </div>
            </div>
        </div>
        <!-- Success Message -->
        <div class="bg-gradient-to-r from-green-50 to-green-100 border-2 border-green-200 rounded-2xl p-8 mb-8">
            <div class="flex items-center">
                <div class="w-16 h-16 bg-green-500 rounded-2xl flex items-center justify-center mr-6">
                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-green-800 mb-2">Test Completed Successfully!</h3>
                    <p class="text-green-700 text-lg">Thank you for completing the career assessment. Here are your personalized results.</p>
                </div>
            </div>
        </div>

        <!-- Main Result Card -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl mb-8 border border-gray-100">
            <div class="p-8">
                <div class="text-center mb-8">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">{{ $careerResult->career_title }}</h2>
                    <div class="inline-flex items-center px-6 py-3 rounded-full text-xl font-bold bg-gradient-to-r from-primary-100 to-primary-200 text-primary-800">
                        {{ $careerResult->match_percentage }}% Match
                    </div>
                </div>

                <div class="prose max-w-none">
                    <p class="text-xl text-gray-700 leading-relaxed text-center">{{ $careerResult->career_description }}</p>
                </div>
            </div>
        </div>

        <!-- Skills and Career Path -->
        <div class="grid md:grid-cols-2 gap-8 mb-8">
            <!-- Required Skills -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100">
                <div class="p-8">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6">Required Skills</h3>
                    <div class="space-y-4">
                        @foreach($careerResult->career_skills as $skill)
                            <div class="flex items-center p-4 bg-gray-50 rounded-xl">
                                <div class="w-8 h-8 bg-primary-500 rounded-lg flex items-center justify-center mr-4">
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-lg text-gray-700 font-medium">{{ $skill }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Career Progression -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100">
                <div class="p-8">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6">Career Progression Path</h3>
                    <div class="space-y-4">
                        @foreach($careerResult->career_paths as $index => $path)
                            <div class="flex items-center p-4 bg-gray-50 rounded-xl">
                                <div class="w-10 h-10 bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-xl flex items-center justify-center text-lg font-bold mr-4">
                                    {{ $index + 1 }}
                                </div>
                                <span class="text-lg text-gray-700 font-medium">{{ $path }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Analysis -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl mb-8 border border-gray-100">
            <div class="p-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-6">Career Match Analysis</h3>
                <div class="space-y-6">
                    @foreach($careerResult->detailed_analysis as $career => $percentage)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                            <span class="text-lg text-gray-700 font-medium">{{ $career }}</span>
                            <div class="flex items-center">
                                <div class="w-40 bg-gray-200 rounded-full h-3 mr-4">
                                    <div class="bg-gradient-to-r from-primary-500 to-primary-600 h-3 rounded-full transition-all duration-500"
                                         style="width: {{ min($percentage, 100) }}%"></div>
                                </div>
                                <span class="text-lg text-gray-600 w-16 text-right font-semibold">{{ round($percentage, 1) }}%</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Category Breakdown -->
        @if($careerResult->category_scores && count($careerResult->category_scores) > 0)
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl mb-8 border border-gray-100">
            <div class="p-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-6">Category Breakdown</h3>
                <div class="grid md:grid-cols-2 gap-6">
                    @foreach($careerResult->category_scores as $category => $careers)
                        <div class="bg-gray-50 rounded-xl p-6">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4 capitalize">{{ $category }}</h4>
                            <div class="space-y-3">
                                @php
                                    arsort($careers);
                                    $topCareers = array_slice($careers, 0, 3, true);
                                @endphp
                                @foreach($topCareers as $career => $score)
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600">{{ $career }}</span>
                                        <div class="flex items-center">
                                            <div class="w-24 bg-gray-200 rounded-full h-2 mr-3">
                                                <div class="bg-gradient-to-r from-primary-400 to-primary-500 h-2 rounded-full"
                                                     style="width: {{ min($score, 100) }}%"></div>
                                            </div>
                                            <span class="text-sm text-gray-500 w-12 text-right">{{ round($score, 1) }}%</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Action Buttons -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100">
            <div class="p-8">
                <div class="flex flex-col sm:flex-row gap-6 justify-center">
                    <a href="{{ route('dashboard') }}"
                       class="bg-primary-600 text-white px-8 py-4 rounded-xl text-lg font-semibold hover:bg-primary-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 text-center">
                        Back to Dashboard
                    </a>
                    <a href="{{ route('test.history') }}"
                       class="border-2 border-primary-600 text-primary-600 px-8 py-4 rounded-xl text-lg font-semibold hover:bg-primary-50 transition-all duration-300 text-center">
                        View All Results
                    </a>
                    <button onclick="window.print()"
                            class="border-2 border-gray-300 text-gray-700 px-8 py-4 rounded-xl text-lg font-semibold hover:bg-gray-50 transition-all duration-300 text-center">
                        Print Results
                    </button>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>
</x-app-layout>
