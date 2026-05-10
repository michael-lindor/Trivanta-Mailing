@extends('layouts.customer')

@section('title', 'Segments – ' . ($list->display_name ?? $list->name))

@php
    $confirmedCount = (int) ($list->confirmed_subscribers_count ?? 0);
    $lastActivity   = $list->last_subscriber_at ?? $list->updated_at ?? null;

    $sortLabels = [
        'updated_at'       => 'Last Updated',
        'name'             => 'Segment Name',
        'subscribers_count'=> 'Subscriber Count',
        'created_at'       => 'Date Created',
    ];
    $typeLabels = ['dynamic' => 'Dynamic', 'static' => 'Static'];

    $fieldLabels = [
        'email' => 'Email', 'first_name' => 'First Name', 'last_name' => 'Last Name',
        'tags' => 'Tags', 'status' => 'Status', 'subscribed_at' => 'Subscribed',
        'unsubscribed_at' => 'Unsubscribed', 'source' => 'Source',
        'confirmed_at' => 'Confirmed', 'last_opened_at' => 'Last Opened',
        'open_count' => 'Opens', 'last_clicked_at' => 'Last Clicked',
        'click_count' => 'Clicks', 'inactive_days' => 'Inactive Days',
        'campaign_received' => 'Received Campaign', 'campaign_opened' => 'Opened Campaign',
        'campaign_clicked' => 'Clicked Campaign', 'campaign_not_opened' => 'Not Opened Campaign',
        'campaign_bounced' => 'Bounced', 'custom_fields' => 'Custom Field',
    ];
    $operatorLabels = [
        'is' => '=', 'is_not' => '≠', 'contains' => 'contains',
        'not_contains' => "doesn't contain", 'greater_than' => '>',
        'less_than' => '<', 'between' => 'between', 'in_last_days' => 'in last',
        'before' => 'before', 'after' => 'after',
    ];

    function segIsDynamic(?array $rules): bool {
        return is_array($rules)
            && !empty($rules)
            && (isset($rules['conditions']) && is_array($rules['conditions']) && count($rules['conditions']) > 0);
    }

    function segRuleBadges(?array $rules, array $fieldLabels, array $operatorLabels): array {
        if (!segIsDynamic($rules)) return [];
        $conditions = $rules['conditions'] ?? [];
        $combine    = strtoupper($rules['combine_operator'] ?? 'all') === 'ANY' ? 'OR' : 'AND';
        $badges = [];
        foreach (array_slice($conditions, 0, 3) as $cond) {
            $field = $fieldLabels[$cond['field'] ?? ''] ?? ucwords(str_replace('_', ' ', (string)($cond['field'] ?? '')));
            $op    = $operatorLabels[$cond['operator'] ?? ''] ?? (string)($cond['operator'] ?? '');
            $val   = is_array($cond['value'] ?? null) ? implode(', ', (array)$cond['value']) : (string)($cond['value'] ?? '');
            $badges[] = ['label' => trim("{$field} {$op} {$val}"), 'combine' => $combine];
        }
        if (count($conditions) > 3) {
            $badges[] = ['label' => '+' . (count($conditions) - 3) . ' more', 'combine' => $combine, 'overflow' => true];
        }
        return $badges;
    }

    $iconSet = [
        'star'    => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/></svg>',
        'activity'=> '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12h3l3-9 4.5 18L18 8l2 4h1"/></svg>',
        'user-minus'=> '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M22 10H16M15.75 6A3.75 3.75 0 1 1 8.25 6a3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>',
        'clock'   => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>',
        'bag'     => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z"/></svg>',
        'gift'    => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>',
        'filter'  => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z"/></svg>',
    ];
    $iconKeys = array_keys($iconSet);
    $iconBgColors = ['bg-amber-100 text-amber-600', 'bg-blue-100 text-blue-600', 'bg-rose-100 text-rose-600', 'bg-sky-100 text-sky-600', 'bg-violet-100 text-violet-600', 'bg-emerald-100 text-emerald-600', 'bg-gray-100 text-gray-500'];
@endphp

@section('content')
<div class="space-y-5" x-data="{ showSortMenu: false }" @click.outside="showSortMenu = false">

    {{-- ── Header ─────────────────────────────────────────────────────────── --}}
    @include('customer.lists.partials.header', ['list' => $list])

    {{-- ── Tab Nav ──────────────────────────────────────────────────────────── --}}
    @include('customer.lists.partials.subnav', ['list' => $list, 'segmentsCount' => $segments->total()])

    {{-- ── Toolbar ──────────────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('customer.lists.segments.index', $list) }}" id="filterForm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            {{-- Search --}}
            <div class="relative w-full sm:max-w-xs">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search segments..."
                       class="block w-full rounded-lg border border-gray-200 bg-white py-2 pl-9 pr-4 text-sm text-gray-900 shadow-sm placeholder-gray-400 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500">
            </div>

            <div class="flex items-center gap-2">
                {{-- Type filter --}}
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button type="button" @click="open = !open"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z"/></svg>
                        Type
                        @if($typeFilter)
                            <span class="rounded-full bg-primary-100 px-1.5 py-0.5 text-xs font-semibold text-primary-700 dark:bg-primary-900/40 dark:text-primary-300">{{ $typeLabels[$typeFilter] ?? ucfirst($typeFilter) }}</span>
                        @endif
                        <svg class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                    </button>
                    <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                         class="absolute left-0 z-20 mt-1 w-40 origin-top-left rounded-xl border border-admin-border bg-white shadow-lg dark:bg-gray-800">
                        <div class="p-1">
                            @foreach(['' => 'All Types', 'dynamic' => 'Dynamic', 'static' => 'Static'] as $val => $label)
                                <button type="submit" name="type" value="{{ $val }}"
                                        class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 transition hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700 {{ $typeFilter === $val ? 'font-semibold text-primary-600 dark:text-primary-400' : '' }}">
                                    @if($typeFilter === $val)
                                        <svg class="h-3.5 w-3.5 text-primary-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
                                    @else
                                        <span class="h-3.5 w-3.5"></span>
                                    @endif
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Sort dropdown --}}
                <div class="relative">
                    <button type="button" @click="showSortMenu = !showSortMenu"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5 7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5"/></svg>
                        Sort
                    </button>
                    <div x-show="showSortMenu" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                         class="absolute right-0 z-20 mt-1 w-52 origin-top-right rounded-xl border border-admin-border bg-white shadow-lg dark:bg-gray-800">
                        <div class="p-2">
                            <p class="px-2 pb-1 pt-0.5 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Sort by</p>
                            @foreach(['updated_at' => 'Last Updated', 'name' => 'Segment Name', 'subscribers_count' => 'Subscriber Count', 'created_at' => 'Date Created'] as $col => $label)
                                <button type="submit" name="sort_by" value="{{ $col }}"
                                        onclick="document.getElementById('filterForm').querySelector('[name=order]') || (function(f,v){ var i=document.createElement('input');i.type='hidden';i.name='order';i.value=v;f.appendChild(i); })(document.getElementById('filterForm'), '{{ $order }}')"
                                        class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm text-gray-700 transition hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700 {{ $sortBy === $col ? 'font-semibold text-primary-600 dark:text-primary-400' : '' }}">
                                    {{ $label }}
                                    @if($sortBy === $col)
                                        <svg class="h-4 w-4 text-primary-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
                                    @endif
                                </button>
                            @endforeach

                            <div class="my-1.5 border-t border-gray-100 dark:border-gray-700"></div>
                            <p class="px-2 pb-1 pt-0.5 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Order</p>
                            @foreach(['desc' => 'Descending', 'asc' => 'Ascending'] as $dir => $label)
                                <button type="submit" name="order" value="{{ $dir }}"
                                        onclick="document.getElementById('filterForm').querySelector('[name=sort_by]') || (function(f,v){ var i=document.createElement('input');i.type='hidden';i.name='sort_by';i.value=v;f.appendChild(i); })(document.getElementById('filterForm'), '{{ $sortBy }}')"
                                        class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm text-gray-700 transition hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700 {{ $order === $dir ? 'font-semibold text-primary-600 dark:text-primary-400' : '' }}">
                                    {{ $label }}
                                    @if($order === $dir)
                                        <svg class="h-4 w-4 text-primary-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                        <input type="hidden" name="sort_by" value="{{ $sortBy }}">
                        <input type="hidden" name="order" value="{{ $order }}">
                    </div>
                </div>

                @if($search || $typeFilter)
                    <a href="{{ route('customer.lists.segments.index', $list) }}" class="text-sm font-medium text-gray-400 transition hover:text-gray-600 dark:hover:text-gray-200">Clear</a>
                @endif
            </div>
        </div>
        <input type="hidden" name="sort_by" value="{{ $sortBy }}">
        <input type="hidden" name="order" value="{{ $order }}">
        <input type="hidden" name="type" value="{{ $typeFilter }}">
    </form>

    {{-- ── Table ────────────────────────────────────────────────────────────── --}}
    <div class="overflow-hidden rounded-xl border border-admin-border bg-admin-sidebar shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-admin-border bg-gray-50/70 dark:bg-gray-800/60">
                        <th class="w-12 px-4 py-3.5 text-left">
                            <input type="checkbox" class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700">
                        </th>
                        <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Segment Name</th>
                        <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Rules</th>
                        <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Subscribers</th>
                        <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Growth (30d)</th>
                        <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Last Updated</th>
                        <th class="w-10 px-4 py-3.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                    @forelse($segments as $segment)
                        @php
                            $rules    = is_array($segment->rules ?? null) ? $segment->rules : [];
                            $dynamic  = segIsDynamic($rules);
                            $badges   = segRuleBadges($rules, $fieldLabels, $operatorLabels);
                            $combine  = strtoupper($rules['combine_operator'] ?? 'all') === 'ANY' ? 'OR' : 'AND';
                            $iconIdx  = abs(crc32($segment->name)) % count($iconKeys);
                            $iconKey  = $iconKeys[$iconIdx];
                            $iconBg   = $iconBgColors[$iconIdx % count($iconBgColors)];
                            $subCount = (int) ($segment->subscribers_count ?? 0);
                        @endphp
                        <tr class="group transition-colors hover:bg-gray-50/60 dark:hover:bg-gray-800/40">
                            {{-- Checkbox --}}
                            <td class="px-4 py-4">
                                <input type="checkbox" class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700">
                            </td>

                            {{-- Segment Name --}}
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $iconBg }}">
                                        {!! $iconSet[$iconKey] !!}
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $segment->name }}</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">
                                            {{ $dynamic ? 'Automatically updates daily' : 'Static List' }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            {{-- Rules --}}
                            <td class="px-4 py-4">
                                @if(count($badges) > 0)
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        @foreach($badges as $i => $badge)
                                            @if($i > 0 && !($badge['overflow'] ?? false))
                                                <span class="text-xs font-semibold text-gray-400 dark:text-gray-500">{{ $combine }}</span>
                                            @endif
                                            <span class="rounded-full border border-gray-200 bg-gray-50 px-2.5 py-0.5 text-xs font-medium text-gray-700 dark:border-gray-600 dark:bg-gray-700/50 dark:text-gray-300 {{ ($badge['overflow'] ?? false) ? 'text-gray-400 dark:text-gray-500' : '' }}">
                                                {{ $badge['label'] }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-sm text-gray-300 dark:text-gray-600">—</span>
                                @endif
                            </td>

                            {{-- Subscribers --}}
                            <td class="px-4 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ number_format($subCount) }}
                            </td>

                            {{-- Growth (30d) — placeholder 0% since no snapshot history --}}
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/></svg>
                                    0.0%
                                </span>
                            </td>

                            {{-- Last Updated --}}
                            <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ optional($segment->updated_at)->format('M d, Y') ?? '—' }}
                            </td>

                            {{-- 3-dot Actions --}}
                            <td class="px-4 py-4" x-data="{ open: false }" @click.outside="open = false">
                                <div class="relative">
                                    <button type="button" @click="open = !open"
                                            class="flex h-7 w-7 items-center justify-center rounded-lg text-gray-400 opacity-0 transition group-hover:opacity-100 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 3a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM10 8.5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM11.5 15.5a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0Z"/></svg>
                                    </button>
                                    <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                         class="absolute right-0 z-30 mt-1 w-44 origin-top-right rounded-xl border border-admin-border bg-white shadow-lg dark:bg-gray-800">
                                        <div class="p-1">
                                            <a href="{{ route('customer.lists.subscribers.index', $list) }}?segment_id={{ $segment->id }}"
                                               class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 transition hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700">
                                                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                                                View Subscribers
                                            </a>
                                            @customercan('lists.permissions.can_edit_lists')
                                            <a href="{{ route('customer.segments.create', ['list_id' => $list->id]) }}"
                                               class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 transition hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700">
                                                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                                                Duplicate
                                            </a>
                                            <div class="my-1 border-t border-gray-100 dark:border-gray-700"></div>
                                            <form method="POST" action="{{ route('customer.lists.segments.destroy', ['list' => $list, 'segment' => $segment]) }}"
                                                  onsubmit="return confirm('Delete segment \'{{ addslashes($segment->name) }}\'? This cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                                    Delete
                                                </button>
                                            </form>
                                            @endcustomercan
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z"/></svg>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">No segments found</p>
                                    @if($search || $typeFilter)
                                        <a href="{{ route('customer.lists.segments.index', $list) }}" class="text-sm font-medium text-primary-600 hover:text-primary-700">Clear filters</a>
                                    @else
                                        <a href="{{ route('customer.segments.create', ['list_id' => $list->id]) }}" class="rounded-lg bg-primary-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-primary-700">Create first segment</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="flex flex-col items-center justify-between gap-3 border-t border-admin-border px-5 py-4 sm:flex-row">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                @if($segments->total() > 0)
                    Showing <span class="font-medium text-gray-900 dark:text-gray-100">{{ number_format($segments->firstItem()) }}</span>
                    to <span class="font-medium text-gray-900 dark:text-gray-100">{{ number_format($segments->lastItem()) }}</span>
                    of <span class="font-medium text-gray-900 dark:text-gray-100">{{ number_format($segments->total()) }}</span> segments
                @else
                    No segments
                @endif
            </p>
            <div>{{ $segments->links() }}</div>
        </div>
    </div>
</div>
@endsection
