@extends('layouts.customer')

@section('title', 'Dashboard')

@section('page-header')
<div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div class="min-w-0">
        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-widest">Overview &middot; {{ $customerLocalTime->format('F Y') }}</p>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $greeting }}, {{ $firstName }} 👋</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            You have {{ $activeCampaignsCount }} active {{ $activeCampaignsCount === 1 ? 'campaign' : 'campaigns' }} right now.
        </p>
    </div>
    <div class="flex items-center gap-2.5 flex-shrink-0">
        <a href="{{ route('customer.campaigns.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Campaign
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-5">

    {{-- Pending account warning --}}
    @if(auth()->guard('customer')->user()->status === 'pending')
    <div class="flex gap-3 p-4 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-xl dark:bg-yellow-900/20 dark:border-yellow-700/40 dark:text-yellow-300">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
        <div>
            <p class="text-sm font-semibold">Account Pending Approval</p>
            <p class="text-sm mt-0.5 opacity-80">Your account is pending approval. Some features may be limited until an administrator approves it.</p>
        </div>
    </div>
    @endif

    {{-- ── Row 1: Usage stat cards ─────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

        {{-- Lists --}}
        @php
            $listPct      = $listLimit ? min(100, round(($emailListsCount / max(1,$listLimit)) * 100, 1)) : null;
            $listOverflow = $listLimit && $emailListsCount > $listLimit;
        @endphp
        <div class="bg-white dark:bg-admin-card rounded-lg p-5 border border-gray-100 dark:border-admin-border shadow-sm">
            <div class="flex items-start gap-3 mb-4 justify-between">
                <div class="min-w-0">
                    <p class="text-3xl font-bold text-gray-900 dark:text-white leading-tight mt-0.5">{{ number_format($emailListsCount) }}</p>
                    <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide">Lists</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center flex-shrink-0">
                    <svg class="text-gray-600 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-list-ordered-icon lucide-list-ordered"><path d="M11 5h10"/><path d="M11 12h10"/><path d="M11 19h10"/><path d="M4 4h1v5"/><path d="M4 9h2"/><path d="M6.5 20H3.4c0-1 2.6-1.925 2.6-3.5a1.5 1.5 0 0 0-2.6-1.02"/></svg>
                </div>
            </div>
            @if($listLimit)
                <div class="flex items-center justify-between text-xs text-gray-400 dark:text-gray-500 mb-1.5">
                    <span>{{ $listPct }}%</span>
                    <span>{{ number_format($listLimit) }} max</span>
                </div>
                <div class="h-1.5 rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden">
                    <div class="h-full rounded-full transition-all {{ $listOverflow ? 'bg-red-500' : 'bg-blue-500' }}" style="width:{{ min(100,$listPct) }}%"></div>
                </div>
            @else
                <p class="text-xs text-gray-400 dark:text-gray-500">Unlimited</p>
            @endif
        </div>

        {{-- Campaigns --}}
        @php
            $campPct      = $campaignLimit ? min(100, round(($campaignsCount / max(1,$campaignLimit)) * 100, 1)) : null;
            $campOverflow = $campaignLimit && $campaignsCount > $campaignLimit;
        @endphp
        <div class="bg-white dark:bg-admin-card rounded-lg p-5 border border-gray-100 dark:border-admin-border shadow-sm">
            <div class="flex justify-between gap-3 mb-4">
                <div class="min-w-0">
                    <p class="text-3xl font-bold text-gray-900 dark:text-white leading-tight mt-0.5">{{ number_format($campaignsCount) }}</p>
                    <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide">Campaigns</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-violet-50 dark:bg-violet-900/20 flex items-center justify-center flex-shrink-0">
                    <svg class="text-violet-600 dark:text-violet-400" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-megaphone-icon lucide-megaphone"><path d="M11 6a13 13 0 0 0 8.4-2.8A1 1 0 0 1 21 4v12a1 1 0 0 1-1.6.8A13 13 0 0 0 11 14H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2z"/><path d="M6 14a12 12 0 0 0 2.4 7.2 2 2 0 0 0 3.2-2.4A8 8 0 0 1 10 14"/><path d="M8 6v8"/></svg>
                </div>
                
            </div>
            @if($campaignLimit)
                <div class="flex items-center justify-between text-xs text-gray-400 dark:text-gray-500 mb-1.5">
                    <span>{{ $campPct }}%</span>
                    <span>{{ number_format($campaignLimit) }} max</span>
                </div>
                <div class="h-1.5 rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden">
                    <div class="h-full rounded-full transition-all {{ $campOverflow ? 'bg-red-500' : 'bg-violet-500' }}" style="width:{{ min(100,$campPct) }}%"></div>
                </div>
            @else
                <p class="text-xs text-gray-400 dark:text-gray-500">Unlimited</p>
            @endif
        </div>

        {{-- Subscribers --}}
        @php
            $subPct      = $subscriberLimit ? min(100, round(($subscribersCount / max(1,$subscriberLimit)) * 100, 1)) : null;
            $subOverflow = $subscriberLimit && $subscribersCount > $subscriberLimit;
        @endphp
        <div class="bg-white dark:bg-admin-card rounded-lg p-5 border border-gray-100 dark:border-admin-border shadow-sm">
            <div class="flex justify-between gap-3 mb-4">
                <div class="min-w-0">
                    <p class="text-3xl font-bold text-gray-900 dark:text-white leading-tight mt-0.5">{{ number_format($subscribersCount) }}</p>
                    <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide">Subscribers</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center flex-shrink-0">
                    <svg class="text-emerald-600 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users-icon lucide-users"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><path d="M16 3.128a4 4 0 0 1 0 7.744"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><circle cx="9" cy="7" r="4"/></svg>
                </div>
                
            </div>
            @if($subscriberLimit)
                <div class="flex items-center justify-between text-xs text-gray-400 dark:text-gray-500 mb-1.5">
                    <span>{{ $subPct }}%</span>
                    <span>{{ number_format($subscriberLimit) }} max</span>
                </div>
                <div class="h-1.5 rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden">
                    <div class="h-full rounded-full transition-all {{ $subOverflow ? 'bg-red-500' : 'bg-emerald-500' }}" style="width:{{ min(100,$subPct) }}%"></div>
                </div>
            @else
                <p class="text-xs text-gray-400 dark:text-gray-500">Unlimited</p>
            @endif
        </div>

        {{-- Emails Sent --}}
        @php
            $sentPct      = $quotaLimit ? min(100, round(($quotaUsed / max(1,$quotaLimit)) * 100, 1)) : null;
            $sentOverflow = $quotaLimit && $quotaUsed > $quotaLimit;
        @endphp
        <div class="bg-white dark:bg-admin-card rounded-lg p-5 border border-gray-100 dark:border-admin-border shadow-sm">
            <div class="flex justify-between gap-3 mb-4">
                <div class="min-w-0">
                    <p class="text-3xl font-bold text-gray-900 dark:text-white leading-tight mt-0.5">{{ number_format($quotaUsed) }}</p>
                    <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide">Emails Sent</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center flex-shrink-0">
                    <svg class="text-yellow-600" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-truck-electric-icon lucide-truck-electric"><path d="M14 19V7a2 2 0 0 0-2-2H9"/><path d="M15 19H9"/><path d="M19 19h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.62L18.3 9.38a1 1 0 0 0-.78-.38H14"/><path d="M2 13v5a1 1 0 0 0 1 1h2"/><path d="M4 3 2.15 5.15a.495.495 0 0 0 .35.86h2.15a.47.47 0 0 1 .35.86L3 9.02"/><circle cx="17" cy="19" r="2"/><circle cx="7" cy="19" r="2"/></svg>
                </div>
            </div>
            @if($quotaLimit)
                <div class="flex items-center justify-between text-xs text-gray-400 dark:text-gray-500 mb-1.5">
                    <span>{{ $sentPct }}%</span>
                    <span>{{ number_format($quotaLimit) }} max</span>
                </div>
                <div class="h-1.5 rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden">
                    <div class="h-full rounded-full transition-all {{ $sentOverflow ? 'bg-red-500' : 'bg-amber-500' }}" style="width:{{ min(100,$sentPct) }}%"></div>
                </div>
            @else
                <p class="text-xs text-gray-400 dark:text-gray-500">This month &bull; Unlimited</p>
            @endif
        </div>
    </div>

    {{-- ── Row 2: KPI cards ─────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">

        {{-- Total Recipients --}}
        <div class="bg-white dark:bg-admin-card rounded-lg p-5 border border-gray-100 dark:border-admin-border shadow-sm">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Recipients</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1 leading-none">{{ number_format($totalSent) }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1.5">All-time sends</p>
                </div>
                <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center flex-shrink-0">
                    <svg class="text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-smile-plus-icon lucide-smile-plus"><path d="M22 11v1a10 10 0 1 1-9-10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" x2="9.01" y1="9" y2="9"/><line x1="15" x2="15.01" y1="9" y2="9"/><path d="M16 5h6"/><path d="M19 2v6"/></svg>
                </div>
            </div>
        </div>

        {{-- Avg Open Rate --}}
        <div class="bg-white dark:bg-admin-card rounded-lg p-5 border border-gray-100 dark:border-admin-border shadow-sm">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Avg. Open Rate</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1 leading-none">{{ $avgOpenRate }}%</p>
                    <p class="text-xs mt-1.5 {{ $avgOpenRate >= 20 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400' }}">
                        @if($avgOpenRate >= 20) Above industry avg @else Industry avg ~20% @endif
                    </p>
                </div>
                <div class="w-9 h-9 rounded-xl bg-violet-50 dark:bg-violet-900/20 flex items-center justify-center flex-shrink-0">
                    <svg  class="text-violet-600 dark:text-violet-400"  xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail-open-icon lucide-mail-open"><path d="M21.2 8.4c.5.38.8.97.8 1.6v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V10a2 2 0 0 1 .8-1.6l8-6a2 2 0 0 1 2.4 0l8 6Z"/><path d="m22 10-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 10"/></svg>
                </div>
            </div>
        </div>

        {{-- Click-to-Open --}}
        <div class="bg-white dark:bg-admin-card rounded-lg p-5 border border-gray-100 dark:border-admin-border shadow-sm">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Click-to-Open</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1 leading-none">{{ $clickToOpenRate }}%</p>
                    <p class="text-xs mt-1.5 {{ $clickToOpenRate >= 10 ? 'text-emerald-600 dark:text-emerald-400' : ($clickToOpenRate > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-400') }}">
                        @if($clickToOpenRate >= 10) Good engagement @elseif($clickToOpenRate > 0) Below avg (10%) @else No data yet @endif
                    </p>
                </div>
                <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center flex-shrink-0">
                    <svg class="text-amber-600 dark:text-amber-400" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mouse-pointer-click-icon lucide-mouse-pointer-click"><path d="M14 4.1 12 6"/><path d="m5.1 8-2.9-.8"/><path d="m6 12-1.9 2"/><path d="M7.2 2.2 8 5.1"/><path d="M9.037 9.69a.498.498 0 0 1 .653-.653l11 4.5a.5.5 0 0 1-.074.949l-4.349 1.041a1 1 0 0 0-.74.739l-1.04 4.35a.5.5 0 0 1-.95.074z"/></svg>
                </div>
            </div>
        </div>

        {{-- Unsubscribe Rate --}}
        <div class="bg-white dark:bg-admin-card rounded-lg p-5 border border-gray-100 dark:border-admin-border shadow-sm">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Unsubscribe Rate</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1 leading-none">{{ $unsubscribeRate }}%</p>
                    <p class="text-xs mt-1.5 {{ $unsubscribeRate < 0.5 ? 'text-emerald-600 dark:text-emerald-400' : ($unsubscribeRate < 1 ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400') }}">
                        @if($unsubscribeRate < 0.5) Excellent @elseif($unsubscribeRate < 1) Acceptable @else High — review content @endif
                    </p>
                </div>
                <div class="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-900/20 flex items-center justify-center flex-shrink-0">
                    
                    <svg  class="text-red-500 dark:text-red-400"  xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-round-x-icon lucide-user-round-x"><path d="M2 21a8 8 0 0 1 11.873-7"/><circle cx="10" cy="8" r="5"/><path d="m17 17 5 5"/><path d="m22 17-5 5"/></svg>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Row 3: Engagement chart + quick actions ──────────────────────────── --}}
    @php
        $actions = [
            ['href' => route('customer.campaigns.create'), 'label' => 'New Campaign',     'sub' => 'Create and send an email',     'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-truck-icon lucide-truck"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>'],
            ['href' => route('customer.lists.index'),     'label' => 'Import Contacts',   'sub' => 'Upload your subscriber list',  'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-round-plus-icon lucide-user-round-plus"><path d="M2 21a8 8 0 0 1 13.292-6"/><circle cx="10" cy="8" r="5"/><path d="M19 16v6"/><path d="M22 19h-6"/></svg>'],
            ['href' => route('customer.lists.create'),   'label' => 'Create List',        'sub' => 'Organize your contacts',       'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-list-plus-icon lucide-list-plus"><path d="M16 5H3"/><path d="M11 12H3"/><path d="M16 19H3"/><path d="M18 9v6"/><path d="M21 12h-6"/></svg>'],
            ['href' => route('customer.analytics.index'),'label' => 'View Reports',       'sub' => 'Check campaign performance',   'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chart-no-axes-column-icon lucide-chart-no-axes-column"><path d="M5 21v-6"/><path d="M12 21V3"/><path d="M19 21V9"/></svg>'],
        ];
    @endphp
    <div class="grid grid-cols-1 gap-5 items-stretch lg:grid-cols-12">
        <div class="bg-white dark:bg-admin-card rounded-lg p-6 border border-gray-100 dark:border-admin-border shadow-sm h-full flex flex-col lg:col-span-8 xl:col-span-9">
            <div class="flex flex-wrap items-start justify-between gap-3 mb-6">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Engagement Over Time</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Daily opens and clicks for all campaigns — last 7 days.</p>
                </div>
                <div class="flex items-center gap-5 text-xs text-gray-500 dark:text-gray-400">
                    <span class="flex items-center gap-1.5">
                        <span class="inline-block w-4 h-0.5 bg-blue-500 rounded-full"></span> Opens
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="inline-block w-4 h-0.5 bg-emerald-500 rounded-full" style="border-top:2px dashed #10b981;background:none"></span> Clicks
                    </span>
                </div>
            </div>
            <div class="relative h-56 sm:h-64 flex-1">
                <canvas id="engagementChart"></canvas>
            </div>
        </div>

        <div class="lg:col-span-4 xl:col-span-3">
            <div class="bg-white dark:bg-admin-card rounded-lg border border-gray-100 dark:border-admin-border shadow-sm divide-y divide-gray-100 dark:divide-admin-border h-full flex flex-col">
                @foreach($actions as $action)
                <a href="{{ $action['href'] }}" class="group flex items-center justify-between gap-3 p-4 hover:bg-gray-50 dark:hover:bg-gray-800/60 transition-colors flex-1">
                    <div class="flex items-start gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-gray-50 dark:bg-gray-800 flex items-center justify-center flex-shrink-0 group-hover:bg-blue-50 dark:group-hover:bg-blue-900/20 transition-colors">
                            <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $action['icon'] !!}</svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $action['label'] }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 leading-snug">{{ $action['sub'] }}</p>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Row 4: Top Campaigns + Subscriber Overview ───────────────────────── --}}
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">

        {{-- Top Campaigns --}}
        <div class="bg-white dark:bg-admin-card rounded-lg p-6 border border-gray-100 dark:border-admin-border shadow-sm">
            <div class="flex items-start justify-between mb-5">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Top Campaigns</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Best performing by open rate</p>
                </div>
                <a href="{{ route('customer.campaigns.index') }}" class="flex items-center gap-1 text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300">
                    View all <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
            @if($topCampaigns->isEmpty())
                <div class="py-10 text-center text-sm text-gray-400 dark:text-gray-500">No completed campaigns yet</div>
            @else
                <div class="space-y-4">
                    @foreach($topCampaigns as $i => $camp)
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-5 text-sm font-semibold text-gray-400 dark:text-gray-500 text-right flex-shrink-0">{{ $i + 1 }}</span>
                                <a href="{{ route('customer.campaigns.show', $camp) }}" class="text-sm font-medium text-gray-800 dark:text-gray-200 hover:text-blue-600 dark:hover:text-blue-400 truncate">{{ $camp->name }}</a>
                            </div>
                            <span class="flex-shrink-0 ml-3 text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($camp->open_rate, 1) }}% opens</span>
                        </div>
                        <div class="ml-7 h-1.5 rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden">
                            <div class="h-full rounded-full bg-emerald-400" style="width:{{ min(100, $camp->open_rate) }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Subscriber Overview --}}
        <div class="bg-white dark:bg-admin-card rounded-lg p-6 border border-gray-100 dark:border-admin-border shadow-sm">
            <div class="mb-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Subscriber Overview</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Contacts by status across all your lists.</p>
            </div>
            @php
                $subTotal = max(1, $subscribersCount);
                $subRows  = [
                    ['label' => 'Subscribed',   'count' => $subscribedCount,  'bar' => 'bg-emerald-500', 'dot' => 'bg-emerald-500'],
                    ['label' => 'Unconfirmed',  'count' => $unconfirmedCount, 'bar' => 'bg-green-400',   'dot' => 'bg-green-400'],
                    ['label' => 'Blacklisted',  'count' => $blacklistedCount, 'bar' => 'bg-violet-400',  'dot' => 'bg-violet-400'],
                ];
            @endphp
            <div class="space-y-4">
                @foreach($subRows as $row)
                @php $pct = $subTotal > 0 ? round(($row['count'] / $subTotal) * 100, 1) : 0; @endphp
                <div class="flex items-center gap-3">
                    <span class="w-2 h-2 rounded-full {{ $row['dot'] }} flex-shrink-0"></span>
                    <span class="text-sm text-gray-700 dark:text-gray-300 flex-shrink-0 w-24">{{ $row['label'] }}</span>
                    <div class="flex-1 h-2 rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden">
                        <div class="h-full rounded-full {{ $row['bar'] }}" style="width:{{ $pct }}%"></div>
                    </div>
                    <span class="text-sm text-gray-700 dark:text-gray-200 w-36 text-right flex-shrink-0">
                        {{ number_format($row['count']) }} <span class="text-gray-400 dark:text-gray-500">({{ $pct }}%)</span>
                    </span>
                </div>
                @endforeach
            </div>
            <div class="mt-5 pt-4 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between">
                <span class="text-sm text-gray-500 dark:text-gray-400">Total contacts</span>
                <span class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($subscribersCount) }}</span>
            </div>
        </div>
    </div>

    {{-- ── Row 6: Campaign Performance + Delivery Breakdown ────────────────── --}}
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-5">

        {{-- Campaign Performance table (3/5 width) --}}
        <div class="lg:col-span-3 bg-white dark:bg-admin-card rounded-lg p-6 border border-gray-100 dark:border-admin-border shadow-sm">
            <div class="flex items-start justify-between mb-5">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Campaign Performance</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Detailed metrics for recent sends.</p>
                </div>
                <a href="{{ route('customer.campaigns.index') }}" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 flex-shrink-0">View All</a>
            </div>
            @if($performanceCampaigns->isEmpty())
                <div class="py-10 text-center text-sm text-gray-400 dark:text-gray-500">No campaigns yet</div>
            @else
            <div class="overflow-x-auto -mx-1">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-700">
                            <th class="px-1 pb-2.5 text-left text-xs font-semibold text-gray-400 uppercase tracking-wide">Campaign</th>
                            <th class="px-1 pb-2.5 text-right text-xs font-semibold text-gray-400 uppercase tracking-wide">Sent</th>
                            <th class="px-1 pb-2.5 text-right text-xs font-semibold text-gray-400 uppercase tracking-wide">Open Rate</th>
                            <th class="px-1 pb-2.5 text-right text-xs font-semibold text-gray-400 uppercase tracking-wide">Click Rate</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800/80">
                        @foreach($performanceCampaigns as $camp)
                        <tr class="hover:bg-gray-50/70 dark:hover:bg-white/[0.03] transition-colors">
                            <td class="px-1 py-3">
                                <a href="{{ route('customer.campaigns.show', $camp) }}" class="font-medium text-gray-800 dark:text-gray-200 hover:text-blue-600 dark:hover:text-blue-400 line-clamp-1 block">{{ $camp->name }}</a>
                                <span class="text-xs text-gray-400 dark:text-gray-500">
                                    {{ $camp->started_at ? $camp->started_at->format('M j, Y') : ucfirst($camp->status) }}
                                </span>
                            </td>
                            <td class="px-1 py-3 text-right font-medium text-gray-700 dark:text-gray-300">{{ number_format($camp->sent_count) }}</td>
                            <td class="px-1 py-3 text-right">
                                <span class="inline-flex items-center gap-1 font-medium {{ $camp->open_rate >= 20 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-700 dark:text-gray-300' }}">
                                    {{ number_format($camp->open_rate, 1) }}%
                                    @if($camp->open_rate >= 20)
                                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 17l9.2-9.2M17 17V7H7"/></svg>
                                    @else
                                        <svg class="w-3.5 h-3.5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 7l-9.2 9.2M7 7v10h10"/></svg>
                                    @endif
                                </span>
                            </td>
                            <td class="px-1 py-3 text-right">
                                <span class="inline-flex items-center gap-1 font-medium {{ $camp->click_rate >= 3 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-700 dark:text-gray-300' }}">
                                    {{ number_format($camp->click_rate, 1) }}%
                                    @if($camp->click_rate >= 3)
                                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 17l9.2-9.2M17 17V7H7"/></svg>
                                    @else
                                        <svg class="w-3.5 h-3.5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 7l-9.2 9.2M7 7v10h10"/></svg>
                                    @endif
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- Right column: Delivery Breakdown + Sender Score (2/5 width) --}}
        <div class="lg:col-span-2 flex flex-col gap-4">

            {{-- Delivery Breakdown --}}
            <div class="bg-white dark:bg-admin-card rounded-lg p-6 border border-gray-100 dark:border-admin-border shadow-sm flex-1">
                <div class="mb-5">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Delivery Breakdown</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Status of all emails sent.</p>
                </div>
                @php
                    $deliveryRate  = $totalSent > 0 ? round(($totalDelivered / $totalSent) * 100, 1) : 0;
                    $bounceRatePct = $totalSent > 0 ? round(($totalBounced / $totalSent) * 100, 1) : 0;
                @endphp
                <div class="space-y-5">
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-800 dark:text-gray-200 leading-none">Delivered</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $deliveryRate }}% success rate</p>
                                </div>
                            </div>
                            <span class="text-base font-bold text-gray-900 dark:text-white">{{ number_format($totalDelivered) }}</span>
                        </div>
                        <div class="h-1.5 rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden">
                            <div class="h-full rounded-full bg-blue-500 transition-all" style="width:{{ $deliveryRate }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-800 dark:text-gray-200 leading-none">Bounced</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $bounceRatePct }}% bounce rate</p>
                                </div>
                            </div>
                            <span class="text-base font-bold text-gray-900 dark:text-white">{{ number_format($totalBounced) }}</span>
                        </div>
                        <div class="h-1.5 rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden">
                            <div class="h-full rounded-full bg-amber-500 transition-all" style="width:{{ min(100, $bounceRatePct * 20) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sender Score --}}
            @php
                $circumference = 2 * M_PI * 20;
                $dashOffset    = $circumference * (1 - $senderScore / 100);
                $scoreColor    = $senderScore >= 80 ? '#10b981' : ($senderScore >= 60 ? '#f59e0b' : '#ef4444');
                $scoreLabel    = $senderScore >= 80 ? 'Excellent reputation' : ($senderScore >= 60 ? 'Fair reputation' : 'Needs improvement');
                $scoreLabelCls = $senderScore >= 80 ? 'text-emerald-600 dark:text-emerald-400' : ($senderScore >= 60 ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400');
            @endphp
            <div class="bg-white dark:bg-admin-card rounded-lg p-6 border border-gray-100 dark:border-admin-border shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Sender Score</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $senderScore }}<span class="text-sm font-normal text-gray-400">/100</span></p>
                    <p class="text-xs mt-1 {{ $scoreLabelCls }}">{{ $scoreLabel }}</p>
                </div>
                <div class="relative w-14 h-14 flex-shrink-0">
                    <svg class="w-14 h-14 -rotate-90" viewBox="0 0 44 44">
                        <circle cx="22" cy="22" r="20" fill="none" stroke="currentColor" stroke-width="3.5" class="text-gray-100 dark:text-gray-700"/>
                        <circle cx="22" cy="22" r="20" fill="none" stroke="{{ $scoreColor }}" stroke-width="3.5" stroke-linecap="round"
                            stroke-dasharray="{{ round($circumference, 2) }}"
                            stroke-dashoffset="{{ round($dashOffset, 2) }}"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    var _chart = null;

    function initChart() {
        var canvas = document.getElementById('engagementChart');
        if (!canvas) return;
        if (_chart) { _chart.destroy(); _chart = null; }

        var isDark    = document.documentElement.classList.contains('dark');
        var gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';
        var tickColor = '#94a3b8';

        _chart = new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: @json($engagementDates),
                datasets: [
                    {
                        label: 'Opens',
                        data: @json($engagementOpens),
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59,130,246,0.07)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 4,
                        pointHoverBackgroundColor: '#3b82f6',
                    },
                    {
                        label: 'Clicks',
                        data: @json($engagementClicks),
                        borderColor: '#10b981',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        borderDash: [5, 4],
                        fill: false,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 4,
                        pointHoverBackgroundColor: '#10b981',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: tickColor, font: { size: 11 } },
                        border: { display: false }
                    },
                    y: {
                        grid: { color: gridColor },
                        ticks: { color: tickColor, font: { size: 11 } },
                        border: { display: false },
                        beginAtZero: true
                    }
                },
                interaction: { mode: 'nearest', axis: 'x', intersect: false }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', initChart);
    document.addEventListener('turbo:load', initChart);
    document.addEventListener('turbo:render', function () {
        if (_chart) { _chart.destroy(); _chart = null; }
    });
})();
</script>
@endpush

