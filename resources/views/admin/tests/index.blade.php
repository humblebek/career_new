<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Career Tests') }}
        </h2>
    </x-slot>

    <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl mb-8 border border-gray-100">
            <div class="p-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">Manage Career Tests</h1>
                        <p class="text-gray-600 text-lg">Create, edit, and manage your career assessment tests</p>
                    </div>
                    <a href="{{ route('admin.tests.create') }}"
                       class="bg-primary-600 text-white px-6 py-3 rounded-xl text-lg font-semibold hover:bg-primary-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                        Create New Test
                    </a>
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

        @if($tests->count() > 0)
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-bold text-gray-900 uppercase tracking-wider">Test</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-gray-900 uppercase tracking-wider">Questions</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-gray-900 uppercase tracking-wider">Attempts</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-gray-900 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-gray-900 uppercase tracking-wider">Created</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-gray-900 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($tests as $test)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $test->title }}</div>
                                                <div class="text-sm text-gray-500">{{ Str::limit($test->description, 60) }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $test->questions_count }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $test->test_attempts_count }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $test->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $test->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $test->created_at->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-3">
                                                <a href="{{ route('admin.tests.questions', $test) }}"
                                                   class="bg-primary-600 text-white px-3 py-1 rounded-lg text-xs font-semibold hover:bg-primary-700 transition-colors">Questions</a>
                                                <a href="{{ route('admin.tests.edit', $test) }}"
                                                   class="bg-gray-600 text-white px-3 py-1 rounded-lg text-xs font-semibold hover:bg-gray-700 transition-colors">Edit</a>
                                                <form method="POST" action="{{ route('admin.tests.destroy', $test) }}" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="bg-red-600 text-white px-3 py-1 rounded-lg text-xs font-semibold hover:bg-red-700 transition-colors"
                                                            onclick="return confirm('Are you sure you want to delete this test?')">
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $tests->links() }}
                </div>
        @else
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100">
                <div class="p-12 text-center">
                    <div class="w-20 h-20 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">No Tests Created</h3>
                    <p class="text-gray-600 mb-8 text-lg">Get started by creating your first career test.</p>
                    <a href="{{ route('admin.tests.create') }}"
                       class="bg-primary-600 text-white px-8 py-4 rounded-xl text-lg font-semibold hover:bg-primary-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                        Create Your First Test
                    </a>
                </div>
            </div>
        @endif
        </div>
    </div>
</div>
</x-app-layout>
