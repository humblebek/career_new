<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $testAttempt->careerTest->title }}
        </h2>
    </x-slot>

    <div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Test Header -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl mb-8 border border-gray-100">
            <div class="p-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $testAttempt->careerTest->title }}</h1>
                        <p class="text-gray-600">{{ $testAttempt->careerTest->description }}</p>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold text-primary-600">{{ $currentQuestionIndex + 1 }}</div>
                        <div class="text-sm text-gray-500">of {{ $questions->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Progress Bar -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl mb-8 border border-gray-100">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-lg font-semibold text-gray-900">Progress</span>
                    <span class="text-lg font-bold text-primary-600">{{ round((($currentQuestionIndex + 1) / $questions->count()) * 100) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="bg-gradient-to-r from-primary-500 to-primary-600 h-3 rounded-full transition-all duration-500"
                         style="width: {{ (($currentQuestionIndex + 1) / $questions->count()) * 100 }}%"></div>
                </div>
            </div>
        </div>

        <!-- Question Card -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100">
            <div class="p-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-8">{{ $currentQuestion->question_text }}</h3>

                <form method="POST" action="{{ route('test.submit', $testAttempt) }}" class="space-y-8">
                    @csrf
                    <input type="hidden" name="question_id" value="{{ $currentQuestion->id }}">

                    @if($currentQuestion->question_type === 'multiple_choice')
                        <div class="space-y-4">
                            @foreach($currentQuestion->options as $index => $option)
                                <label class="flex items-center p-6 border-2 border-gray-200 rounded-xl hover:border-primary-300 hover:bg-primary-50 cursor-pointer transition-all duration-300">
                                    <input type="radio" name="answer_text" value="{{ $option }}" class="mr-4 text-primary-600 focus:ring-primary-500 w-5 h-5">
                                    <span class="text-lg text-gray-900 font-medium">{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                    @elseif($currentQuestion->question_type === 'scale')
                        <div class="space-y-6">
                            <div class="flex items-center justify-between text-lg text-gray-600 font-medium">
                                <span>Not at all</span>
                                <span>Very much</span>
                            </div>
                            <div class="flex items-center justify-center space-x-6">
                                @for($i = 1; $i <= 10; $i++)
                                    <label class="flex flex-col items-center cursor-pointer">
                                        <input type="radio" name="score" value="{{ $i }}" class="mb-2 text-primary-600 focus:ring-primary-500 w-6 h-6">
                                        <span class="text-lg font-semibold text-gray-700">{{ $i }}</span>
                                    </label>
                                @endfor
                            </div>
                            <input type="hidden" name="answer_text" value="Scale rating: {{ $i ?? 1 }}">
                        </div>
                    @else
                        <div>
                            <textarea name="answer_text" rows="6"
                                      class="w-full border-2 border-gray-300 rounded-xl shadow-sm focus:border-primary-500 focus:ring-primary-500 text-lg p-4"
                                      placeholder="Enter your detailed answer here..."></textarea>
                        </div>
                    @endif

                    <div class="flex justify-end pt-6">
                        <button type="submit"
                                class="bg-primary-600 text-white px-8 py-4 rounded-xl text-lg font-semibold hover:bg-primary-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                            @if($currentQuestionIndex + 1 === $questions->count())
                                Complete Test
                            @else
                                Next Question
                            @endif
                        </button>
                    </div>
                    </form>
                </div>
            </div>

        <!-- Test Info -->
        <div class="bg-primary-50 border-2 border-primary-200 rounded-2xl p-6 mt-8">
            <div class="flex items-start">
                <div class="w-12 h-12 bg-primary-500 rounded-xl flex items-center justify-center mr-4">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div>
                    <h4 class="text-lg font-bold text-primary-800 mb-2">Test Information</h4>
                    <p class="text-primary-700 leading-relaxed">
                        This test has <strong>{{ $questions->count() }} questions</strong> and should take approximately <strong>{{ $testAttempt->careerTest->duration_minutes }} minutes</strong> to complete.
                        Take your time to provide thoughtful answers.
                    </p>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>
</x-app-layout>
