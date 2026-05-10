@extends('layouts.customer')

@section('title', __('SuperScrape API Settings'))
@section('page-title', __('SuperScrape API Settings'))

@section('page-header')
<div class="mb-6">
    <nav aria-label="Breadcrumb" class="mb-0">
        <ol class="flex flex-wrap items-center gap-1.5 text-[12px] text-admin-text-secondary">
            <li><a href="{{ route('customer.dashboard') }}" class="font-medium transition hover:text-admin-text-primary">{{ __('Dashboard') }}</a></li>
            <li aria-hidden="true" class="text-admin-text-secondary/60">/</li>
            <li><a href="{{ route('customer.scraper.index') }}" class="font-medium transition hover:text-admin-text-primary">{{ __('Lead Scraper') }}</a></li>
            <li aria-hidden="true" class="text-admin-text-secondary/60">/</li>
            <li class="font-medium text-admin-text-primary">{{ __('API Settings') }}</li>
        </ol>
    </nav>
    <h1 class="text-[22px] font-semibold tracking-tight text-admin-text-primary mt-1">{{ __('API Settings') }}</h1>
    <p class="text-sm text-admin-text-secondary mt-0.5">{{ __('API credentials are managed by the administrator. Contact support to request changes.') }}</p>
</div>
@endsection

@section('content')
<div class="max-w-xl space-y-5">

    <div class="bg-white dark:bg-admin-card border border-gray-100 dark:border-admin-border rounded-xl p-6 space-y-4">
        <div class="flex items-center gap-3 pb-4 border-b border-gray-100 dark:border-admin-border">
            <div class="w-8 h-8 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-admin-text-primary">{{ __('API Configuration') }}</p>
                <p class="text-xs text-admin-text-secondary">{{ __('Current API status for your account') }}</p>
            </div>
        </div>

        <div class="space-y-3">
            <div class="flex items-center justify-between py-2">
                <div>
                    <p class="text-sm font-medium text-admin-text-primary">SerpAPI</p>
                    <p class="text-xs text-admin-text-secondary">{{ __('Powers Maps, Places, Reviews & Images scraping') }}</p>
                </div>
                @if($serpApiConfigured)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-medium bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 rounded-full border border-green-200 dark:border-green-800">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                        {{ __('Configured') }}
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-medium bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-full border border-red-200 dark:border-red-800">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                        {{ __('Not configured') }}
                    </span>
                @endif
            </div>

            <div class="h-px bg-gray-100 dark:bg-admin-border"></div>

            <div class="flex items-center justify-between py-2">
                <div>
                    <p class="text-sm font-medium text-admin-text-primary">Serper.dev</p>
                    <p class="text-xs text-admin-text-secondary">{{ __('Powers Google News scraping') }}</p>
                </div>
                @if($serperConfigured)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-medium bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 rounded-full border border-green-200 dark:border-green-800">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                        {{ __('Configured') }}
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-medium bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-full border border-red-200 dark:border-red-800">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                        {{ __('Not configured') }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4 text-sm text-amber-700 dark:text-amber-300">
        <p class="font-medium">{{ __('Note') }}</p>
        <p class="mt-1">{{ __('API keys are managed by your administrator in the admin panel. Contact support if you need changes.') }}</p>
    </div>

    <a href="{{ route('customer.scraper.index') }}"
       class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 dark:text-admin-text-secondary bg-white dark:bg-admin-card border border-gray-200 dark:border-admin-border rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        {{ __('Back to Lead Scraper') }}
    </a>
</div>
@endsection
