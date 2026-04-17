<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Audit Logs</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-6">Security & Activity Log</h3>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-gray-500 border-b border-gray-200">
                                    <th class="pb-3 pr-4 font-semibold">Timestamp</th>
                                    <th class="pb-3 pr-4 font-semibold">Action</th>
                                    <th class="pb-3 pr-4 font-semibold">User</th>
                                    <th class="pb-3 pr-4 font-semibold">Target</th>
                                    <th class="pb-3 font-semibold">IP Address</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($logs as $log)
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-3 pr-4 text-gray-500 whitespace-nowrap">
                                            {{ $log->created_at->format('Y-m-d H:i:s') }}
                                        </td>
                                        <td class="py-3 pr-4">
                                            @php
                                                $color = match(true) {
                                                    str_contains($log->action, 'failed')  => 'bg-red-100 text-red-700',
                                                    str_contains($log->action, 'deleted') => 'bg-orange-100 text-orange-700',
                                                    str_contains($log->action, 'login.success') => 'bg-green-100 text-green-700',
                                                    str_contains($log->action, 'logout')  => 'bg-gray-100 text-gray-700',
                                                    default => 'bg-blue-100 text-blue-700',
                                                };
                                            @endphp
                                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $color }}">
                                                {{ $log->action }}
                                            </span>
                                        </td>
                                        <td class="py-3 pr-4 text-gray-700">
                                            {{ $log->user?->name ?? '<guest>' }}
                                        </td>
                                        <td class="py-3 pr-4 text-gray-600">
                                            @if($log->target_label)
                                                <span class="text-gray-400 text-xs">{{ $log->target_type }} #{{ $log->target_id }}</span><br>
                                                {{ $log->target_label }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="py-3 text-gray-500 font-mono text-xs">
                                            {{ $log->ip_address ?? '—' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-gray-400">No audit log entries yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>