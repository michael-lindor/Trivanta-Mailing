@extends('layouts.customer')

@section('title', __('Scraping Jobs'))
@section('page-title', __('Scraping Jobs'))

@section('page-header')
<div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div class="min-w-0">
        <nav aria-label="Breadcrumb" class="mb-0">
            <ol class="flex flex-wrap items-center gap-1.5 text-[12px] text-admin-text-secondary">
                <li><a href="{{ route('customer.dashboard') }}" class="font-medium transition hover:text-admin-text-primary">{{ __('Dashboard') }}</a></li>
                <li aria-hidden="true" class="text-admin-text-secondary/60">/</li>
                <li><a href="{{ route('customer.scraper.index') }}" class="font-medium transition hover:text-admin-text-primary">{{ __('Lead Scraper') }}</a></li>
                <li aria-hidden="true" class="text-admin-text-secondary/60">/</li>
                <li class="font-medium text-admin-text-primary">{{ __('All Jobs') }}</li>
            </ol>
        </nav>
        <h1 class="text-[22px] font-semibold tracking-tight text-admin-text-primary mt-1">{{ __('Scraping Jobs') }}</h1>
    </div>
    <a href="{{ route('customer.scraper.index') }}"
       class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-[#1E5FEA] hover:bg-blue-700 rounded-lg transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        {{ __('New Scrape') }}
    </a>
</div>
@endsection

@section('content')
<div class="space-y-4">

    @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-sm text-green-800 dark:text-green-300">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Filters --}}
    <form method="GET" action="{{ route('customer.scraper.jobs') }}" class="flex flex-wrap gap-3">
        <select name="type" onchange="this.form.submit()"
            class="px-3 py-2 text-sm border border-gray-200 dark:border-admin-border rounded-lg bg-white dark:bg-admin-card text-admin-text-primary focus:outline-none focus:ring-2 focus:ring-[#1E5FEA]/30">
            <option value="">{{ __('All Types') }}</option>
            <option value="maps" {{ request('type') === 'maps' ? 'selected' : '' }}>Maps</option>
            <option value="places" {{ request('type') === 'places' ? 'selected' : '' }}>Places</option>
            <option value="reviews" {{ request('type') === 'reviews' ? 'selected' : '' }}>Reviews</option>
            <option value="news" {{ request('type') === 'news' ? 'selected' : '' }}>News</option>
            <option value="images" {{ request('type') === 'images' ? 'selected' : '' }}>Images</option>
        </select>

        <select name="status" onchange="this.form.submit()"
            class="px-3 py-2 text-sm border border-gray-200 dark:border-admin-border rounded-lg bg-white dark:bg-admin-card text-admin-text-primary focus:outline-none focus:ring-2 focus:ring-[#1E5FEA]/30">
            <option value="">{{ __('All Statuses') }}</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="running" {{ request('status') === 'running' ? 'selected' : '' }}>Running</option>
            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
            <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
        </select>
    </form>

    <div class="bg-white dark:bg-admin-card border border-gray-100 dark:border-admin-border rounded-xl">
        @if($jobs->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-14 h-14 bg-gray-50 dark:bg-white/5 rounded-2xl flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <p class="text-sm font-semibold text-admin-text-primary">{{ __('No jobs found') }}</p>
                <p class="mt-1 text-sm text-admin-text-secondary">{{ __('Try adjusting the filters or start a new scraping job.') }}</p>
                <a href="{{ route('customer.scraper.index') }}" class="mt-4 px-4 py-2 text-sm font-semibold text-white bg-[#1E5FEA] hover:bg-blue-700 rounded-lg transition-colors">{{ __('Start Scraping') }}</a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-admin-border">
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-admin-text-secondary">{{ __('Query') }}</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-admin-text-secondary">{{ __('Type') }}</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-admin-text-secondary">{{ __('Status') }}</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-admin-text-secondary">{{ __('Records') }}</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-admin-text-secondary">{{ __('Credits Used') }}</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-admin-text-secondary">{{ __('Created') }}</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-admin-text-secondary">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-admin-border">
                        @foreach($jobs as $job)
                        @php
                            $colors = ['running' => 'blue', 'completed' => 'green', 'failed' => 'red', 'pending' => 'yellow'];
                            $color = $colors[$job->status] ?? 'gray';
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                            <td class="px-5 py-3">
                                <span class="font-medium text-admin-text-primary">{{ Str::limit($job->query, 50) }}</span>
                                @if($job->location)
                                    <span class="block text-xs text-admin-text-secondary">{{ $job->location }}</span>
                                @endif
                                @if($job->extract_emails)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium text-indigo-600 dark:text-indigo-400 mt-0.5">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                                        Email extraction
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-gray-500 dark:text-admin-text-secondary">{{ $job->getTypeLabel() }}</td>
                            <td class="px-5 py-3">
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
                                @if($job->isFailed() && $job->error_message)
                                    <p class="text-xs text-red-500 dark:text-red-400 mt-1">{{ Str::limit($job->error_message, 60) }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3 font-medium text-admin-text-primary">
                                {{ $job->records_count > 0 ? number_format($job->records_count) : '—' }}
                            </td>
                            <td class="px-5 py-3 text-admin-text-secondary">
                                {{ $job->credits_used > 0 ? $job->credits_used : '—' }}
                            </td>
                            <td class="px-5 py-3 text-admin-text-secondary text-xs">
                                <span title="{{ $job->created_at->format('M d, Y H:i') }}">{{ $job->created_at->diffForHumans() }}</span>
                                @if($job->completed_at)
                                    <span class="block text-gray-400 dark:text-gray-600">Done {{ $job->completed_at->diffForHumans() }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-1">
                                    @if($job->isCompleted())
                                        <a href="{{ route('customer.scraper.results', $job) }}"
                                           class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-[#1E5FEA] bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/40 rounded-md transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            {{ __('Results') }}
                                        </a>
                                        <a href="{{ route('customer.scraper.export', $job) }}"
                                           class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-gray-600 dark:text-admin-text-secondary bg-gray-100 dark:bg-white/5 hover:bg-gray-200 dark:hover:bg-white/10 rounded-md transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                            CSV
                                        </a>
                                    @endif
                                    <form method="POST" action="{{ route('customer.scraper.delete', $job) }}"
                                          onsubmit="return confirm('{{ __('Delete this job and its :count leads?', ['count' => $job->records_count]) }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/40 rounded-md transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($jobs->hasPages())
                <div class="px-5 py-4 border-t border-gray-100 dark:border-admin-border">
                    {{ $jobs->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
