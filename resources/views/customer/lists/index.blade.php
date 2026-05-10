@extends('layouts.customer')

@section('title', 'Audience')

@section('page-title', 'Audience')

@section('page-actions')
    <div class="flex w-full flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-end">
        <a href="{{ route('customer.segments.create') }}"
           class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 w-full sm:w-auto">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Create Segment
        </a>
        <a href="{{ route('customer.lists.create') }}"
           class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-primary-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-700 w-full sm:w-auto">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Create List
        </a>
    </div>
@endsection

@section('content')
@php
    $statCards = [
        [
            'label' => 'Total Contacts',
            'value' => number_format($audienceStats['total_contacts']),
            'delta' => $audienceStats['total_contacts_delta'],
            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>',
            'down_is_good' => false,
        ],
        [
            'label' => 'Active Subscribers',
            'value' => number_format($audienceStats['active_subscribers']),
            'delta' => $audienceStats['active_subscribers_delta'],
            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
            'down_is_good' => false,
        ],
        [
            'label' => 'Avg. Open Rate',
            'value' => $audienceStats['avg_open_rate'] . '%',
            'delta' => $audienceStats['avg_open_rate_delta'],
            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M21.75 9v.906a2.25 2.25 0 0 1-1.183 1.981l-6.478 3.488M2.25 9v.906a2.25 2.25 0 0 0 1.183 1.981l6.478 3.488m8.839 2.51-4.661-2.51m0 0-1.023-.55a2.25 2.25 0 0 0-2.134 0l-1.022.55m0 0-4.661 2.51m16.5 1.615a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V8.844a2.25 2.25 0 0 1 1.183-1.981l7.5-4.039a2.25 2.25 0 0 1 2.134 0l7.5 4.039a2.25 2.25 0 0 1 1.183 1.98V19.5Z"/>',
            'down_is_good' => false,
        ],
        [
            'label' => 'Unsubscribed',
            'value' => number_format($audienceStats['unsubscribed']),
            'delta' => $audienceStats['unsubscribed_delta'],
            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M22 10.5h-6m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM4 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 10.374 21c-2.331 0-4.512-.645-6.374-1.766Z"/>',
            'down_is_good' => true,
        ],
    ];

    $viewTabs = [
        ['key' => 'all',      'label' => 'All Lists & Segments', 'count' => $audienceStats['all_count']],
        ['key' => 'lists',    'label' => 'Lists',                'count' => $audienceStats['lists_count']],
        ['key' => 'segments', 'label' => 'Segments',             'count' => $audienceStats['segments_count']],
    ];

    $sortOptions = [
        'updated_at_desc' => 'Date',
        'name_asc'        => 'Name A–Z',
        'name_desc'       => 'Name Z–A',
        'subs_desc'       => 'Subscribers',
    ];
@endphp

<div class="space-y-6">

    {{-- ── Stat cards ─────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach($statCards as $card)
        @php
            $d = $card['delta'];
            $isPositive = $d !== null && $d >= 0;
            $isNegative = $d !== null && $d < 0;
            $goodColor  = $card['down_is_good'] ? ($isNegative ? 'text-emerald-600 dark:text-emerald-400' : ($isPositive ? 'text-rose-500 dark:text-rose-400' : 'text-gray-400')) : ($isPositive ? 'text-emerald-600 dark:text-emerald-400' : ($isNegative ? 'text-rose-500 dark:text-rose-400' : 'text-gray-400'));
        @endphp
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-start justify-between">
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $card['label'] }}</span>
                <svg class="h-5 w-5 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">{!! $card['icon'] !!}</svg>
            </div>
            <div class="mt-3 text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-50">{{ $card['value'] }}</div>
            <div class="mt-2 flex items-center gap-1 {{ $goodColor }}">
                @if($d !== null)
                    @if($isPositive)
                        <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/></svg>
                    @else
                        <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6 9 12.75l4.306-4.306a11.95 11.95 0 0 1 5.814 5.518l2.74 1.22m0 0-5.94 2.281m5.94-2.28-2.28-5.941"/></svg>
                    @endif
                    <span class="text-xs font-medium">{{ $isPositive ? '+' : '' }}{{ $d }}% from last month</span>
                @else
                    <span class="text-xs text-gray-400 dark:text-gray-500">from last month</span>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    {{-- ── Combined table card ─────────────────────────────────────────────────── --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">

        {{-- Filter tabs --}}
        <div class="border-b border-gray-200 px-6 dark:border-gray-700">
            <nav class="-mb-px flex gap-6" aria-label="View tabs">
                @foreach($viewTabs as $t)
                <a href="{{ request()->fullUrlWithQuery(['view' => $t['key'], 'page' => 1]) }}"
                   class="flex items-center gap-2 whitespace-nowrap border-b-2 py-4 text-sm font-medium transition {{ $viewTab === $t['key'] ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
                    {{ $t['label'] }}
                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $viewTab === $t['key'] ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">{{ $t['count'] }}</span>
                </a>
                @endforeach
            </nav>
        </div>

        {{-- Toolbar --}}
        <form method="GET" action="{{ route('customer.lists.index') }}" class="flex flex-col gap-3 border-b border-gray-200 px-6 py-4 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between" x-data>
            <input type="hidden" name="view" value="{{ $viewTab }}">
            <div class="relative w-full sm:max-w-xs">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="{{ $viewTab === 'lists' ? 'Search lists…' : ($viewTab === 'segments' ? 'Search segments…' : 'Search lists and segments…') }}"
                       class="block w-full rounded-lg border border-gray-200 bg-white py-2 pl-9 pr-3.5 text-sm text-gray-900 placeholder-gray-400 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-500">
            </div>
            <div class="flex items-center gap-2">
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button type="button" @click="open = !open"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                        Sort by: {{ $sortOptions[$sortBy] ?? 'Date' }}
                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                    </button>
                    <div x-show="open" x-cloak
                         class="absolute right-0 z-10 mt-1 w-44 rounded-lg border border-gray-200 bg-white py-1 shadow-lg dark:border-gray-700 dark:bg-gray-800">
                        @foreach($sortOptions as $val => $label)
                        <button type="submit" name="sort" value="{{ $val }}"
                                class="block w-full px-4 py-2 text-left text-sm {{ $sortBy === $val ? 'bg-primary-50 font-medium text-primary-700 dark:bg-primary-900/30 dark:text-primary-300' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                            {{ $label }}
                        </button>
                        @endforeach
                    </div>
                </div>
                <button type="button"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z"/></svg>
                    Filter
                </button>
            </div>
        </form>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px]">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/70 dark:border-gray-700 dark:bg-gray-700/30">
                        <th class="w-10 px-4 py-3 text-left">
                            <input type="checkbox" class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700">
                        </th>
                        <th class="w-10 px-2 py-3"></th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Subscribers</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Open Rate</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Click Rate</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Last Updated</th>
                        <th class="w-10 px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                    @forelse($paginator as $item)
                    <tr class="group transition hover:bg-gray-50/70 dark:hover:bg-gray-700/20">
                        <td class="px-4 py-4">
                            <input type="checkbox" class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700">
                        </td>
                        <td class="px-2 py-4">
                            @if($item['type'] === 'list')
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-500 dark:bg-blue-900/20">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                                </span>
                            @else
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-50 text-violet-500 dark:bg-violet-900/20">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z"/></svg>
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            <a href="{{ $item['url'] }}"
                               class="font-semibold text-gray-900 hover:text-primary-600 dark:text-gray-100 dark:hover:text-primary-400">
                                {{ $item['name'] }}
                            </a>
                            <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">{{ $item['subtitle'] }}</p>
                        </td>
                        <td class="px-4 py-4">
                            @if($item['type'] === 'list')
                                <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/20 dark:text-blue-400">List</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-violet-50 px-2.5 py-0.5 text-xs font-medium text-violet-700 dark:bg-violet-900/20 dark:text-violet-400">Segment</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-300">{{ number_format($item['subscribers']) }}</td>
                        <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-300">
                            {{ $item['open_rate'] > 0 ? $item['open_rate'] . '%' : '—' }}
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-300">
                            {{ $item['click_rate'] > 0 ? $item['click_rate'] . '%' : '—' }}
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-400 dark:text-gray-500">
                            @if($item['updated_at'])
                                @php $ts = $item['updated_at']; @endphp
                                {{ $ts->isToday() ? 'Today, ' . $ts->format('g:i A') : ($ts->isYesterday() ? 'Yesterday, ' . $ts->format('g:i A') : $ts->format('M j, Y')) }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                                <button type="button" @click="open = !open"
                                        class="flex h-7 w-7 items-center justify-center rounded-md text-gray-400 opacity-0 transition hover:bg-gray-100 hover:text-gray-600 group-hover:opacity-100 dark:hover:bg-gray-700 dark:hover:text-gray-300">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 3a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Zm0 5.5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Zm0 5.5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Z"/></svg>
                                </button>
                                <div x-show="open" x-cloak
                                     class="absolute right-0 z-20 mt-1 w-44 rounded-lg border border-gray-200 bg-white py-1 shadow-lg dark:border-gray-700 dark:bg-gray-800">
                                    <a href="{{ $item['url'] }}"
                                       class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700">
                                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                        View
                                    </a>
                                    @if($item['type'] === 'list')
                                    <a href="{{ route('customer.lists.settings', $item['id']) }}"
                                       class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700">
                                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                        Settings
                                    </a>
                                    <form method="POST" action="{{ route('customer.lists.destroy', $item['id']) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                onclick="return confirm('Delete this list?')"
                                                class="flex w-full items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                            Delete
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-16 text-center">
                            <div class="mx-auto max-w-sm">
                                <svg class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                                <p class="mt-3 text-sm font-medium text-gray-500 dark:text-gray-400">
                                    @if($search) No results for "{{ $search }}" @else No lists or segments yet @endif
                                </p>
                                @unless($search)
                                <a href="{{ route('customer.lists.create') }}"
                                   class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                    Create your first list
                                </a>
                                @endunless
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination footer --}}
        @if($paginator->total() > 0)
        <div class="flex flex-col gap-3 border-t border-gray-200 px-6 py-4 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Showing {{ $paginator->firstItem() ?? 0 }} to {{ $paginator->lastItem() ?? 0 }} of {{ number_format($paginator->total()) }} {{ $viewTab === 'segments' ? 'segments' : ($viewTab === 'lists' ? 'lists' : 'items') }}
            </p>
            <div class="flex items-center gap-2">
                @if($paginator->onFirstPage())
                    <span class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-sm font-medium text-gray-400 dark:border-gray-700 dark:bg-gray-800">Previous</span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}"
                       class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">Previous</a>
                @endif
                @if($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}"
                       class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">Next</a>
                @else
                    <span class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-sm font-medium text-gray-400 dark:border-gray-700 dark:bg-gray-800">Next</span>
                @endif
            </div>
        </div>
        @endif

    </div>{{-- end table card --}}

</div>
@endsection
