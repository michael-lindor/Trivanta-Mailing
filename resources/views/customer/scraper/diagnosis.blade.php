@extends('layouts.customer')

@section('title', __('Scrape Diagnosis'))
@section('page-title', __('Scrape Diagnosis'))

@section('page-header')
<div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div class="min-w-0">
        <nav aria-label="Breadcrumb" class="mb-0">
            <ol class="flex flex-wrap items-center gap-1.5 text-[12px] text-admin-text-secondary">
                <li><a href="{{ route('customer.dashboard') }}" class="font-medium transition hover:text-admin-text-primary">{{ __('Dashboard') }}</a></li>
                <li aria-hidden="true" class="text-admin-text-secondary/60">/</li>
                <li><a href="{{ route('customer.scraper.index') }}" class="font-medium transition hover:text-admin-text-primary">{{ __('Lead Scraper') }}</a></li>
                <li aria-hidden="true" class="text-admin-text-secondary/60">/</li>
                <li><a href="{{ route('customer.scraper.results', $job) }}" class="font-medium transition hover:text-admin-text-primary">{{ Str::limit($job->query, 40) }}</a></li>
                <li aria-hidden="true" class="text-admin-text-secondary/60">/</li>
                <li class="font-medium text-admin-text-primary">{{ __('Diagnosis') }}</li>
            </ol>
        </nav>
        <div class="flex flex-wrap items-center gap-3 mt-1">
            <h1 class="text-[22px] font-semibold tracking-tight text-admin-text-primary">{{ __('Scrape Diagnosis') }}</h1>
            <span class="px-2 py-0.5 text-xs font-medium bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-admin-text-secondary rounded-md">{{ $job->getTypeLabel() }}</span>
            @php $color = $job->getStatusBadgeColor(); @endphp
            <span class="px-2.5 py-1 text-xs font-medium rounded-full
                {{ $color === 'green' ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300' : '' }}
                {{ $color === 'red' ? 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300' : '' }}
                {{ $color === 'gray' ? 'bg-gray-100 text-gray-600 dark:bg-white/5 dark:text-admin-text-secondary' : '' }}
            ">{{ ucfirst($job->status) }}</span>
        </div>
        <p class="mt-1 text-sm text-admin-text-secondary">{{ $job->query }}@if($job->location) · {{ $job->location }}@endif · {{ $job->created_at->format('M d, Y H:i') }}</p>
    </div>

    <div class="flex items-center gap-3 shrink-0">
        <a href="{{ route('customer.scraper.results', $job) }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 dark:text-admin-text-secondary bg-white dark:bg-admin-card border border-gray-200 dark:border-admin-border rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            {{ __('Back to Results') }}
        </a>
    </div>
</div>
@endsection

@section('content')
@php $debugData = $job->debug_data ?? []; @endphp
<div class="space-y-4">

    @if($job->error_message)
        <div class="flex items-start gap-3 px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-sm text-red-800 dark:text-red-300">
            <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ $job->error_message }}</span>
        </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
        <div class="bg-white dark:bg-admin-card border border-gray-100 dark:border-admin-border rounded-xl p-4">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-admin-text-secondary">{{ __('Raw Results') }}</div>
            <div class="mt-2 text-3xl font-semibold text-admin-text-primary">{{ number_format((int) ($debugData['raw_results_count'] ?? 0)) }}</div>
            <div class="mt-1 text-xs text-admin-text-secondary">{{ __('Returned by API') }}</div>
        </div>
        <div class="bg-white dark:bg-admin-card border border-gray-100 dark:border-admin-border rounded-xl p-4">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-admin-text-secondary">{{ __('Normalized') }}</div>
            <div class="mt-2 text-3xl font-semibold text-admin-text-primary">{{ number_format((int) ($debugData['normalized_results_count'] ?? 0)) }}</div>
            <div class="mt-1 text-xs text-admin-text-secondary">{{ __('Processed for storage') }}</div>
        </div>
        <div class="bg-white dark:bg-admin-card border border-gray-100 dark:border-admin-border rounded-xl p-4">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-admin-text-secondary">{{ __('Records Saved') }}</div>
            <div class="mt-2 text-3xl font-semibold text-admin-text-primary">{{ number_format($job->records_count) }}</div>
            <div class="mt-1 text-xs text-admin-text-secondary">{{ __('In database') }}</div>
        </div>
        <div class="bg-white dark:bg-admin-card border border-gray-100 dark:border-admin-border rounded-xl p-4">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-admin-text-secondary">{{ __('Credits Used') }}</div>
            <div class="mt-2 text-3xl font-semibold text-admin-text-primary">{{ number_format($job->credits_used) }}</div>
            <div class="mt-1 text-xs text-admin-text-secondary">{{ __('Deducted from balance') }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

        {{-- Request context --}}
        <div class="bg-white dark:bg-admin-card border border-gray-100 dark:border-admin-border rounded-xl p-5">
            <h2 class="text-sm font-semibold text-admin-text-primary mb-3">{{ __('Request Context') }}</h2>
            <dl class="space-y-2.5 text-sm">
                <div class="flex justify-between gap-3 border-b border-gray-50 dark:border-admin-border pb-2">
                    <dt class="text-admin-text-secondary">{{ __('Type') }}</dt>
                    <dd class="text-admin-text-primary font-medium text-right">{{ $debugData['type'] ?? $job->type }}</dd>
                </div>
                <div class="flex justify-between gap-3 border-b border-gray-50 dark:border-admin-border pb-2">
                    <dt class="text-admin-text-secondary">{{ __('Query') }}</dt>
                    <dd class="text-admin-text-primary font-medium text-right">{{ $debugData['query'] ?? $job->query }}</dd>
                </div>
                @if(!empty($debugData['api_query']) && $debugData['api_query'] !== ($debugData['query'] ?? ''))
                <div class="flex justify-between gap-3 border-b border-gray-50 dark:border-admin-border pb-2">
                    <dt class="text-admin-text-secondary">{{ __('API Query Sent') }}</dt>
                    <dd class="text-[#1E5FEA] font-medium text-right">{{ $debugData['api_query'] }}</dd>
                </div>
                @endif
                <div class="flex justify-between gap-3 border-b border-gray-50 dark:border-admin-border pb-2">
                    <dt class="text-admin-text-secondary">{{ __('Location') }}</dt>
                    <dd class="text-admin-text-primary font-medium text-right">{{ $debugData['location'] ?? $job->location ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-3 border-b border-gray-50 dark:border-admin-border pb-2">
                    <dt class="text-admin-text-secondary">{{ __('Language') }}</dt>
                    <dd class="text-admin-text-primary font-medium text-right">{{ $debugData['language'] ?? $job->language }}</dd>
                </div>
                <div class="flex justify-between gap-3 border-b border-gray-50 dark:border-admin-border pb-2">
                    <dt class="text-admin-text-secondary">{{ __('Max Results') }}</dt>
                    <dd class="text-admin-text-primary font-medium text-right">{{ $debugData['max_results'] ?? $job->max_results }}</dd>
                </div>
                <div class="flex justify-between gap-3 border-b border-gray-50 dark:border-admin-border pb-2">
                    <dt class="text-admin-text-secondary">{{ __('Extract Emails') }}</dt>
                    <dd class="text-admin-text-primary font-medium text-right">{{ !empty($debugData['extract_emails']) ? __('Yes') : __('No') }}</dd>
                </div>
                @if(!empty($debugData['started_at']))
                <div class="flex justify-between gap-3 border-b border-gray-50 dark:border-admin-border pb-2">
                    <dt class="text-admin-text-secondary">{{ __('Started') }}</dt>
                    <dd class="text-admin-text-primary font-medium text-right">{{ $debugData['started_at'] }}</dd>
                </div>
                @endif
                @if(!empty($debugData['completed_at']))
                <div class="flex justify-between gap-3">
                    <dt class="text-admin-text-secondary">{{ __('Completed') }}</dt>
                    <dd class="text-admin-text-primary font-medium text-right">{{ $debugData['completed_at'] }}</dd>
                </div>
                @endif
                @if(!empty($debugData['failed_at']))
                <div class="flex justify-between gap-3">
                    <dt class="text-admin-text-secondary">{{ __('Failed At') }}</dt>
                    <dd class="text-red-600 dark:text-red-400 font-medium text-right">{{ $debugData['failed_at'] }}</dd>
                </div>
                @endif
            </dl>
            @if(!empty($debugData['message']))
                <div class="mt-4 rounded-lg bg-gray-50 dark:bg-white/5 px-3 py-2 text-xs text-admin-text-secondary">
                    {{ $debugData['message'] }}
                </div>
            @endif
        </div>

        {{-- Exception / samples --}}
        <div class="bg-white dark:bg-admin-card border border-gray-100 dark:border-admin-border rounded-xl p-5 space-y-4">
            @if(!empty($debugData['exception_message']))
                <div>
                    <h2 class="text-sm font-semibold text-red-600 dark:text-red-400 mb-2">{{ __('Exception') }}</h2>
                    <pre class="max-h-40 overflow-auto rounded-lg bg-red-50 dark:bg-red-900/20 p-3 text-xs text-red-800 dark:text-red-300 whitespace-pre-wrap">{{ ($debugData['exception_class'] ?? '') }}: {{ $debugData['exception_message'] }}</pre>
                </div>
            @endif

            <div>
                <h2 class="text-sm font-semibold text-admin-text-primary mb-2">{{ __('Raw API Result Sample') }}</h2>
                <pre class="max-h-64 overflow-auto rounded-lg bg-gray-50 dark:bg-white/5 p-3 text-xs text-admin-text-primary whitespace-pre-wrap">{{ json_encode($debugData['raw_result_sample'] ?? null, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: 'null' }}</pre>
            </div>

            <div>
                <h2 class="text-sm font-semibold text-admin-text-primary mb-2">{{ __('Normalized Result Sample') }}</h2>
                <pre class="max-h-64 overflow-auto rounded-lg bg-gray-50 dark:bg-white/5 p-3 text-xs text-admin-text-primary whitespace-pre-wrap">{{ json_encode($debugData['normalized_result_sample'] ?? null, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: 'null' }}</pre>
            </div>
        </div>
    </div>

</div>
@endsection
