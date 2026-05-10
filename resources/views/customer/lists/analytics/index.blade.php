@extends('layouts.customer')

@section('title', 'Analytics – ' . ($list->display_name ?? $list->name))

@section('content')
@php
    $confirmedCountDisplay = (int) ($list->confirmed_subscribers_count ?? 0);
    $lastActivity = $list->last_subscriber_at ?? $list->updated_at ?? null;

    $badge = function (float $d): array {
        if ($d > 0) return [
            'cls' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
            'prefix' => '+', 'dir' => 'up',
        ];
        if ($d < 0) return [
            'cls' => 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
            'prefix' => '', 'dir' => 'down',
        ];
        return ['cls' => 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400', 'prefix' => '', 'dir' => 'neutral'];
    };

    $audienceBadge = $badge($audienceDelta);
    $openBadge     = $badge($openRateDelta);
    $clickBadge    = $badge($clickRateDelta);
    $unsubBadge    = $badge($unsubsDelta);

    $periodLabels = ['7' => 'Last 7 Days', '30' => 'Last 30 Days', '90' => 'Last 90 Days'];
    $periodLabel  = $periodLabels[(string) $period] ?? 'Last 30 Days';
@endphp

<div class="space-y-5" x-data="{ periodOpen: false }" @click.outside="periodOpen = false">

    {{-- ── Header ─────────────────────────────────────────────────────────── --}}
    @include('customer.lists.partials.header', ['list' => $list])

    {{-- ── Tab Nav ──────────────────────────────────────────────────────────── --}}
    @include('customer.lists.partials.subnav', ['list' => $list])

    {{-- ── Performance Overview Header ─────────────────────────────────────── --}}
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-50">Performance Overview</h2>
        <div class="relative">
            <button type="button" @click="periodOpen = !periodOpen"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                {{ $periodLabel }}
                <svg class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
            </button>
            <div x-show="periodOpen" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 class="absolute right-0 z-20 mt-1 w-40 origin-top-right rounded-xl border border-admin-border bg-white shadow-lg dark:bg-gray-800">
                <div class="p-1">
                    @foreach(['7' => 'Last 7 Days', '30' => 'Last 30 Days', '90' => 'Last 90 Days'] as $val => $label)
                        <a href="{{ route('customer.lists.analytics', ['list' => $list, 'period' => $val]) }}"
                           class="flex items-center justify-between rounded-lg px-3 py-2 text-sm transition hover:bg-gray-50 dark:hover:bg-gray-700 {{ (string)$period === $val ? 'font-semibold text-primary-600 dark:text-primary-400' : 'text-gray-700 dark:text-gray-200' }}">
                            {{ $label }}
                            @if((string)$period === $val)
                                <svg class="h-3.5 w-3.5 text-primary-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ── Stat Cards ───────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        @php
        $statCards = [
            [
                'label' => 'Total Audience',
                'value' => number_format($confirmedCountDisplay),
                'badge' => $audienceBadge,
                'delta' => $audienceDelta,
                'unit'  => '',
                'icon'  => '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>',
            ],
            [
                'label' => 'Avg Open Rate',
                'value' => number_format($currentOpen, 1) . '%',
                'badge' => $openBadge,
                'delta' => $openRateDelta,
                'unit'  => '%',
                'icon'  => '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>',
            ],
            [
                'label' => 'Avg Click Rate',
                'value' => number_format($currentClick, 1) . '%',
                'badge' => $clickBadge,
                'delta' => $clickRateDelta,
                'unit'  => '%',
                'icon'  => '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.042 21.672 13.684 16.6m0 0-2.51 2.225.569-9.47 5.227 7.917-3.286-.672ZM12 2.25V4.5m5.834.166-1.591 1.591M20.25 10.5H18M7.757 14.743l-1.59 1.59M6 10.5H3.75m4.007-4.243-1.59-1.59"/></svg>',
            ],
            [
                'label' => 'Unsubscribes',
                'value' => number_format($unsubsCurrent),
                'badge' => $unsubBadge,
                'delta' => $unsubsDelta,
                'unit'  => '',
                'icon'  => '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M22 10H16M15.75 6A3.75 3.75 0 1 1 8.25 6a3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>',
            ],
        ];
        @endphp

        @foreach($statCards as $card)
        <div class="rounded-xl border border-admin-border bg-white p-5 shadow-sm dark:bg-gray-800">
            <div class="mb-3 flex items-center justify-between">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $card['label'] }}</p>
                <span class="text-gray-300 dark:text-gray-600">{!! $card['icon'] !!}</span>
            </div>
            <p class="mb-2.5 text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-50">{{ $card['value'] }}</p>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-0.5 rounded-full px-2 py-0.5 text-xs font-semibold {{ $card['badge']['cls'] }}">
                    @if($card['badge']['dir'] === 'up')
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18"/></svg>
                    @elseif($card['badge']['dir'] === 'down')
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3"/></svg>
                    @else
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/></svg>
                    @endif
                    {{ $card['badge']['prefix'] }}{{ number_format(abs($card['delta']), 1) }}%
                </span>
                <span class="text-xs text-gray-400 dark:text-gray-500">vs last {{ $days === 7 ? 'week' : ($days === 30 ? 'month' : '90 days') }}</span>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ── Row 2: Audience Growth + Device Breakdown ───────────────────────── --}}
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-5">

        {{-- Audience Growth chart (3/5) --}}
        <div class="rounded-xl border border-admin-border bg-white p-6 shadow-sm dark:bg-gray-800 lg:col-span-3">
            <div class="mb-5 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-50">Audience Growth</h3>
                <a href="{{ route('customer.lists.subscribers.index', $list) }}"
                   class="text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400">View Details</a>
            </div>
            <div class="h-56">
                <canvas id="growthChart"></canvas>
            </div>
        </div>

        {{-- Device Breakdown (2/5) --}}
        <div class="rounded-xl border border-admin-border bg-white p-6 shadow-sm dark:bg-gray-800 lg:col-span-2">
            <h3 class="mb-5 text-sm font-semibold text-gray-900 dark:text-gray-50">Device Breakdown</h3>
            <div class="space-y-5">
                @php
                $devices = [
                    ['label' => 'Mobile',  'icon' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 8.25h3"/></svg>', 'pct' => 0, 'color' => 'bg-blue-500'],
                    ['label' => 'Desktop', 'icon' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0H3"/></svg>', 'pct' => 0, 'color' => 'bg-blue-400'],
                    ['label' => 'Tablet',  'icon' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5h3m-6.75 2.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-15a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 4.5v15a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>', 'pct' => 0, 'color' => 'bg-blue-200'],
                ];
                @endphp
                @foreach($devices as $device)
                <div>
                    <div class="mb-1.5 flex items-center justify-between">
                        <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                            <span class="text-gray-400">{!! $device['icon'] !!}</span>
                            {{ $device['label'] }}
                        </div>
                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $device['pct'] }}%</span>
                    </div>
                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                        <div class="h-1.5 rounded-full {{ $device['color'] }}" style="width: {{ $device['pct'] }}%"></div>
                    </div>
                </div>
                @endforeach
                <p class="pt-2 text-xs text-gray-400 dark:text-gray-500 text-center">Device tracking not yet configured</p>
            </div>
        </div>
    </div>

    {{-- ── Row 3: Engagement Trends + Top Segments ─────────────────────────── --}}
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">

        {{-- Engagement Trends --}}
        <div class="rounded-xl border border-admin-border bg-white p-6 shadow-sm dark:bg-gray-800">
            <div class="mb-2 flex items-start justify-between">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-50">Engagement Trends</h3>
                <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                    <span class="flex items-center gap-1"><span class="inline-block h-2.5 w-2.5 rounded-full bg-blue-500"></span>Opens</span>
                    <span class="flex items-center gap-1"><span class="inline-block h-2.5 w-2.5 rounded-full bg-blue-200"></span>Clicks</span>
                </div>
            </div>
            <div class="h-56">
                <canvas id="engagementChart"></canvas>
            </div>
        </div>

        {{-- Top Segments by Engagement --}}
        <div class="rounded-xl border border-admin-border bg-white p-6 shadow-sm dark:bg-gray-800">
            <div class="mb-5 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-50">Top Segments by Engagement</h3>
                <a href="{{ route('customer.lists.segments.index', $list) }}"
                   class="text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400">View All</a>
            </div>

            @if($topSegments->isEmpty())
                <div class="flex flex-col items-center justify-center py-8 text-center">
                    <svg class="h-8 w-8 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z"/></svg>
                    <p class="mt-2 text-sm text-gray-400">No segments yet</p>
                    <a href="{{ route('customer.segments.create', ['list_id' => $list->id]) }}" class="mt-2 text-sm font-medium text-primary-600 hover:text-primary-700">Create first segment</a>
                </div>
            @else
                <div class="divide-y divide-gray-100 dark:divide-gray-700/60">
                    @foreach($topSegments as $seg)
                    @php
                        $rules = is_array($seg->rules ?? null) ? $seg->rules : [];
                        $conds = $rules['conditions'] ?? [];
                        $isDynamic = is_array($conds) && count($conds) > 0;
                        $subCount = (int) ($seg->subscribers_count ?? 0);
                    @endphp
                    <div class="flex items-center justify-between py-3.5">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $seg->name }}</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500">{{ number_format($subCount) }} subscribers</p>
                        </div>
                        <div class="ml-4 flex shrink-0 items-end gap-4 text-right">
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">—</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">Open</p>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">—</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">Click</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(function () {
    const isDark = document.documentElement.classList.contains('dark');
    const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
    const tickColor = isDark ? '#9ca3af' : '#6b7280';

    const growthLabels = @json(array_column($weeklyGrowth, 'label'));
    const growthValues = @json(array_column($weeklyGrowth, 'value'));

    const engLabels  = @json(array_column($weeklyEngagement, 'label'));
    const engOpens   = @json(array_column($weeklyEngagement, 'opens'));
    const engClicks  = @json(array_column($weeklyEngagement, 'clicks'));

    // ── Audience Growth Bar Chart ────────────────────────────────────
    new Chart(document.getElementById('growthChart'), {
        type: 'bar',
        data: {
            labels: growthLabels,
            datasets: [{
                data: growthValues,
                backgroundColor: '#3b82f6',
                borderRadius: 5,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ' ' + ctx.parsed.y + ' subscribers' } } },
            scales: {
                y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: tickColor, maxTicksLimit: 5 } },
                x: { grid: { display: false }, ticks: { color: tickColor } }
            }
        }
    });

    // ── Engagement Trends Grouped Bar Chart ──────────────────────────
    new Chart(document.getElementById('engagementChart'), {
        type: 'bar',
        data: {
            labels: engLabels,
            datasets: [
                {
                    label: 'Opens',
                    data: engOpens,
                    backgroundColor: '#3b82f6',
                    borderRadius: 4,
                    borderSkipped: false,
                },
                {
                    label: 'Clicks',
                    data: engClicks,
                    backgroundColor: '#bfdbfe',
                    borderRadius: 4,
                    borderSkipped: false,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => ' ' + ctx.dataset.label + ': ' + ctx.parsed.y + '%' } }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    grid: { color: gridColor },
                    ticks: { color: tickColor, maxTicksLimit: 5, callback: v => v + '%' }
                },
                x: { grid: { display: false }, ticks: { color: tickColor } }
            }
        }
    });
})();
</script>
@endpush
