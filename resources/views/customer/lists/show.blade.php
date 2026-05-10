@extends('layouts.customer')

@section('title', $list->display_name ?? $list->name)
@section('page-title', $list->display_name ?? $list->name)

@php
    function fmtCount(int $n): string {
        if ($n >= 1_000_000) return round($n / 1_000_000, 1) . 'M';
        if ($n >= 1_000)     return round($n / 1_000, 1) . 'k';
        return (string) $n;
    }

    $sourceBadge = [
        'api'      => ['bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',   'API'],
        'popup'    => ['bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300', 'Popup'],
        'import'   => ['bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300', 'Import'],
        'webinar'  => ['bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300', 'Webinar'],
        'form'     => ['bg-teal-100 text-teal-700 dark:bg-teal-900 dark:text-teal-300',   'Form'],
        'manual'   => ['bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',    'Manual'],
        'default'  => ['bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',    'Other'],
    ];

    $avatarColors = ['bg-blue-500', 'bg-indigo-500', 'bg-violet-500', 'bg-emerald-500', 'bg-amber-500', 'bg-rose-500', 'bg-cyan-500', 'bg-fuchsia-500'];

    $healthColor = match($healthLabel) {
        'Excellent' => 'text-emerald-500',
        'Good'      => 'text-blue-500',
        'Fair'      => 'text-amber-500',
        default     => 'text-red-500',
    };

    $openRateDelta  = 0.0;
    $clickRateDelta = 0.0;
@endphp

@section('content')
<div class="space-y-6">
    {{-- ── Header ─────────────────────────────────────────────────────────── --}}
    @include('customer.lists.partials.header', ['list' => $list])

    {{-- ── Tab Navigation ──────────────────────────────────────────────────── --}}
    @include('customer.lists.partials.subnav', ['list' => $list, 'segmentsCount' => $segmentsCount])

    {{-- ── Stat Cards ──────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        {{-- Total Subscribers --}}
        <div class="rounded-xl border border-admin-border bg-admin-sidebar p-5 shadow-sm">
            <div class="flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400">
                Total Subscribers
                <svg class="h-3.5 w-3.5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
            </div>
            <div class="mt-2 flex items-end gap-2">
                <span class="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-50">{{ number_format((int) $list->subscribers_count) }}</span>
                @if($subscriberDelta != 0)
                    <span class="mb-0.5 inline-flex items-center gap-0.5 text-sm font-medium {{ $subscriberDelta > 0 ? 'text-emerald-600' : 'text-red-500' }}">
                        @if($subscriberDelta > 0)
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/></svg>
                        @else
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6 9 12.75l4.286-4.286a11.948 11.948 0 0 1 4.306 6.43l.776 2.898m0 0 3.182-5.511m-3.182 5.51-5.511-3.181"/></svg>
                        @endif
                        {{ abs($subscriberDelta) }}%
                    </span>
                @endif
            </div>
        </div>

        {{-- Avg. Open Rate --}}
        <div class="rounded-xl border border-admin-border bg-admin-sidebar p-5 shadow-sm">
            <div class="text-sm text-gray-500 dark:text-gray-400">Avg. Open Rate</div>
            <div class="mt-2 flex items-end gap-2">
                <span class="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-50">{{ number_format((float) ($listPerformance['open_rate'] ?? 0), 1) }}%</span>
                @if($openRateDelta != 0)
                    <span class="mb-0.5 inline-flex items-center gap-0.5 text-sm font-medium {{ $openRateDelta > 0 ? 'text-emerald-600' : 'text-red-500' }}">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/></svg>
                        {{ abs($openRateDelta) }}%
                    </span>
                @endif
            </div>
        </div>

        {{-- Avg. Click Rate --}}
        <div class="rounded-xl border border-admin-border bg-admin-sidebar p-5 shadow-sm">
            <div class="text-sm text-gray-500 dark:text-gray-400">Avg. Click Rate</div>
            <div class="mt-2 flex items-end gap-2">
                <span class="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-50">{{ number_format((float) ($listPerformance['click_rate'] ?? 0), 1) }}%</span>
                @if($clickRateDelta != 0)
                    <span class="mb-0.5 inline-flex items-center gap-0.5 text-sm font-medium {{ $clickRateDelta > 0 ? 'text-emerald-600' : 'text-red-500' }}">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/></svg>
                        {{ abs($clickRateDelta) }}%
                    </span>
                @endif
            </div>
        </div>

        {{-- List Health Score --}}
        <div class="rounded-xl border border-admin-border bg-admin-sidebar p-5 shadow-sm">
            <div class="text-sm text-gray-500 dark:text-gray-400">List Health Score</div>
            <div class="mt-2 flex items-end gap-2">
                <span class="text-3xl font-bold tracking-tight {{ $healthColor }}">{{ $healthLabel }}</span>
                <span class="mb-0.5 text-sm text-gray-400 dark:text-gray-500">{{ $healthScore }}/100</span>
            </div>
        </div>
    </div>

    {{-- ── Growth + Recent Additions ───────────────────────────────────────── --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3"
         x-data="{
            period: 30,
            chart: null,
            periods: {{ Js::from($growthPeriods) }},
            init() {
                this.$nextTick(() => { this.initChart(); });
            },
            initChart() {
                const ctx = document.getElementById('growthChart');
                if (!ctx || typeof Chart === 'undefined') return;
                const d = this.periods[this.period];
                const isDark = document.documentElement.classList.contains('dark');
                const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.05)';
                const tickColor = isDark ? '#9ca3af' : '#6b7280';
                this.chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: d.labels,
                        datasets: [{
                            data: d.values,
                            borderColor: '#3b82f6',
                            borderWidth: 2,
                            backgroundColor: 'rgba(59,130,246,0.08)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 0,
                            pointHoverRadius: 4,
                            pointHoverBackgroundColor: '#3b82f6',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { intersect: false, mode: 'index' },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: isDark ? '#1f2937' : '#fff',
                                borderColor: isDark ? '#374151' : '#e5e7eb',
                                borderWidth: 1,
                                titleColor: isDark ? '#f9fafb' : '#111827',
                                bodyColor: isDark ? '#d1d5db' : '#374151',
                                padding: 10,
                                callbacks: {
                                    label: ctx => ' ' + ctx.parsed.y + ' subscribers'
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                border: { display: false },
                                ticks: { color: tickColor, maxRotation: 0, font: { size: 11 } }
                            },
                            y: {
                                grid: { color: gridColor, drawBorder: false },
                                border: { display: false },
                                ticks: { color: tickColor, font: { size: 11 } },
                                beginAtZero: true
                            }
                        }
                    }
                });
            },
            changePeriod(p) {
                this.period = p;
                if (!this.chart) return;
                const d = this.periods[p];
                this.chart.data.labels = d.labels;
                this.chart.data.datasets[0].data = d.values;
                this.chart.update('active');
            }
         }">

        {{-- Subscriber Growth Chart --}}
        <div class="lg:col-span-2 rounded-xl border border-admin-border bg-admin-sidebar p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-50">Subscriber Growth</h3>
                <div class="relative">
                    <select
                        @change="changePeriod(parseInt($event.target.value))"
                        class="appearance-none rounded-lg border border-gray-200 bg-white py-1.5 pl-3 pr-8 text-xs font-medium text-gray-700 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                        aria-label="Select time period">
                        <option value="7" :selected="period === 7">Last 7 Days</option>
                        <option value="30" :selected="period === 30">Last 30 Days</option>
                        <option value="90" :selected="period === 90">Last 90 Days</option>
                    </select>
                    <svg class="pointer-events-none absolute right-2 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                </div>
            </div>
            <div class="mt-4 h-56">
                <canvas id="growthChart"></canvas>
            </div>
        </div>

        {{-- Recent Additions --}}
        <div class="rounded-xl border border-admin-border bg-admin-sidebar p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-50">Recent Additions</h3>
                <a href="{{ route('customer.lists.subscribers.index', $list) }}"
                   class="text-xs font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                    View all
                </a>
            </div>
            <ul class="mt-4 divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($recentAdditions as $subscriber)
                    @php
                        $initials = '';
                        if ($subscriber->first_name || $subscriber->last_name) {
                            $initials = strtoupper(
                                substr((string) ($subscriber->first_name ?? ''), 0, 1) .
                                substr((string) ($subscriber->last_name ?? ''), 0, 1)
                            );
                        } else {
                            $initials = strtoupper(substr($subscriber->email, 0, 2));
                        }
                        $colorIndex = abs(crc32($subscriber->email)) % count($avatarColors);
                        $avatarBg   = $avatarColors[$colorIndex];
                        $src        = strtolower((string) ($subscriber->source ?? 'default'));
                        [$badgeClass, $badgeLabel] = $sourceBadge[$src] ?? $sourceBadge['default'];
                    @endphp
                    <li class="flex items-center gap-3 py-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $avatarBg }} text-xs font-semibold text-white">
                            {{ $initials ?: '?' }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ trim(($subscriber->first_name ?? '') . ' ' . ($subscriber->last_name ?? '')) ?: 'Subscriber' }}
                            </p>
                            <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $subscriber->email }}</p>
                        </div>
                        @if($subscriber->source)
                            <span class="shrink-0 rounded-md px-2 py-0.5 text-xs font-medium {{ $badgeClass }}">
                                {{ $badgeLabel }}
                            </span>
                        @endif
                    </li>
                @empty
                    <li class="py-6 text-center text-sm text-gray-400 dark:text-gray-500">No subscribers yet.</li>
                @endforelse
            </ul>
        </div>
    </div>

    {{-- ── Recent Campaign Activity ─────────────────────────────────────────── --}}
    @if($recentCampaigns->isNotEmpty())
    <div id="reports" class="rounded-xl border border-admin-border bg-admin-sidebar shadow-sm">
        <div class="flex items-center justify-between border-b border-admin-border px-5 py-4">
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-50">Recent Campaign Activity</h3>
            <a href="{{ route('customer.campaigns.index') }}"
               class="text-xs font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                View all campaigns
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                <thead>
                    <tr class="bg-gray-50/60 dark:bg-gray-800/50">
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Campaign</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Sent</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Open Rate</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Click Rate</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($recentCampaigns as $campaign)
                        @php
                            $sent      = (int) $campaign->sent_count;
                            $bounced   = (int) $campaign->bounced_count;
                            $delivered = max(0, $sent - $bounced);
                            $cOpenRate  = $delivered > 0 ? round(($campaign->opened_count / $delivered) * 100, 1) : 0;
                            $cClickRate = $delivered > 0 ? round(($campaign->clicked_count / $delivered) * 100, 1) : 0;
                        @endphp
                        <tr class="transition hover:bg-gray-50/50 dark:hover:bg-gray-800/40">
                            <td class="px-5 py-3.5 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $campaign->name }}</td>
                            <td class="px-5 py-3.5">
                                @php
                                    $statusColor = match($campaign->status) {
                                        'sent'    => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400',
                                        'draft'   => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
                                        'sending' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400',
                                        'paused'  => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400',
                                        default   => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
                                    };
                                @endphp
                                <span class="rounded-md px-2 py-0.5 text-xs font-medium {{ $statusColor }}">{{ ucfirst((string) $campaign->status) }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-right text-sm text-gray-700 dark:text-gray-300">{{ number_format($sent) }}</td>
                            <td class="px-5 py-3.5 text-right text-sm font-medium text-blue-600 dark:text-blue-400">{{ $cOpenRate }}%</td>
                            <td class="px-5 py-3.5 text-right text-sm font-medium text-indigo-600 dark:text-indigo-400">{{ $cClickRate }}%</td>
                            <td class="px-5 py-3.5 text-right text-sm text-gray-500 dark:text-gray-400">{{ optional($campaign->created_at)->format('M d, Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

