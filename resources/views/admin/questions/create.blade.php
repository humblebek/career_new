<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add Question') }} - {{ $careerTest->title }}
        </h2>
    </x-slot>

    <div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl mb-8 border border-gray-100">
            <div class="p-8">
                <div class="flex items-center">
                    <div class="w-16 h-16 bg-gradient-to-r from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center mr-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">Add Question</h1>
                        <p class="text-gray-600 text-lg">{{ $careerTest->title }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100">
            <div class="p-8">
                <form method="POST" action="{{ route('admin.questions.store', $careerTest) }}" class="space-y-8">
                    @csrf

                    <!-- Question Text -->
                    <div>
                        <label for="question_text" class="block text-lg font-semibold text-gray-900 mb-3">Question Text</label>
                        <textarea id="question_text"
                                  name="question_text"
                                  rows="3"
                                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-primary-500 focus:ring-primary-500 text-lg"
                                  placeholder="Enter your question..."
                                  required>{{ old('question_text') }}</textarea>
                        @error('question_text')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Question Type -->
                    <div>
                        <label for="question_type" class="block text-lg font-semibold text-gray-900 mb-3">Question Type</label>
                        <select id="question_type"
                                name="question_type"
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-primary-500 focus:ring-primary-500 text-lg"
                                onchange="toggleOptions()"
                                required>
                            <option value="">Select question type</option>
                            <option value="multiple_choice" {{ old('question_type') === 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                            <option value="scale" {{ old('question_type') === 'scale' ? 'selected' : '' }}>Scale (1-10)</option>
                            <option value="short_answer" {{ old('question_type') === 'short_answer' ? 'selected' : '' }}>Short Answer</option>
                        </select>
                        @error('question_type')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Options (for multiple choice) -->
                    <div id="options-section" style="display: none;">
                        <label class="block text-lg font-semibold text-gray-900 mb-3">Answer Options</label>
                        <div id="options-container" class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <input type="text"
                                       name="options[]"
                                       class="flex-1 px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-primary-500 focus:ring-primary-500 text-lg"
                                       placeholder="Option 1">
                                <button type="button" onclick="removeOption(this)" class="text-red-600 hover:text-red-800">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <button type="button"
                                onclick="addOption()"
                                class="mt-3 bg-gray-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-700 transition-colors">
                            Add Option
                        </button>
                        @error('options')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Order -->
                    <div>
                        <label for="order" class="block text-lg font-semibold text-gray-900 mb-3">Question Order</label>
                        <input type="number"
                               id="order"
                               name="order"
                               value="{{ old('order', $careerTest->questions()->count() + 1) }}"
                               min="1"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-primary-500 focus:ring-primary-500 text-lg"
                               required>
                        @error('order')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex justify-end space-x-4 pt-6">
                        <a href="{{ route('admin.tests.questions', $careerTest) }}"
                           class="px-8 py-4 border-2 border-gray-300 text-gray-700 rounded-xl text-lg font-semibold hover:bg-gray-50 transition-all duration-300">
                            Cancel
                        </a>
                        <button type="submit"
                                class="bg-primary-600 text-white px-8 py-4 rounded-xl text-lg font-semibold hover:bg-primary-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                            Add Question
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function toggleOptions() {
    const questionType = document.getElementById('question_type').value;
    const optionsSection = document.getElementById('options-section');

    if (questionType === 'multiple_choice') {
        optionsSection.style.display = 'block';
    } else {
        optionsSection.style.display = 'none';
    }
}

function addOption() {
    const container = document.getElementById('options-container');
    const optionCount = container.children.length + 1;

    const optionDiv = document.createElement('div');
    optionDiv.className = 'flex items-center space-x-3';
    optionDiv.innerHTML = `
        <input type="text"
               name="options[]"
               class="flex-1 px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-primary-500 focus:ring-primary-500 text-lg"
               placeholder="Option ${optionCount}">
        <button type="button" onclick="removeOption(this)" class="text-red-600 hover:text-red-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    `;

    container.appendChild(optionDiv);
}

function removeOption(button) {
    button.parentElement.remove();
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleOptions();
});
</script>
@endsection
