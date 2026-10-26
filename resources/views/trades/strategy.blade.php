@extends('layouts.app')

@section('title', 'Strategy Details')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('trades') }}" class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Trades
        </a>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Strategy Details</h1>
    </div>

    <section class="mb-6 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6">
        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Interactive Brokers order</p>
        <h2 class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">{{ $brokerageOrderId }}</h2>
        <div class="mt-4 flex flex-wrap gap-6 text-sm text-gray-600 dark:text-gray-300">
            <span>{{ $positions->count() }} {{ $positions->count() === 1 ? 'position' : 'positions' }}</span>
            <span>{{ $positions->pluck('instrument.symbol')->unique()->implode(' + ') }}</span>
            <span class="font-semibold {{ $realizedPnl > 0 ? 'text-green-600' : ($realizedPnl < 0 ? 'text-red-600' : '') }}">
                Realized P&amp;L: {{ $realizedPnl >= 0 ? '+' : '-' }}${{ number_format(abs((float) $realizedPnl), 2) }}
            </span>
            @if($positions->contains(fn($position) => $position->isOpen()))
                <span class="font-medium text-amber-600">Partially or fully open</span>
            @endif
        </div>
    </section>

    <section class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Instrument</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Quantity</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Opened</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Closed</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Realized P&amp;L</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($positions as $position)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">
                                {{ $position->instrument->symbol }}
                                @if($position->instrument->expiry)
                                    <span class="block text-xs font-normal text-gray-500">{{ $position->instrument->expiry->format('M d, Y') }}</span>
                                @endif
                                @if($position->instrument->strike !== null)
                                    <span class="block text-xs font-normal text-gray-500">
                                        ${{ number_format((float) $position->instrument->strike, 2) }}
                                        {{ $position->instrument->put_call === 'C' ? 'Call' : 'Put' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ number_format((float) $position->quantity, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $position->open_datetime->format('M d, Y H:i') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $position->close_datetime?->format('M d, Y H:i') ?? 'Open' }}</td>
                            <td class="px-4 py-3 text-right text-sm font-semibold {{ $position->realized_pnl > 0 ? 'text-green-600' : ($position->realized_pnl < 0 ? 'text-red-600' : 'text-gray-500') }}">
                                @if($position->realized_pnl !== null)
                                    {{ $position->realized_pnl > 0 ? '+' : ($position->realized_pnl < 0 ? '-' : '') }}${{ number_format(abs((float) $position->realized_pnl), 2) }}
                                @else
                                    Open
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('trades.show', $position) }}" class="text-sm font-medium text-orange-600 hover:text-orange-700">View position</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
