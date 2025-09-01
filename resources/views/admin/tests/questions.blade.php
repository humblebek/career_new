<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Questions') }} - {{ $careerTest->title }}
        </h2>
    </x-slot>

    <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl mb-8 border border-gray-100">
            <div class="p-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">Manage Questions</h1>
                        <p class="text-gray-600 text-lg">{{ $careerTest->title }}</p>
                    </div>
                    <div class="flex space-x-4">
                        <a href="{{ route('admin.questions.create', $careerTest) }}"
                           class="bg-primary-600 text-white px-6 py-3 rounded-xl text-lg font-semibold hover:bg-primary-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                            Add Question
                        </a>
                        <a href="{{ route('admin.tests') }}"
                           class="bg-gray-600 text-white px-6 py-3 rounded-xl text-lg font-semibold hover:bg-gray-700 transition-all duration-300">
                            Back to Tests
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-gradient-to-r from-green-50 to-green-100 border-2 border-green-200 rounded-2xl p-6 mb-8">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <p class="text-green-800 text-lg font-semibold">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if($questions->count() > 0)
            <div class="space-y-6">
                @foreach($questions as $question)
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100">
                        <div class="p-8">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center mb-4">
                                        <div class="w-10 h-10 bg-gradient-to-r from-primary-500 to-primary-600 rounded-xl flex items-center justify-center mr-4">
                                            <span class="text-white font-bold">{{ $question->order }}</span>
                                        </div>
                                        <div>
                                            <h3 class="text-xl font-bold text-gray-900">{{ $question->question_text }}</h3>
                                            <div class="flex items-center space-x-4 mt-2">
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-primary-100 text-primary-800">
                                                    {{ ucfirst(str_replace('_', ' ', $question->question_type)) }}
                                                </span>
                                                @if($question->question_type === 'multiple_choice' && $question->options)
                                                    <span class="text-sm text-gray-600">{{ count($question->options) }} options</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    @if($question->question_type === 'multiple_choice' && $question->options)
                                        <div class="ml-14">
                                            <h4 class="text-sm font-semibold text-gray-700 mb-2">Options:</h4>
                                            <ul class="space-y-1">
                                                @foreach($question->options as $option)
                                                    <li class="text-sm text-gray-600">• {{ $option }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>

                                <div class="flex space-x-3 ml-6">
                                    <a href="{{ route('admin.questions.edit', $question) }}"
                                       class="bg-gray-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-700 transition-colors">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('admin.questions.destroy', $question) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-red-700 transition-colors"
                                                onclick="return confirm('Are you sure you want to delete this question?')">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100">
                <div class="p-12 text-center">
                    <div class="w-20 h-20 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">No Questions Added</h3>
                    <p class="text-gray-600 mb-8 text-lg">Start building your test by adding questions.</p>
                    <a href="{{ route('admin.questions.create', $careerTest) }}"
                       class="bg-primary-600 text-white px-8 py-4 rounded-xl text-lg font-semibold hover:bg-primary-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                        Add First Question
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
</x-app-layout>
