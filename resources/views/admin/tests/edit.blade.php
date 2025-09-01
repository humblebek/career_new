<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Test') }}
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">Edit Test</h1>
                        <p class="text-gray-600 text-lg">Update the career assessment test details</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100">
            <div class="p-8">
                <form method="POST" action="{{ route('admin.tests.update', $careerTest) }}" class="space-y-8">
                    @csrf
                    @method('PUT')

                    <!-- Test Title -->
                    <div>
                        <label for="title" class="block text-lg font-semibold text-gray-900 mb-3">Test Title</label>
                        <input type="text"
                               id="title"
                               name="title"
                               value="{{ old('title', $careerTest->title) }}"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-primary-500 focus:ring-primary-500 text-lg"
                               placeholder="Enter test title..."
                               required>
                        @error('title')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Test Description -->
                    <div>
                        <label for="description" class="block text-lg font-semibold text-gray-900 mb-3">Test Description</label>
                        <textarea id="description"
                                  name="description"
                                  rows="4"
                                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-primary-500 focus:ring-primary-500 text-lg"
                                  placeholder="Describe what this test assesses..."
                                  required>{{ old('description', $careerTest->description) }}</textarea>
                        @error('description')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Duration -->
                    <div>
                        <label for="duration_minutes" class="block text-lg font-semibold text-gray-900 mb-3">Duration (minutes)</label>
                        <input type="number"
                               id="duration_minutes"
                               name="duration_minutes"
                               value="{{ old('duration_minutes', $careerTest->duration_minutes) }}"
                               min="1"
                               max="300"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-primary-500 focus:ring-primary-500 text-lg"
                               required>
                        @error('duration_minutes')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox"
                                   name="is_active"
                                   value="1"
                                   {{ old('is_active', $careerTest->is_active) ? 'checked' : '' }}
                                   class="w-5 h-5 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                            <span class="ml-3 text-lg font-semibold text-gray-900">Active (students can take this test)</span>
                        </label>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex justify-end space-x-4 pt-6">
                        <a href="{{ route('admin.tests') }}"
                           class="px-8 py-4 border-2 border-gray-300 text-gray-700 rounded-xl text-lg font-semibold hover:bg-gray-50 transition-all duration-300">
                            Cancel
                        </a>
                        <button type="submit"
                                class="bg-primary-600 text-white px-8 py-4 rounded-xl text-lg font-semibold hover:bg-primary-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                            Update Test
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</x-app-layout>
