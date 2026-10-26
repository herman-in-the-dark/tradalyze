@extends('layouts.app')

@section('title', 'Import Logs')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Import Logs</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Review CSV and Flex imports, including errors and skipped records.</p>
        </div>
        <a href="{{ route('trades.create') }}?tab=csv" class="inline-flex items-center justify-center rounded-lg bg-orange-600 px-4 py-2 text-sm font-medium text-white hover:bg-orange-700">
            Import trades
        </a>
    </div>

    <div class="mb-4 flex flex-wrap gap-2">
        @foreach(['all' => 'All', 'error' => 'Errors', 'success' => 'Successful', 'started' => 'In progress'] as $value => $label)
            <a href="{{ $value === 'all' ? route('trade-import-logs.index') : route('trade-import-logs.index', ['status' => $value]) }}"
               class="rounded-full border px-3 py-1.5 text-sm {{ ($value === 'all' && !$status) || $status === $value ? 'border-orange-600 bg-orange-600 text-white' : 'border-gray-300 bg-white text-gray-700 hover:border-orange-400 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Source</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">File</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Result / Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($logs as $log)
                        <tr class="align-top">
                            <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-600 dark:text-gray-300">
                                {{ $log->created_at->format('M d, Y H:i:s') }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 text-sm font-medium text-gray-800 dark:text-gray-200">
                                {{ strtoupper($log->source) }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold
                                    {{ $log->status === 'error' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' : ($log->status === 'success' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300') }}">
                                    {{ ucfirst($log->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-300">
                                {{ $log->file_name ?: '—' }}
                                @if($log->file_size_bytes)
                                    <span class="mt-1 block text-xs text-gray-500">{{ number_format($log->file_size_bytes / 1048576, 2) }} MB</span>
                                @endif
                            </td>
                            <td class="min-w-72 px-4 py-4 text-sm text-gray-700 dark:text-gray-300">
                                <p class="{{ $log->status === 'error' ? 'font-medium text-red-700 dark:text-red-300' : '' }}">{{ $log->message }}</p>
                                @if($log->status === 'success')
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        {{ $log->fills_imported }} fills · {{ $log->instruments_created }} instruments · {{ $log->positions_created }} positions
                                    </p>
                                @endif
                                @if(!empty($log->details['errors']))
                                    <ul class="mt-2 list-inside list-disc space-y-1 text-xs text-red-600 dark:text-red-300">
                                        @foreach($log->details['errors'] as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                No import logs found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $logs->links() }}</div>
</div>
@endsection
