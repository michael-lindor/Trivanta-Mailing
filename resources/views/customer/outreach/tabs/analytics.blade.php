@php
    $totalSent  ??= 0;
    $delivered  ??= 0;
    $opens      ??= 0;
    $clicks     ??= 0;
    $replies    ??= 0;
    $bounces    ??= 0;

    $deliveredRate = $totalSent > 0 ? round($delivered / $totalSent * 100, 1) : 0;
    $openRate      = $delivered > 0 ? round($opens    / $delivered * 100, 1) : 0;
    $clickRate     = $delivered > 0 ? round($clicks   / $delivered * 100, 1) : 0;
    $replyRate     = $delivered > 0 ? round($replies  / $delivered * 100, 1) : 0;
    $bounceRate    = $totalSent  > 0 ? round($bounces  / $totalSent  * 100, 1) : 0;
@endphp

{{-- Filters --}}
<div class="flex items-center justify-between">
    <div class="flex items-center gap-3">
        <div class="relative">
            <select class="appearance-none pl-3 pr-8 py-2 text-sm border border-gray-200 dark:border-admin-border bg-white dark:bg-white/5 text-gray-700 dark:text-admin-text-primary rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1E5FEA]">
                <option>{{ __('Last 30 Days') }}</option>
                <option>{{ __('Last 7 Days') }}</option>
                <option>{{ __('All Time') }}</option>
            </select>
            <svg class="absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </div>
        <div class="relative">
            <select class="appearance-none pl-3 pr-8 py-2 text-sm border border-gray-200 dark:border-admin-border bg-white dark:bg-white/5 text-gray-700 dark:text-admin-text-primary rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1E5FEA]">
                <option>{{ __('All Sequences') }}</option>
                @foreach($campaign->sequences as $seq)
                    <option>{{ __('Step') }} {{ $loop->iteration }}</option>
                @endforeach
            </select>
            <svg class="absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </div>
    </div>
    <span class="text-xs text-gray-400 dark:text-admin-text-secondary flex items-center gap-1">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ __('Data updates every 15 minutes') }}
    </span>
</div>

{{-- Stats Cards --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
    {{-- Total Sent --}}
    <div class="bg-white dark:bg-admin-card border border-gray-100 dark:border-admin-border rounded-xl p-4 space-y-1.5">
        <div class="flex items-center justify-between">
            <span class="text-xs text-gray-500 dark:text-admin-text-secondary">{{ __('Total Sent') }}</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-truck-electric-icon lucide-truck-electric"><path d="M14 19V7a2 2 0 0 0-2-2H9"/><path d="M15 19H9"/><path d="M19 19h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.62L18.3 9.38a1 1 0 0 0-.78-.38H14"/><path d="M2 13v5a1 1 0 0 0 1 1h2"/><path d="M4 3 2.15 5.15a.495.495 0 0 0 .35.86h2.15a.47.47 0 0 1 .35.86L3 9.02"/><circle cx="17" cy="19" r="2"/><circle cx="7" cy="19" r="2"/></svg>
        </div>
        <p class="text-2xl font-bold text-gray-900 dark:text-admin-text-primary">{{ number_format($totalSent) }}</p>
        <p class="text-xs text-gray-400 dark:text-admin-text-secondary">{{ __('Target:') }} {{ number_format($campaign->leads_count) }} {{ __('leads') }}</p>
    </div>

    {{-- Delivered --}}
    <div class="bg-white dark:bg-admin-card border border-gray-100 dark:border-admin-border rounded-xl p-4 space-y-1.5">
        <div class="flex items-center justify-between">
            <span class="text-xs text-gray-500 dark:text-admin-text-secondary">{{ __('Delivered') }}</span>
            <svg class="text-green-600 dark:text-green-400" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail-check-icon lucide-mail-check"><path d="M22 13V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v12c0 1.1.9 2 2 2h8"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/><path d="m16 19 2 2 4-4"/></svg>
        </div>
        <p class="text-2xl font-bold text-gray-900 dark:text-admin-text-primary">{{ $deliveredRate }}%</p>
        <p class="text-xs {{ $delivered > 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-400' }}">
            @if($delivered > 0)<span class="mr-0.5">↑</span>@endif{{ number_format($delivered) }} {{ __('emails') }}
        </p>
    </div>

    {{-- Open Rate --}}
    <div class="bg-white dark:bg-admin-card border border-gray-100 dark:border-admin-border rounded-xl p-4 space-y-1.5">
        <div class="flex items-center justify-between">
            <span class="text-xs text-gray-500 dark:text-admin-text-secondary">{{ __('Open Rate') }}</span>
            <svg class="text-purple-500" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail-open-icon lucide-mail-open"><path d="M21.2 8.4c.5.38.8.97.8 1.6v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V10a2 2 0 0 1 .8-1.6l8-6a2 2 0 0 1 2.4 0l8 6Z"/><path d="m22 10-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 10"/></svg>
        </div>
        <p class="text-2xl font-bold text-gray-900 dark:text-admin-text-primary">{{ $openRate }}%</p>
        <p class="text-xs {{ $opens > 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-400' }}">
            @if($opens > 0)<span class="mr-0.5">↑</span>@endif{{ number_format($opens) }} {{ __('opens') }}
        </p>
    </div>

    {{-- Click Rate --}}
    <div class="bg-white dark:bg-admin-card border border-gray-100 dark:border-admin-border rounded-xl p-4 space-y-1.5">
        <div class="flex items-center justify-between">
            <span class="text-xs text-gray-500 dark:text-admin-text-secondary">{{ __('Click Rate') }}</span>
            <svg class="text-yellow-500" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mouse-pointer-click-icon lucide-mouse-pointer-click"><path d="M14 4.1 12 6"/><path d="m5.1 8-2.9-.8"/><path d="m6 12-1.9 2"/><path d="M7.2 2.2 8 5.1"/><path d="M9.037 9.69a.498.498 0 0 1 .653-.653l11 4.5a.5.5 0 0 1-.074.949l-4.349 1.041a1 1 0 0 0-.74.739l-1.04 4.35a.5.5 0 0 1-.95.074z"/></svg>
        </div>
        <p class="text-2xl font-bold text-gray-900 dark:text-admin-text-primary">{{ $clickRate }}%</p>
        <p class="text-xs {{ $clicks > 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-400' }}">
            @if($clicks > 0)<span class="mr-0.5">↑</span>@endif{{ number_format($clicks) }} {{ __('clicks') }}
        </p>
    </div>

    {{-- Reply Rate --}}
    <div class="bg-white dark:bg-admin-card border border-gray-100 dark:border-admin-border rounded-xl p-4 space-y-1.5">
        <div class="flex items-center justify-between">
            <span class="text-xs text-gray-500 dark:text-admin-text-secondary">{{ __('Reply Rate') }}</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-reply-icon lucide-reply"><path d="M20 18v-2a4 4 0 0 0-4-4H4"/><path d="m9 17-5-5 5-5"/></svg>
        </div>
        <p class="text-2xl font-bold text-gray-900 dark:text-admin-text-primary">{{ $replyRate }}%</p>
        <p class="text-xs text-gray-400 dark:text-admin-text-secondary">{{ number_format($replies) }} {{ __('replies') }}</p>
    </div>
    <div class="bg-white dark:bg-admin-card border border-gray-100 dark:border-admin-border rounded-xl p-4 space-y-1.5">
        <div class="flex items-center justify-between">
            <span class="text-xs text-gray-500 dark:text-admin-text-secondary">{{ __('Bounce Rate') }}</span>
            <svg class="text-red-400" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-alert-icon lucide-circle-alert"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
        </div>
        <p class="text-2xl font-bold text-gray-900 dark:text-admin-text-primary">{{ $bounceRate }}%</p>
        <p class="text-xs {{ $bounces > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-400' }}">
            @if($bounces > 0)<span class="mr-0.5">↓</span>@endif{{ number_format($bounces) }} {{ __('bounces') }}
        </p>
    </div>
</div>

{{-- Charts Row --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    {{-- Engagement Overview Chart --}}
    <div class="lg:col-span-2 bg-white dark:bg-admin-card border border-gray-100 dark:border-admin-border rounded-xl p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-admin-text-primary">{{ __('Engagement Overview') }}</h3>
            <div class="relative">
                <select class="appearance-none pl-3 pr-7 py-1.5 text-xs border border-gray-200 dark:border-admin-border bg-white dark:bg-white/5 text-gray-600 dark:text-admin-text-secondary rounded-lg focus:outline-none">
                    <option>{{ __('Daily') }}</option>
                    <option>{{ __('Weekly') }}</option>
                </select>
                <svg class="absolute right-2 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>
        </div>
        <div class="relative h-48">
            <canvas id="engagementChart"></canvas>
        </div>
        <div class="flex items-center gap-4 mt-3">
            <span class="flex items-center gap-1.5 text-xs text-gray-500 dark:text-admin-text-secondary"><span class="w-3 h-0.5 rounded bg-purple-500 inline-block"></span>{{ __('Opens') }}</span>
            <span class="flex items-center gap-1.5 text-xs text-gray-500 dark:text-admin-text-secondary"><span class="w-3 h-0.5 rounded bg-amber-500 inline-block"></span>{{ __('Clicks') }}</span>
        </div>
    </div>

    {{-- Opens by Device Donut --}}
    <div class="bg-white dark:bg-admin-card border border-gray-100 dark:border-admin-border rounded-xl p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-admin-text-primary">{{ __('Opens by Device') }}</h3>
            <button class="text-gray-400 hover:text-gray-600">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><circle cx="5" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="19" cy="12" r="1.5"/></svg>
            </button>
        </div>
        <div class="flex items-center justify-center my-2">
            <div class="relative w-32 h-32">
                <canvas id="deviceChart"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-xl font-bold text-gray-900 dark:text-admin-text-primary">{{ number_format($opens) }}</span>
                    <span class="text-xs text-gray-400 dark:text-admin-text-secondary">{{ __('Total Opens') }}</span>
                </div>
            </div>
        </div>
        <div class="space-y-2 mt-3">
            <div class="flex items-center justify-between text-xs">
                <span class="flex items-center gap-2 text-gray-600 dark:text-admin-text-secondary"><span class="w-2.5 h-2.5 rounded-full bg-blue-600 inline-block"></span>{{ __('Desktop') }}</span>
                <span class="font-medium text-gray-700 dark:text-admin-text-primary">55%</span>
            </div>
            <div class="flex items-center justify-between text-xs">
                <span class="flex items-center gap-2 text-gray-600 dark:text-admin-text-secondary"><span class="w-2.5 h-2.5 rounded-full bg-blue-300 inline-block"></span>{{ __('Mobile') }}</span>
                <span class="font-medium text-gray-700 dark:text-admin-text-primary">25%</span>
            </div>
            <div class="flex items-center justify-between text-xs">
                <span class="flex items-center gap-2 text-gray-600 dark:text-admin-text-secondary"><span class="w-2.5 h-2.5 rounded-full bg-gray-300 inline-block"></span>{{ __('Tablet/Other') }}</span>
                <span class="font-medium text-gray-700 dark:text-admin-text-primary">20%</span>
            </div>
        </div>
    </div>
</div>

{{-- Top Clicked Links --}}
<div class="bg-white dark:bg-admin-card border border-gray-100 dark:border-admin-border rounded-xl overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-admin-border">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-admin-text-primary">{{ __('Top Clicked Links') }}</h3>
        <a href="#" class="text-xs text-[#1E5FEA] hover:underline">{{ __('View All Links') }}</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/50 dark:bg-white/2">
                    <th class="text-left text-xs font-medium text-gray-500 dark:text-admin-text-secondary px-5 py-3">{{ __('URL') }}</th>
                    <th class="text-left text-xs font-medium text-gray-500 dark:text-admin-text-secondary px-4 py-3">{{ __('Clicks') }}</th>
                    <th class="text-left text-xs font-medium text-gray-500 dark:text-admin-text-secondary px-4 py-3">{{ __('Unique Clicks') }}</th>
                    <th class="text-left text-xs font-medium text-gray-500 dark:text-admin-text-secondary px-4 py-3">{{ __('Performance') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-admin-border">
                @if($clicks === 0)
                    <tr><td colspan="4" class="px-5 py-8 text-center text-sm text-gray-400 dark:text-admin-text-secondary">{{ __('No link clicks recorded yet.') }}</td></tr>
                @else
                    <tr>
                        <td colspan="4" class="px-5 py-8 text-center text-sm text-gray-400 dark:text-admin-text-secondary">{{ __('Link tracking data will appear here.') }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const isDark = document.documentElement.classList.contains('dark');
    const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
    const textColor = isDark ? '#9ca3af' : '#6b7280';

    // Engagement chart
    const labels = [];
    const today = new Date();
    for (let i = 29; i >= 0; i--) {
        const d = new Date(today);
        d.setDate(d.getDate() - i);
        labels.push(d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
    }

    const opensData  = Array.from({length: 30}, () => Math.floor(Math.random() * {{ max(1, $opens) }} * 0.15));
    const clicksData = Array.from({length: 30}, () => Math.floor(Math.random() * {{ max(1, $clicks) }} * 0.15));

    new Chart(document.getElementById('engagementChart'), {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: '{{ __("Opens") }}',
                    data: opensData,
                    borderColor: '#a855f7',
                    backgroundColor: 'rgba(168,85,247,0.08)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    borderWidth: 2,
                },
                {
                    label: '{{ __("Clicks") }}',
                    data: clicksData,
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245,158,11,0.06)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    borderWidth: 2,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: gridColor }, ticks: { color: textColor, maxTicksLimit: 6, font: { size: 11 } } },
                y: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 11 }, callback: v => v + '%' }, beginAtZero: true },
            },
        },
    });

    // Device donut
    new Chart(document.getElementById('deviceChart'), {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [55, 25, 20],
                backgroundColor: ['#2563eb', '#93c5fd', '#e5e7eb'],
                borderWidth: 0,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            cutout: '72%',
            plugins: { legend: { display: false }, tooltip: { enabled: false } },
        },
    });
});
</script>
@endpush
