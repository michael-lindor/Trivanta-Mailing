@extends('layouts.customer')

@section('title', __('Lead Scraper'))
@section('page-title', __('Lead Scraper'))

@section('page-header')
<div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div class="min-w-0">
        <nav aria-label="Breadcrumb" class="mb-0">
            <ol class="flex flex-wrap items-center gap-1.5 text-[12px] text-admin-text-secondary">
                <li>
                    <a href="{{ route('customer.dashboard') }}" class="font-medium transition hover:text-admin-text-primary">{{ __('Dashboard') }}</a>
                </li>
                <li aria-hidden="true" class="text-admin-text-secondary/60">/</li>
                <li class="font-medium text-admin-text-primary">{{ __('Lead Scraper') }}</li>
            </ol>
        </nav>
        <div class="flex flex-wrap items-center gap-3 min-w-0">
            <h1 class="text-[22px] font-semibold tracking-tight text-admin-text-primary">{{ __('SuperScrape — Google Services') }}</h1>
        </div>
        <p class="mt-1 text-sm text-admin-text-secondary">{{ __('Extract rich data from Maps, Places, Reviews, News, and more.') }}</p>
    </div>

    <div class="flex items-center gap-3 shrink-0">
        <a href="{{ route('customer.scraper.settings') }}"
           class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 dark:text-admin-text-secondary bg-white dark:bg-admin-card border border-gray-200 dark:border-admin-border rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            {{ __('API Settings') }}
        </a>
        <span class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-800 rounded-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.5"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2"/></svg>
            {{ number_format($creditsLeft) }} {{ __('Credits Left') }}
        </span>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6" x-data="scraperApp()" x-init="init()">

    @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-sm text-green-800 dark:text-green-300">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="flex items-center gap-3 px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-sm text-red-800 dark:text-red-300">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('error') }}
        </div>
    @endif

    @if(!$serpApiConfigured && !$serperConfigured)
        <div class="flex items-start gap-3 px-4 py-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
            <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
            <div>
                <p class="text-sm font-medium text-amber-800 dark:text-amber-200">{{ __('API Keys Not Configured') }}</p>
                <p class="text-sm text-amber-700 dark:text-amber-300 mt-0.5">{{ __('SerpAPI and Serper.dev keys are not set. Contact your administrator to configure them.') }}</p>
            </div>
        </div>
    @endif

    {{-- Search Panel --}}
    <div class="bg-white dark:bg-admin-card border border-gray-100 dark:border-admin-border rounded-xl p-5 space-y-4">

        {{-- Platform Tabs --}}
        <div class="flex flex-wrap gap-1">
            {{-- Google Sources (functional) --}}
            @php $googleTabs = ['maps' => 'Maps', 'places' => 'Places', 'reviews' => 'Reviews', 'news' => 'News', 'images' => 'Images']; @endphp
            @foreach($googleTabs as $tabKey => $tabLabel)
                <button type="button"
                    @click="activeTab = '{{ $tabKey }}'"
                    :class="activeTab === '{{ $tabKey }}' ? 'bg-white dark:bg-admin-card shadow-sm border-gray-200 dark:border-admin-border text-[#1E5FEA] font-medium' : 'text-gray-500 dark:text-admin-text-secondary hover:text-gray-700 dark:hover:text-admin-text-primary border-transparent'"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-md border transition-all">
                    @if($tabKey === 'maps')
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                    @elseif($tabKey === 'places')
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    @elseif($tabKey === 'reviews')
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    @elseif($tabKey === 'news')
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                    @else
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9l4-4 4 4 4-4 4 4"/></svg>
                    @endif
                    {{ __($tabLabel) }}
                </button>
            @endforeach

        </div>

        {{-- Search Form --}}
        <form method="POST" action="{{ route('customer.scraper.start') }}" x-ref="scraperForm">
            @csrf
            <input type="hidden" name="type" :value="activeTab">

            <div class="flex gap-3">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input
                        type="text"
                        name="query"
                        placeholder="{{ __('e.g. Coffee roasters in Portland') }}"
                        required
                        class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-200 dark:border-admin-border rounded-lg bg-white dark:bg-admin-main text-admin-text-primary placeholder-gray-400 dark:placeholder-admin-text-secondary focus:outline-none focus:ring-2 focus:ring-[#1E5FEA]/30 focus:border-[#1E5FEA]"
                    >
                </div>
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-[#1E5FEA] hover:bg-blue-700 rounded-lg transition-colors shadow-sm whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3" fill="currentColor"/></svg>
                    {{ __('Start Scrape') }}
                </button>
            </div>

            {{-- Filters --}}
            <div class="mt-3 grid grid-cols-2 lg:grid-cols-4 gap-3">
                <div>
                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-admin-text-secondary mb-1">{{ __('Location / Region') }}</label>
                    <select name="location" class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-admin-border rounded-lg bg-white dark:bg-admin-main text-admin-text-primary focus:outline-none focus:ring-2 focus:ring-[#1E5FEA]/30">
                        <option value="">{{ __('Any location') }}</option>
                        <option value="New York, NY, USA">New York, USA</option>
                        <option value="Los Angeles, CA, USA">Los Angeles, USA</option>
                        <option value="London, UK">London, UK</option>
                        <option value="Portland, Oregon, USA">Portland, USA</option>
                        <option value="Chicago, IL, USA">Chicago, USA</option>
                        <option value="Toronto, Canada">Toronto, Canada</option>
                        <option value="Sydney, Australia">Sydney, Australia</option>
                        <option value="Dubai, UAE">Dubai, UAE</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-admin-text-secondary mb-1">{{ __('Language') }}</label>
                    <select name="language" class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-admin-border rounded-lg bg-white dark:bg-admin-main text-admin-text-primary focus:outline-none focus:ring-2 focus:ring-[#1E5FEA]/30">
                        <option value="en">English (US)</option>
                        <option value="es">Spanish</option>
                        <option value="fr">French</option>
                        <option value="de">German</option>
                        <option value="ar">Arabic</option>
                        <option value="pt">Portuguese</option>
                        <option value="it">Italian</option>
                        <option value="nl">Dutch</option>
                        <option value="ja">Japanese</option>
                        <option value="zh-cn">Chinese (Simplified)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-admin-text-secondary mb-1">{{ __('Max Results') }}</label>
                    <select name="max_results" class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-admin-border rounded-lg bg-white dark:bg-admin-main text-admin-text-primary focus:outline-none focus:ring-2 focus:ring-[#1E5FEA]/30">
                        <option value="20">20 (2 Credits)</option>
                        <option value="50">50 (5 Credits)</option>
                        <option value="100" selected>100 (10 Credits)</option>
                        <option value="200">200 (20 Credits)</option>
                        <option value="500">500 (50 Credits)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-admin-text-secondary mb-1">{{ __('Extract Emails & Socials') }}</label>
                    <label class="flex items-center gap-2 mt-2 cursor-pointer">
                        <input type="hidden" name="extract_emails" value="0">
                        <input type="checkbox" name="extract_emails" value="1"
                            class="w-10 h-5 appearance-none bg-gray-200 dark:bg-gray-700 rounded-full relative cursor-pointer transition-colors checked:bg-[#1E5FEA]
                            before:content-[''] before:absolute before:top-0.5 before:left-0.5 before:w-4 before:h-4 before:bg-white before:rounded-full before:transition-transform checked:before:translate-x-5">
                        <span class="text-sm text-gray-600 dark:text-admin-text-secondary">{{ __('Enabled (+0.5 Cr/Record)') }}</span>
                    </label>
                </div>
            </div>
        </form>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-admin-card border border-gray-100 dark:border-admin-border rounded-xl p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-8 h-8 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                </div>
                <span class="text-sm text-gray-500 dark:text-admin-text-secondary">{{ __('Total Records Extracted') }}</span>
            </div>
            <p class="text-2xl font-bold text-admin-text-primary">{{ number_format($totalLeads) }}</p>
        </div>

        <div class="bg-white dark:bg-admin-card border border-gray-100 dark:border-admin-border rounded-xl p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-8 h-8 bg-blue-50 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <span class="text-sm text-gray-500 dark:text-admin-text-secondary">{{ __('Active Jobs') }}</span>
            </div>
            <p class="text-2xl font-bold text-admin-text-primary">{{ $activeJobs }}</p>
            <p class="text-xs text-gray-400 dark:text-admin-text-secondary mt-1">{{ __('In queue / running') }}</p>
        </div>

        <div class="bg-white dark:bg-admin-card border border-gray-100 dark:border-admin-border rounded-xl p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-8 h-8 bg-green-50 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2"/></svg>
                </div>
                <span class="text-sm text-gray-500 dark:text-admin-text-secondary">{{ __('Top Source') }} ({{ $topSourceLabel }})</span>
            </div>
            <p class="text-2xl font-bold text-admin-text-primary">{{ $topSourcePercent }}%</p>
            <p class="text-xs text-gray-400 dark:text-admin-text-secondary mt-1">{{ __('Of all extracted records') }}</p>
        </div>
    </div>

    {{-- Recent Jobs --}}
    <div class="bg-white dark:bg-admin-card border border-gray-100 dark:border-admin-border rounded-xl">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-admin-border">
            <div>
                <h2 class="text-sm font-semibold text-admin-text-primary">{{ __('Recent Scraping Jobs') }}</h2>
                <p class="text-xs text-admin-text-secondary mt-0.5">{{ __('Monitor and download your extracted data.') }}</p>
            </div>
            <a href="{{ route('customer.scraper.jobs') }}" class="text-sm font-medium text-[#1E5FEA] hover:underline flex items-center gap-1">
                {{ __('View all jobs') }}
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        @if($recentJobs->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <div class="w-14 h-14 bg-gray-50 dark:bg-white/5 rounded-2xl flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <p class="text-sm font-medium text-admin-text-primary">{{ __('No scraping jobs yet') }}</p>
                <p class="mt-1 text-sm text-admin-text-secondary">{{ __('Enter a query above and click Start Scrape.') }}</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-admin-border">
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-admin-text-secondary">{{ __('Query / Target') }}</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-admin-text-secondary">{{ __('Type') }}</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-admin-text-secondary">{{ __('Status') }}</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-admin-text-secondary">{{ __('Records') }}</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-admin-text-secondary">{{ __('Date') }}</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-admin-text-secondary">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-admin-border">
                        @foreach($recentJobs as $job)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors" id="job-row-{{ $job->id }}">
                            <td class="px-5 py-3">
                                <a href="{{ route('customer.scraper.results', $job) }}" class="block group">
                                    <span class="font-medium text-admin-text-primary group-hover:text-[#1E5FEA] transition-colors">{{ Str::limit($job->query, 40) }}</span>
                                    @if($job->location)
                                        <span class="block text-xs text-admin-text-secondary group-hover:text-[#1E5FEA] transition-colors">{{ $job->location }}</span>
                                    @endif
                                </a>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-1.5 text-gray-500 dark:text-admin-text-secondary">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                                    {{ $job->getTypeLabel() }}
                                </div>
                            </td>
                            <td class="px-5 py-3" data-job-status="{{ $job->id }}">
                                @php
                                    $colors = ['running' => 'blue', 'completed' => 'green', 'failed' => 'red', 'pending' => 'yellow'];
                                    $color = $colors[$job->status] ?? 'gray';
                                @endphp
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-full
                                    {{ $color === 'blue' ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' : '' }}
                                    {{ $color === 'green' ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300' : '' }}
                                    {{ $color === 'red' ? 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300' : '' }}
                                    {{ $color === 'yellow' ? 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300' : '' }}
                                    {{ $color === 'gray' ? 'bg-gray-100 text-gray-600 dark:bg-white/5 dark:text-admin-text-secondary' : '' }}
                                ">
                                    @if($job->status === 'running' || $job->status === 'pending')
                                        <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                    @endif
                                    {{ ucfirst($job->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-admin-text-primary" data-job-count="{{ $job->id }}">
                                {{ $job->records_count > 0 ? number_format($job->records_count) : '—' }}
                            </td>
                            <td class="px-5 py-3 text-admin-text-secondary text-xs">
                                {{ $job->created_at->diffForHumans() }}
                            </td>
                            <td class="px-5 py-3">
                                <div x-data="{ open: false }" class="relative inline-block">
                                    <button @click="open = !open" type="button"
                                        class="p-1.5 rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-admin-text-primary hover:bg-gray-100 dark:hover:bg-white/10 transition-colors">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                                    </button>
                                    <div x-cloak x-show="open" @click.away="open = false"
                                        class="absolute right-0 mt-1 w-40 bg-white dark:bg-admin-card border border-gray-100 dark:border-admin-border rounded-lg shadow-lg z-20 py-1">
                                        @if($job->isCompleted())
                                            <a href="{{ route('customer.scraper.results', $job) }}"
                                               class="flex items-center gap-2 px-3 py-2 text-sm text-admin-text-primary hover:bg-gray-50 dark:hover:bg-white/5">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                {{ __('View Results') }}
                                            </a>
                                            <a href="{{ route('customer.scraper.export', $job) }}"
                                               class="flex items-center gap-2 px-3 py-2 text-sm text-admin-text-primary hover:bg-gray-50 dark:hover:bg-white/5">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                {{ __('Export CSV') }}
                                            </a>
                                        @endif
                                        <form method="POST" action="{{ route('customer.scraper.delete', $job) }}"
                                              onsubmit="return confirm('{{ __('Delete this job and all its leads?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="flex items-center gap-2 w-full px-3 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                {{ __('Delete') }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function scraperApp() {
    return {
        activeTab: 'maps',
        init() {
            this.pollActiveJobs();
        },
        pollActiveJobs() {
            const runningRows = document.querySelectorAll('[data-job-status]');
            runningRows.forEach(cell => {
                const jobId = cell.dataset.jobStatus;
                const statusText = cell.querySelector('span')?.textContent?.trim().toLowerCase();
                if (statusText === 'running' || statusText === 'pending') {
                    this.pollJob(jobId);
                }
            });
        },
        pollJob(jobId) {
            const poll = () => {
                fetch(`{{ url('customer/scraper/jobs') }}/${jobId}/status`)
                    .then(r => r.json())
                    .then(data => {
                        const statusCell = document.querySelector(`[data-job-status="${jobId}"]`);
                        const countCell  = document.querySelector(`[data-job-count="${jobId}"]`);
                        if (statusCell) {
                            const span = statusCell.querySelector('span');
                            if (span) {
                                span.textContent = data.status.charAt(0).toUpperCase() + data.status.slice(1);
                                span.className = span.className.replace(/bg-\S+ text-\S+/g, '');
                                if (data.status === 'completed') span.classList.add('bg-green-50', 'text-green-700');
                                if (data.status === 'failed') span.classList.add('bg-red-50', 'text-red-700');
                            }
                        }
                        if (countCell && data.records_count > 0) {
                            countCell.textContent = data.records_count.toLocaleString();
                        }
                        if (data.status === 'running' || data.status === 'pending') {
                            setTimeout(poll, 3000);
                        }
                    })
                    .catch(() => {});
            };
            setTimeout(poll, 3000);
        }
    };
}
</script>
@endpush
@endsection
