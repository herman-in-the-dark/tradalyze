@extends('layouts.app')

@section('title', 'Trades')

@section('content')
<div class="max-w-full overflow-hidden">
<!-- Confirmation Modal -->
<div id="confirmation-modal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4 transform transition-all">
        <div class="p-6">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0">
                    <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white" id="modal-title">Confirm Deletion</h3>
                </div>
            </div>
            <p class="text-gray-600 dark:text-gray-300 mb-6" id="modal-message">Are you sure you want to delete this position? This action cannot be undone.</p>
            <div class="flex justify-end space-x-3">
                <button onclick="closeConfirmModal()" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-300 font-medium rounded-lg transition-colors">
                    Cancel
                </button>
                <button onclick="confirmDelete()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 md:mb-8">
    <div>
        <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-2">Trades</h2>
        <p class="text-sm md:text-base text-gray-600 dark:text-gray-400">View and manage your trading history</p>
    </div>
    <a href="{{ route('trades.create') }}" class="w-full sm:w-auto bg-orange-600 hover:bg-orange-700 text-white font-semibold px-4 md:px-6 py-3 rounded-lg shadow-md transition-colors duration-200 flex items-center justify-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Add Trade
    </a>
</div>

<!-- Success/Error Messages -->
@if(session('success'))
    <div class="flash-message mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-300 px-4 py-3 rounded-lg flex items-center justify-between">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-green-600 hover:text-green-800">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
@endif

@if(session('error'))
    <div class="flash-message mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 px-4 py-3 rounded-lg flex items-center justify-between">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-800">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
@endif

<!-- Filters Card -->
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 mb-4 overflow-hidden">
    <div class="px-4 md:px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 overflow-x-hidden">
        <!-- Filter Toggle Button -->
        <button
            type="button"
            onclick="toggleFilters()"
            class="w-full flex items-center justify-between mb-3 px-4 py-2 bg-white dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-600 rounded-lg transition-colors duration-200"
        >
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                </svg>
                <span class="font-semibold text-gray-700 dark:text-gray-300">Filters</span>
                @if(request()->except(['page', 'per_page']))
                    <span class="ml-2 px-2 py-0.5 bg-orange-100 text-orange-700 text-xs font-medium rounded-full">
                        Active
                    </span>
                @endif
            </div>
            <svg id="filter-chevron" class="w-5 h-5 text-gray-600 dark:text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>

        <form method="GET" action="{{ route('trades') }}" id="filter-form" class="hidden">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-3 md:gap-4">
                <!-- Symbol Search -->
                <div>
                    <label for="symbol" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Symbol</label>
                    <input 
                        type="text" 
                        name="symbol" 
                        id="symbol" 
                        value="{{ request('symbol') }}" 
                        placeholder="e.g. SPX, ORCL"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                    >
                </div>

                <!-- Trade State -->
                <div>
                    <label for="state" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                    <select 
                        name="state" 
                        id="state"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                    >
                        <option value="">All Positions</option>
                        <option value="open" {{ request('state') == 'open' ? 'selected' : '' }}>Open</option>
                        <option value="closed" {{ request('state') == 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>

                <!-- Asset Type -->
                <div>
                    <label for="asset_type" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Asset Type</label>
                    <select 
                        name="asset_type" 
                        id="asset_type"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                    >
                        <option value="">All Types</option>
                        <option value="OPT" {{ request('asset_type') == 'OPT' ? 'selected' : '' }}>Options</option>
                        <option value="STK" {{ request('asset_type') == 'STK' ? 'selected' : '' }}>Stocks</option>
                        <option value="FUT" {{ request('asset_type') == 'FUT' ? 'selected' : '' }}>Futures</option>
                    </select>
                </div>

                <!-- Option Type -->
                <div>
                    <label for="put_call" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Option Type</label>
                    <select 
                        name="put_call" 
                        id="put_call"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                    >
                        <option value="">All Options</option>
                        <option value="CALL" {{ request('put_call') == 'CALL' ? 'selected' : '' }}>CALL</option>
                        <option value="PUT" {{ request('put_call') == 'PUT' ? 'selected' : '' }}>PUT</option>
                        <option value="STRATEGY" {{ request('put_call') == 'STRATEGY' ? 'selected' : '' }}>Strategy</option>
                    </select>
                </div>

                <!-- P&L Filter -->
                <div>
                    <label for="pnl_filter" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">P&L</label>
                    <select 
                        name="pnl_filter" 
                        id="pnl_filter"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                    >
                        <option value="">All Trades</option>
                        <option value="winner" {{ request('pnl_filter') == 'winner' ? 'selected' : '' }}>Winners</option>
                        <option value="loser" {{ request('pnl_filter') == 'loser' ? 'selected' : '' }}>Losers</option>
                        <option value="breakeven" {{ request('pnl_filter') == 'breakeven' ? 'selected' : '' }}>Break-even</option>
                    </select>
                </div>

                <!-- Tag Filter -->
                <div>
                    <label for="tag" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Tag</label>
                    <select 
                        name="tag" 
                        id="tag"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                    >
                        <option value="">All Tags</option>
                        @foreach($userTags as $tag)
                            <option value="{{ $tag->id }}" {{ request('tag') == $tag->id ? 'selected' : '' }}>
                                {{ $tag->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Opened Date From -->
                <div>
                    <label for="opened_from" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Opened From</label>
                    <input 
                        type="date" 
                        name="opened_from" 
                        id="opened_from" 
                        value="{{ request('opened_from') }}"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                    >
                </div>

                <!-- Opened Date To -->
                <div>
                    <label for="opened_to" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Opened To</label>
                    <input 
                        type="date" 
                        name="opened_to" 
                        id="opened_to" 
                        value="{{ request('opened_to') }}"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                    >
                </div>

                <!-- Closed Date From -->
                <div>
                    <label for="closed_from" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Closed From</label>
                    <input 
                        type="date" 
                        name="closed_from" 
                        id="closed_from" 
                        value="{{ request('closed_from') }}"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                    >
                </div>

                <!-- Closed Date To -->
                <div>
                    <label for="closed_to" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Closed To</label>
                    <input 
                        type="date" 
                        name="closed_to" 
                        id="closed_to" 
                        value="{{ request('closed_to') }}"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                    >
                </div>

                <!-- Sort By -->
                <div>
                    <label for="sort_by" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Sort By</label>
                    <select 
                        name="sort_by" 
                        id="sort_by"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                    >
                        <option value="close_datetime" {{ request('sort_by', 'close_datetime') == 'close_datetime' ? 'selected' : '' }}>Closed Date</option>
                        <option value="open_datetime" {{ request('sort_by') == 'open_datetime' ? 'selected' : '' }}>Opened Date</option>
                        <option value="symbol" {{ request('sort_by') == 'symbol' ? 'selected' : '' }}>Symbol</option>
                        <option value="pnl" {{ request('sort_by') == 'pnl' ? 'selected' : '' }}>P&L</option>
                    </select>
                </div>

                <!-- Order -->
                <div>
                    <label for="sort_order" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Order</label>
                    <select 
                        name="sort_order" 
                        id="sort_order"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                    >
                        <option value="desc" {{ request('sort_order', 'desc') == 'desc' ? 'selected' : '' }}>Descending</option>
                        <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Ascending</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-end gap-2 col-span-1 sm:col-span-2">
                    <button 
                        type="submit"
                        class="flex-1 sm:flex-none px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium rounded-md transition-colors whitespace-nowrap"
                    >
                        Apply Filters
                    </button>
                    <a 
                        href="{{ route('trades') }}"
                        class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-md transition-colors whitespace-nowrap"
                    >
                        Clear
                    </a>
                </div>
            </div>

            <!-- Preserve per_page in filters -->
            <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
        </form>
    </div>
</div>

<!-- Trade History Header + Per Page -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4 my-4">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Trade History</h3>
    <div class="flex items-center space-x-2">
        <label for="per-page" class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">Show:</label>
        <select id="per-page" class="px-2 sm:px-3 py-1.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
            <option value="5"  {{ request('per_page', 10) == 5   ? 'selected' : '' }}>5</option>
            <option value="10" {{ request('per_page', 10) == 10  ? 'selected' : '' }}>10</option>
            <option value="20" {{ request('per_page') == 20  ? 'selected' : '' }}>20</option>
            <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>All</option>
        </select>
        <span class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 hidden sm:inline whitespace-nowrap">days</span>
    </div>
</div>

<!-- Day Group Accordion Cards -->
<div class="space-y-3" id="trades-list">

    @forelse($groupedPositions as $dateKey => $dayPositions)
    @php
        $isOpen          = $dateKey === 'open';
        $groupId         = 'grp-' . str_replace('-', '', $dateKey);
        $totalPnl        = $dayPositions->whereNotNull('realized_pnl')->sum('realized_pnl');
        $dayStrategies   = $dayPositions->groupBy(fn($p) => in_array($p->brokerage_order_id, $multiLegOrderIds, true)
            ? 'order:' . $p->brokerage_order_id
            : 'position:' . $p->id);
        $closedStrategies = $dayStrategies->map(fn($legs) => $legs->whereNotNull('realized_pnl')->sum('realized_pnl'))
            ->filter(fn($pnl, $key) => $dayStrategies[$key]->every(fn($leg) => $leg->realized_pnl !== null));
        $winCount        = $closedStrategies->filter(fn($pnl) => $pnl > 0)->count();
        $lossCount       = $closedStrategies->filter(fn($pnl) => $pnl < 0)->count();
        $tradeCount      = $dayStrategies->count();
        $totalVolume     = $dayPositions->sum(fn($p) => abs((float) $p->quantity));
        $grossProfit     = $closedStrategies->filter(fn($pnl) => $pnl > 0)->sum();
        $grossLoss       = $closedStrategies->filter(fn($pnl) => $pnl < 0)->sum();
        $winRate         = $tradeCount > 0 ? round(($winCount / $tradeCount) * 100, 2) : 0;
        $dayCommissions  = $isOpen ? 0 : (float)($commissionsByDate[$dateKey] ?? 0);
        $grossPnl        = $totalPnl + $dayCommissions;

        if ($grossLoss != 0) {
            $profitFactor = round(abs($grossProfit / $grossLoss), 2);
        } elseif ($grossProfit > 0) {
            $profitFactor = '∞';
        } else {
            $profitFactor = '--';
        }

        $sortedForChart = $dayStrategies
            ->filter(fn($legs) => $legs->every(fn($leg) => $leg->realized_pnl !== null))
            ->map(fn($legs) => (object) [
                'open_datetime' => $legs->min('open_datetime'),
                'realized_pnl' => $legs->sum('realized_pnl'),
            ])
            ->sortBy('open_datetime');
        $chartPnl = [0]; $chartLabels = ['']; $cumPnl = 0;
        foreach ($sortedForChart as $cp) {
            $cumPnl += (float) $cp->realized_pnl;
            $chartPnl[]    = round($cumPnl, 2);
            $chartLabels[] = $cp->open_datetime->format('H:i');
        }
        $chartPositive    = $totalPnl >= 0;
        $pnlColorClass    = $totalPnl > 0 ? 'text-green-600' : ($totalPnl < 0 ? 'text-red-600' : 'text-gray-500');
        $dotColorClass    = $totalPnl > 0 ? 'bg-green-500'   : ($totalPnl < 0 ? 'bg-red-500'   : 'bg-gray-400');
        $borderColorClass = $isOpen ? 'border-l-amber-400'   : ($totalPnl > 0 ? 'border-l-green-400' : ($totalPnl < 0 ? 'border-l-red-400' : 'border-l-gray-300'));
    @endphp

    <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm">

        {{-- Day Header --}}
        <div class="flex items-center justify-between pl-0 pr-5 py-0 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors select-none"
             onclick="toggleDay('{{ $groupId }}')">

            <div class="flex items-center gap-3 flex-1 min-w-0 border-l-4 {{ $borderColorClass }} pl-4 py-4">
                <svg data-chevron="{{ $groupId }}"
                     class="w-4 h-4 text-gray-400 flex-shrink-0 transition-transform duration-200"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                </svg>
                @if($isOpen)
                    <span class="font-semibold text-gray-800 dark:text-gray-200 text-sm">Open Positions</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 px-2 py-0.5 rounded-full shadow-sm">
                        {{ $tradeCount }} {{ $tradeCount === 1 ? 'trade' : 'trades' }}
                    </span>
                @else
                    <span class="font-semibold text-gray-900 dark:text-white text-sm tracking-wide">
                        {{ \Carbon\Carbon::parse($dateKey)->format('D, M d, Y') }}
                    </span>
                    <span class="w-2 h-2 rounded-full flex-shrink-0 {{ $dotColorClass }}"></span>
                    <span class="font-bold text-sm tabular-nums {{ $pnlColorClass }}">
                        Net P&amp;L&nbsp;{{ $totalPnl >= 0 ? '+' : '-' }}${{ number_format(abs($totalPnl), 2) }}
                    </span>
                    <span class="text-xs text-gray-400 dark:text-gray-500 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 px-2 py-0.5 rounded-full shadow-sm hidden sm:inline">
                        {{ $tradeCount }} {{ $tradeCount === 1 ? 'trade' : 'trades' }}
                    </span>
                    @if($winCount > 0)
                        <span class="hidden sm:inline text-xs font-semibold text-green-700 bg-green-100 border border-green-200 px-2 py-0.5 rounded-full">{{ $winCount }}W</span>
                    @endif
                    @if($lossCount > 0)
                        <span class="hidden sm:inline text-xs font-semibold text-red-700 bg-red-100 border border-red-200 px-2 py-0.5 rounded-full">{{ $lossCount }}L</span>
                    @endif
                @endif
            </div>

            <div class="flex items-center gap-2 flex-shrink-0 ml-3" onclick="event.stopPropagation()">
                @if(!$isOpen)
                    @if(isset($diaryEntryByDate[$dateKey]))
                        <a href="{{ route('diary.show', $diaryEntryByDate[$dateKey]->id) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 hover:border-gray-400 transition-colors shadow-sm whitespace-nowrap">
                            <svg class="w-3.5 h-3.5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                            View note
                        </a>
                    @else
                        <a href="{{ route('diary') }}?date={{ $dateKey }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-white border border-dashed border-gray-300 rounded-lg text-gray-400 hover:border-orange-400 hover:text-orange-500 transition-colors shadow-sm whitespace-nowrap">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add note
                        </a>
                    @endif
                @endif
            </div>
        </div>

        {{-- Day Body --}}
        <div class="day-body" id="{{ $groupId }}-body">

            @if(!$isOpen)
            {{-- Stats + Mini Chart --}}
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 px-5 py-4 bg-white dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-700">
                <div class="flex-shrink-0 w-full sm:w-44" style="height: 72px;">
                    <canvas id="chart-{{ $groupId }}"
                            data-pnl="{{ json_encode($chartPnl) }}"
                            data-labels="{{ json_encode($chartLabels) }}"
                            data-positive="{{ $chartPositive ? 'true' : 'false' }}"></canvas>
                </div>
                <div class="flex-1 grid grid-cols-2 sm:grid-cols-4 gap-x-8 gap-y-3 w-full">
                    <div><p class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">Total Trades</p><p class="text-xl font-bold text-gray-900 dark:text-white">{{ $tradeCount }}</p></div>
                    <div>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">Gross P&amp;L</p>
                        <p class="text-xl font-bold {{ $grossPnl >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $grossPnl >= 0 ? '' : '-' }}${{ number_format(abs($grossPnl), 0) }}
                        </p>
                    </div>
                    <div><p class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">Winners / Losers</p><p class="text-xl font-bold text-gray-900 dark:text-white">{{ $winCount }} / {{ $lossCount }}</p></div>
                    <div><p class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">Commissions</p><p class="text-xl font-bold text-gray-700 dark:text-gray-300">${{ number_format($dayCommissions, 2) }}</p></div>
                    <div><p class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">Win Rate</p><p class="text-xl font-bold text-gray-900 dark:text-white">{{ $winRate }}%</p></div>
                    <div><p class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">Volume</p><p class="text-xl font-bold text-gray-900 dark:text-white">{{ number_format($totalVolume, 0) }}</p></div>
                    <div>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">Profit Factor</p>
                        <p class="text-xl font-bold {{ is_numeric($profitFactor) && $profitFactor >= 1 ? 'text-green-600' : (is_numeric($profitFactor) ? 'text-red-600' : 'text-gray-400') }}">
                            {{ is_numeric($profitFactor) ? number_format($profitFactor, 2) : $profitFactor }}
                        </p>
                    </div>
                </div>
            </div>
            @endif

            {{-- Trade Rows Table --}}
            <div class="overflow-x-auto hidden" id="{{ $groupId }}-trades">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700 border-b border-gray-100 dark:border-gray-600">
                            <th class="px-3 py-2.5 w-8">
                                <input type="checkbox"
                                       class="day-select-all w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500 cursor-pointer"
                                       data-group="{{ $groupId }}"
                                       onclick="event.stopPropagation()"
                                       onchange="selectAllInDay(this, '{{ $groupId }}')">
                            </th>
                            <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider whitespace-nowrap">Open time</th>
                            <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider whitespace-nowrap">Close time</th>
                            <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider"></th>
                            <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Position</th>
                            <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider whitespace-nowrap">Net P&amp;L</th>
                            <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider whitespace-nowrap">Net ROI</th>
                            <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider whitespace-nowrap">Realized R-Multiple</th>
                            <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Strategy</th>
                            <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Tags</th>
                            <th class="px-2 py-2.5"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-50 dark:divide-gray-700">
                        @foreach($dayStrategies as $strategyPositions)
                        @php
                            $position = $strategyPositions->first();
                            $brokerageOrderId = $position->brokerage_order_id;
                            $isMultiLegStrategy = in_array($brokerageOrderId, $multiLegOrderIds, true);
                            $strategyPnl = $strategyPositions->whereNotNull('realized_pnl')->sum('realized_pnl');
                            $strategyClosed = $strategyPositions->every(fn($leg) => $leg->realized_pnl !== null);
                            $strategyOpen = $strategyPositions->contains(fn($leg) => $leg->isOpen());
                            $strategySymbols = $strategyPositions
                                ->map(fn($leg) => strtoupper($leg->instrument->underlying_symbol ?: $leg->instrument->symbol))
                                ->unique()
                                ->values();
                            $strategyCostBasis = $strategyPositions->sum(fn($leg) => abs((float) $leg->cost_basis));
                            $strategyTags = $strategyPositions->flatMap(fn($leg) => $leg->tags)->unique('id');
                            $strategyTypeLabels = $strategyPositions->flatMap(function($leg) {
                                return match ($leg->instrument->asset_type) {
                                    'OPT' => ['OPT', $leg->instrument->put_call === 'C' ? 'CALL' : 'PUT'],
                                    'STK' => ['STK'],
                                    'FUT' => ['FUT'],
                                    default => [$leg->instrument->asset_type],
                                };
                            });
                            if ($isMultiLegStrategy) {
                                $strategyTypeLabels->push('SPREAD');
                            }
                            $strategyTypeLabels = $strategyTypeLabels->unique()->values();
                            $positionDescriptions = $strategyPositions
                                ->groupBy('instrument_id')
                                ->map(function($legs) {
                                    $leg = $legs->first();
                                    $instrument = $leg->instrument;
                                    $quantity = (float) $legs->sum(fn($item) => (float) $item->quantity);
                                    $quantityLabel = rtrim(rtrim(number_format(abs($quantity), 2, '.', ''), '0'), '.');
                                    $signedQuantity = ($quantity > 0 ? '+' : ($quantity < 0 ? '-' : '')) . $quantityLabel;

                                    if ($instrument->asset_type === 'OPT') {
                                        $strike = rtrim(rtrim(number_format((float) $instrument->strike, 2, '.', ''), '0'), '.');
                                        return [
                                            'label' => $signedQuantity . ' ' . $strike . ' ' . ($instrument->put_call === 'C' ? 'Call' : 'Put'),
                                            'color' => $quantity < 0 ? 'text-red-600' : 'text-green-600',
                                        ];
                                    }

                                    if ($instrument->asset_type === 'STK') {
                                        return [
                                            'label' => $signedQuantity . ' shares',
                                            'color' => 'text-gray-700 dark:text-gray-300',
                                        ];
                                    }

                                    return [
                                        'label' => $signedQuantity . ' ' . $instrument->symbol,
                                        'color' => 'text-gray-700 dark:text-gray-300',
                                    ];
                                })
                                ->values();
                            $optionLegQuantities = $strategyPositions
                                ->filter(fn($leg) => $leg->instrument->asset_type === 'OPT')
                                ->groupBy('instrument_id')
                                ->map(fn($legs) => abs((float) $legs->sum(fn($leg) => (float) $leg->quantity)));
                            $optionContractCount = $optionLegQuantities->isNotEmpty()
                                ? $optionLegQuantities->min()
                                : null;
                            $optionContractLabel = $optionContractCount !== null
                                ? rtrim(rtrim(number_format($optionContractCount, 2, '.', ''), '0'), '.') .
                                    ' ' . ($optionContractCount === 1.0 ? 'contract' : 'contracts')
                                : null;
                            $strategyName = $isMultiLegStrategy
                                ? ($strategyNames->get($brokerageOrderId) ?? 'Multi-leg Option Strategy')
                                : null;
                            $strategyCloseTime = $strategyPositions->whereNotNull('close_datetime')->max('close_datetime');
                            $strategyOpenTime = $strategyPositions->min('open_datetime');
                            $underlyingSymbol = $position->instrument->underlying_symbol ?: $position->instrument->symbol;
                            $netRoi = null;
                            if ($strategyClosed && $strategyCostBasis != 0) {
                                $netRoi = round(((float)$strategyPnl / $strategyCostBasis) * 100, 2);
                            }
                            $sym = strtoupper($underlyingSymbol);
                            $bhash = array_sum(array_map('ord', str_split($sym)));
                            $badgeOptions = [
                                ['bg-blue-100', 'text-blue-700'], ['bg-purple-100', 'text-purple-700'],
                                ['bg-emerald-100', 'text-emerald-700'], ['bg-orange-100', 'text-orange-700'],
                                ['bg-cyan-100', 'text-cyan-700'], ['bg-pink-100', 'text-pink-700'],
                                ['bg-indigo-100', 'text-indigo-700'], ['bg-amber-100', 'text-amber-700'],
                            ];
                            $badge = $badgeOptions[$bhash % count($badgeOptions)];
                            $rowBg = $strategyClosed
                                ? ($strategyPnl > 0 ? 'hover:bg-green-50/50' : ($strategyPnl < 0 ? 'hover:bg-red-50/50' : 'hover:bg-gray-50'))
                                : 'hover:bg-amber-50/40';
                            $strategyRoute = $isMultiLegStrategy
                                ? route('trades.strategy', ['brokerageOrderId' => $brokerageOrderId])
                                : route('trades.show', $position);
                        @endphp
                        <tr class="group cursor-pointer transition-colors {{ $rowBg }}"
                            data-position-ids="{{ $strategyPositions->pluck('id')->implode(',') }}"
                            onclick="window.location='{{ $strategyRoute }}'">
                            <td class="px-3 py-3.5 w-8" onclick="event.stopPropagation()">
                                <input type="checkbox"
                                       class="trade-checkbox w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500 cursor-pointer"
                                       data-position-ids="{{ $strategyPositions->pluck('id')->implode(',') }}"
                                       data-group="{{ $groupId }}"
                                       onchange="toggleRowSelection(this)">
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="text-sm text-gray-700 dark:text-gray-300 tabular-nums">{{ $strategyOpenTime->format('H:i:s') }}</span>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if($strategyCloseTime)
                                    <span class="text-sm text-gray-700 dark:text-gray-300 tabular-nums">{{ $strategyCloseTime->format('H:i:s') }}</span>
                                @else
                                    <span class="text-gray-300 dark:text-gray-600">--</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $badge[0] }} {{ $badge[1] }}">{{ $strategySymbols->implode(' + ') }}</span>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-sm font-medium text-gray-700 dark:text-gray-300">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($strategyTypeLabels as $typeLabel)
                                        <span class="rounded bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 text-xs">{{ $typeLabel }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-white">
                                <div class="flex flex-col gap-1">
                                    @if($optionContractLabel)
                                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $optionContractLabel }}</span>
                                    @endif
                                    @foreach($positionDescriptions as $description)
                                        <span class="{{ $description['color'] }}">{{ $description['label'] }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if($strategyClosed)
                                    <span class="font-bold text-sm tabular-nums {{ $strategyPnl > 0 ? 'text-green-600' : ($strategyPnl < 0 ? 'text-red-600' : 'text-gray-500') }}">
                                        {{ $strategyPnl > 0 ? '+' : ($strategyPnl < 0 ? '-' : '') }}${{ number_format(abs((float)$strategyPnl), 2) }}
                                    </span>
                                @elseif($strategyOpen)
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200">
                                        <span class="w-1.5 h-1.5 bg-amber-500 rounded-full animate-pulse"></span>{{ $strategyPnl >= 0 ? '+' : '-' }}${{ number_format(abs((float)$strategyPnl), 2) }} · Open
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400 dark:text-gray-500 italic">pending</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-sm">
                                @if($netRoi !== null)
                                    <span class="tabular-nums {{ $netRoi > 0 ? 'text-green-600' : ($netRoi < 0 ? 'text-red-600' : 'text-gray-500') }}">({{ number_format(abs($netRoi), 2) }}%)</span>
                                @else
                                    <span class="text-gray-300 dark:text-gray-600">--</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-sm text-gray-300 dark:text-gray-600 text-center">--</td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-sm">
                                @if($isMultiLegStrategy)
                                    <span class="text-xs font-medium text-indigo-600 dark:text-indigo-400">{{ $strategyName }}</span>
                                @else
                                    <span class="text-gray-300 dark:text-gray-600">--</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="flex flex-wrap items-center gap-1">
                                    @forelse($strategyTags as $tag)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium text-white" style="background-color: {{ $tag->color }}">{{ $tag->name }}</span>
                                    @empty
                                        <span class="w-6 h-6 flex items-center justify-center rounded-full border border-dashed border-gray-300 text-gray-400 hover:border-orange-400 hover:text-orange-500 transition-colors cursor-pointer" title="Add tag">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                        </span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-3 py-3.5 whitespace-nowrap text-right">
                                <svg class="w-4 h-4 text-gray-300 group-hover:text-orange-500 transition-colors inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                </svg>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>{{-- end overflow-x-auto --}}

        </div>{{-- end .day-body --}}
    </div>{{-- end day card --}}

    @empty
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
            <svg class="w-16 h-16 text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <p class="text-gray-500 dark:text-gray-400 text-lg font-medium">No positions found</p>
            <p class="text-gray-400 dark:text-gray-500 text-sm mt-2">Click "Add Trade" to import your first trades</p>
        </div>
    </div>
    @endforelse

</div>{{-- end #trades-list --}}

<!-- Pagination -->
<div class="mt-4 px-2 py-4 flex justify-between items-center">
    <div class="text-sm text-gray-600 dark:text-gray-400">
        @if(request('per_page') === 'all')
            All {{ $totalDays }} trading {{ $totalDays === 1 ? 'day' : 'days' }}
        @else
            {{ $groupedPositions->count() }} of {{ $totalDays }} trading {{ $totalDays === 1 ? 'day' : 'days' }}
        @endif
    </div>
    <div class="flex items-center space-x-4">
        @if($daysPaginator->hasPages() && request('per_page') !== 'all')
            <div class="text-sm text-gray-600 dark:text-gray-400">Page {{ $daysPaginator->currentPage() }} of {{ $daysPaginator->lastPage() }}</div>
            <div class="flex space-x-1">
                @if($daysPaginator->onFirstPage())
                    <span class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 rounded-lg text-sm cursor-not-allowed">Previous</span>
                @else
                    <a href="{{ $daysPaginator->previousPageUrl() }}" class="px-3 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">Previous</a>
                @endif
                @php
                    $currentPage = $daysPaginator->currentPage();
                    $lastPage    = $daysPaginator->lastPage();
                    $start       = max(1, $currentPage - 2);
                    $end         = min($lastPage, $currentPage + 2);
                @endphp
                @if($start > 1)
                    <a href="{{ $daysPaginator->url(1) }}" class="px-3 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">1</a>
                    @if($start > 2)<span class="px-3 py-1.5 text-gray-400 dark:text-gray-500">...</span>@endif
                @endif
                @for($page = $start; $page <= $end; $page++)
                    @if($page == $currentPage)
                        <span class="px-3 py-1.5 bg-orange-600 text-white rounded-lg text-sm font-medium">{{ $page }}</span>
                    @else
                        <a href="{{ $daysPaginator->url($page) }}" class="px-3 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">{{ $page }}</a>
                    @endif
                @endfor
                @if($end < $lastPage)
                    @if($end < $lastPage - 1)<span class="px-3 py-1.5 text-gray-400 dark:text-gray-500">...</span>@endif
                    <a href="{{ $daysPaginator->url($lastPage) }}" class="px-3 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">{{ $lastPage }}</a>
                @endif
                @if($daysPaginator->hasMorePages())
                    <a href="{{ $daysPaginator->nextPageUrl() }}" class="px-3 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">Next</a>
                @else
                    <span class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 rounded-lg text-sm cursor-not-allowed">Next</span>
                @endif
            </div>
        @endif
    </div>
</div>









<!-- Bulk Tag Bar -->
<div id="bulk-tag-bar"
     class="hidden fixed bottom-0 left-0 right-0 z-40 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 shadow-2xl">
    <div class="max-w-full px-4 md:px-6 py-3 flex flex-col sm:flex-row items-start sm:items-center gap-3 flex-wrap">
        <div class="flex items-center gap-2 flex-shrink-0">
            <span class="text-sm font-semibold text-gray-900 dark:text-white" id="bulk-count-label">0 trades selected</span>
            <button onclick="clearBulkSelection()" class="text-xs text-gray-400 dark:text-gray-500 hover:text-red-500 underline transition-colors">Clear</button>
        </div>
        <div class="hidden sm:block w-px h-6 bg-gray-200 dark:bg-gray-600 flex-shrink-0"></div>
        <div class="flex flex-wrap items-center gap-1.5 flex-1" id="bulk-tag-chips">
            @forelse($userTags as $tag)
                <button type="button"
                        data-tag-id="{{ $tag->id }}"
                        onclick="toggleBulkTag(this)"
                        class="bulk-tag-chip px-2.5 py-1 rounded-full text-xs font-medium border-2 transition-all duration-150 opacity-50 hover:opacity-80"
                        style="border-color:{{ $tag->color }};color:{{ $tag->color }};background-color:transparent;">
                    {{ $tag->name }}
                </button>
            @empty
                <span class="text-xs text-gray-400 dark:text-gray-500 italic">No tags — create them in Settings first.</span>
            @endforelse
        </div>
        <button onclick="applyBulkTags()"
                id="bulk-apply-btn"
                disabled
                class="flex-shrink-0 inline-flex items-center gap-1.5 px-4 py-2 bg-orange-600 hover:bg-orange-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a2 2 0 012-2z"/>
            </svg>
            Apply Tags
        </button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Auto-dismiss flash messages after 5 seconds
    document.querySelectorAll('.flash-message').forEach(msg => {
        setTimeout(() => {
            msg.style.transition = 'opacity 0.5s ease-out';
            msg.style.opacity = '0';
            setTimeout(() => msg.remove(), 500);
        }, 5000);
    });

    // Auto-show filters if active
    const hasActiveFilters = {{ request()->except(['page', 'per_page']) ? 'true' : 'false' }};
    if (hasActiveFilters) {
        const filterForm = document.getElementById('filter-form');
        if (filterForm) {
            filterForm.classList.remove('hidden');
            const chevron = document.getElementById('filter-chevron');
            if (chevron) chevron.style.transform = 'rotate(180deg)';
        }
    }

    // Mini charts
    document.querySelectorAll('canvas[data-pnl]').forEach(canvas => {
        const pnlData   = JSON.parse(canvas.dataset.pnl   || '[]');
        const labels    = JSON.parse(canvas.dataset.labels || '[]');
        const isPos     = canvas.dataset.positive === 'true';
        const stroke    = isPos ? '#22c55e' : '#ef4444';
        const fill      = isPos ? 'rgba(34,197,94,0.12)' : 'rgba(239,68,68,0.12)';

        new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    data: pnlData,
                    borderColor: stroke,
                    backgroundColor: fill,
                    borderWidth: 1.5,
                    fill: true,
                    tension: 0.35,
                    pointRadius: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                scales: { x: { display: false }, y: { display: false } },
                plugins: { legend: { display: false }, tooltip: { enabled: false } }
            }
        });
    });

    // Per-page select sync
    const perPageSelect = document.getElementById('per-page');
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function () {
            const form = document.getElementById('filter-form');
            if (form) {
                const hidden = form.querySelector('input[name="per_page"]');
                if (hidden) hidden.value = this.value;
                form.submit();
            } else {
                changePerPage(this.value);
            }
        });
    }
});

function toggleFilters() {
    const filterForm = document.getElementById('filter-form');
    const chevron    = document.getElementById('filter-chevron');
    if (filterForm.classList.contains('hidden')) {
        filterForm.classList.remove('hidden');
        chevron.style.transform = 'rotate(180deg)';
    } else {
        filterForm.classList.add('hidden');
        chevron.style.transform = 'rotate(0deg)';
    }
}

function changePerPage(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', value);
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

function toggleDay(groupId) {
    const trades  = document.getElementById(groupId + '-trades');
    const chevron = document.querySelector('[data-chevron="' + groupId + '"]');
    if (!trades) return;
    const collapsed = trades.classList.contains('hidden');
    trades.classList.toggle('hidden', !collapsed);
    if (chevron) chevron.style.transform = collapsed ? 'rotate(90deg)' : 'rotate(0deg)';
}

let deleteCallback = null;

function showConfirmModal(title, message, callback) {
    document.getElementById('modal-title').textContent   = title;
    document.getElementById('modal-message').textContent = message;
    document.getElementById('confirmation-modal').classList.remove('hidden');
    deleteCallback = callback;
}

function closeConfirmModal() {
    document.getElementById('confirmation-modal').classList.add('hidden');
    deleteCallback = null;
}

function confirmDelete() {
    if (deleteCallback) deleteCallback();
    closeConfirmModal();
}

function deletePosition(positionId) {
    showConfirmModal(
        'Confirm Deletion',
        'Are you sure you want to delete this position? This action cannot be undone.',
        function () {
            const form       = document.createElement('form');
            form.method      = 'POST';
            form.action      = '/trades/' + positionId;

            const csrf       = document.createElement('input');
            csrf.type        = 'hidden';
            csrf.name        = '_token';
            csrf.value       = '{{ csrf_token() }}';
            form.appendChild(csrf);

            const method     = document.createElement('input');
            method.type      = 'hidden';
            method.name      = '_method';
            method.value     = 'DELETE';
            form.appendChild(method);

            document.body.appendChild(form);
            form.submit();
        }
    );
}

// ── Bulk Tagging ─────────────────────────────────────────────────────────────
const selectedPositionIds = new Set();
const selectedTagIds      = new Set();

function toggleRowSelection(checkbox) {
    const ids = (checkbox.dataset.positionIds || checkbox.dataset.positionId || '').split(',').filter(Boolean);
    const row = checkbox.closest('tr');
    if (checkbox.checked) {
        ids.forEach(id => selectedPositionIds.add(id));
        row.classList.add('bg-orange-50/40', 'ring-1', 'ring-orange-300', 'ring-inset');
    } else {
        ids.forEach(id => selectedPositionIds.delete(id));
        row.classList.remove('bg-orange-50/40', 'ring-1', 'ring-orange-300', 'ring-inset');
    }
    syncDayCheckbox(checkbox.dataset.group);
    updateBulkBar();
}

function selectAllInDay(header, groupId) {
    document.querySelectorAll(`.trade-checkbox[data-group="${groupId}"]`).forEach(cb => {
        cb.checked = header.checked;
        toggleRowSelection(cb);
    });
}

function syncDayCheckbox(groupId) {
    const all     = document.querySelectorAll(`.trade-checkbox[data-group="${groupId}"]`);
    const checked = document.querySelectorAll(`.trade-checkbox[data-group="${groupId}"]:checked`);
    const header  = document.querySelector(`.day-select-all[data-group="${groupId}"]`);
    if (!header) return;
    if (checked.length === 0)               { header.checked = false; header.indeterminate = false; }
    else if (checked.length === all.length) { header.checked = true;  header.indeterminate = false; }
    else                                    { header.checked = false; header.indeterminate = true;  }
}

function toggleBulkTag(btn) {
    const id = btn.dataset.tagId;
    if (selectedTagIds.has(id)) {
        selectedTagIds.delete(id);
        btn.style.backgroundColor = 'transparent';
        btn.style.color           = btn.style.borderColor;
        btn.classList.replace('opacity-100', 'opacity-50');
    } else {
        selectedTagIds.add(id);
        btn.style.backgroundColor = btn.style.borderColor;
        btn.style.color           = '#ffffff';
        btn.classList.replace('opacity-50', 'opacity-100');
    }
    updateApplyButton();
}

function updateApplyButton() {
    document.getElementById('bulk-apply-btn').disabled =
        selectedPositionIds.size === 0 || selectedTagIds.size === 0;
}

function updateBulkBar() {
    const bar   = document.getElementById('bulk-tag-bar');
    const count = selectedPositionIds.size;
    if (count > 0) {
        bar.classList.remove('hidden');
        document.getElementById('bulk-count-label').textContent =
            count + ' trade' + (count !== 1 ? 's' : '') + ' selected';
        document.querySelector('.max-w-full.overflow-hidden').style.paddingBottom = '72px';
    } else {
        bar.classList.add('hidden');
        document.querySelector('.max-w-full.overflow-hidden').style.paddingBottom = '';
        resetTagChips();
    }
    updateApplyButton();
}

function resetTagChips() {
    selectedTagIds.clear();
    document.querySelectorAll('.bulk-tag-chip').forEach(btn => {
        btn.style.backgroundColor = 'transparent';
        btn.style.color           = btn.style.borderColor;
        btn.classList.remove('opacity-100');
        btn.classList.add('opacity-50');
    });
}

function clearBulkSelection() {
    document.querySelectorAll('.trade-checkbox').forEach(cb => { cb.checked = false; });
    document.querySelectorAll('.day-select-all').forEach(cb => {
        cb.checked = false; cb.indeterminate = false;
    });
    document.querySelectorAll('tr[data-position-id]').forEach(tr =>
        tr.classList.remove('bg-orange-50/40', 'ring-1', 'ring-orange-300', 'ring-inset'));
    selectedPositionIds.clear();
    updateBulkBar();
}

async function applyBulkTags() {
    if (selectedPositionIds.size === 0 || selectedTagIds.size === 0) return;
    const btn = document.getElementById('bulk-apply-btn');
    btn.disabled    = true;
    btn.textContent = 'Applying…';

    try {
        const res  = await fetch('{{ route("trades.bulk-tag") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept':       'application/json',
            },
            body: JSON.stringify({
                position_ids: [...selectedPositionIds].map(Number),
                tag_ids:      [...selectedTagIds].map(Number),
            }),
        });
        const data = await res.json();
        if (data.success) {
            clearBulkSelection();
            setTimeout(() => window.location.reload(), 600);
        } else {
            alert(data.message || 'An error occurred.');
            btn.disabled    = false;
            btn.textContent = 'Apply Tags';
        }
    } catch {
        alert('Network error. Please try again.');
        btn.disabled    = false;
        btn.textContent = 'Apply Tags';
    }
}
</script>
</div>
@endsection
