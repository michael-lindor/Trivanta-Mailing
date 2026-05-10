@extends('layouts.customer')

@section('title', __('Scrape Results'))
@section('page-title', __('Scrape Results'))

@section('page-header')
<div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div class="min-w-0">
        <nav aria-label="Breadcrumb" class="mb-0">
            <ol class="flex flex-wrap items-center gap-1.5 text-[12px] text-admin-text-secondary">
                <li><a href="{{ route('customer.dashboard') }}" class="font-medium transition hover:text-admin-text-primary">{{ __('Dashboard') }}</a></li>
                <li aria-hidden="true" class="text-admin-text-secondary/60">/</li>
                <li><a href="{{ route('customer.scraper.index') }}" class="font-medium transition hover:text-admin-text-primary">{{ __('Lead Scraper') }}</a></li>
                <li aria-hidden="true" class="text-admin-text-secondary/60">/</li>
                <li class="font-medium text-admin-text-primary">{{ Str::limit($job->query, 40) }}</li>
            </ol>
        </nav>
        <div class="flex flex-wrap items-center gap-3 mt-1">
            <h1 class="text-[22px] font-semibold tracking-tight text-admin-text-primary">{{ $job->query }}</h1>
            <span class="px-2 py-0.5 text-xs font-medium bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-admin-text-secondary rounded-md">{{ $job->getTypeLabel() }}</span>
            @php $color = $job->getStatusBadgeColor(); @endphp
            <span class="px-2.5 py-1 text-xs font-medium rounded-full
                {{ $color === 'green' ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300' : '' }}
                {{ $color === 'red' ? 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300' : '' }}
                {{ $color === 'gray' ? 'bg-gray-100 text-gray-600 dark:bg-white/5 dark:text-admin-text-secondary' : '' }}
            ">{{ ucfirst($job->status) }}</span>
        </div>
        <p class="mt-1 text-sm text-admin-text-secondary">
            {{ number_format($job->records_count) }} {{ __('records extracted') }}
            @if($job->location) · {{ $job->location }}@endif
            · {{ $job->created_at->format('M d, Y') }}
        </p>
    </div>

    <div class="flex items-center gap-3 shrink-0">
        <a href="{{ route('customer.scraper.diagnosis', $job) }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 dark:text-admin-text-secondary bg-white dark:bg-admin-card border border-gray-200 dark:border-admin-border rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2v-4M9 21H5a2 2 0 01-2-2v-4m0 0h18"/></svg>
            {{ __('Diagnosis') }}
        </a>
        <a href="{{ route('customer.scraper.export', $job) }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 dark:text-admin-text-secondary bg-white dark:bg-admin-card border border-gray-200 dark:border-admin-border rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            {{ __('Export CSV') }}
        </a>

        @if($emailLists->isNotEmpty())
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" type="button"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-[#1E5FEA] hover:bg-blue-700 rounded-lg transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                {{ __('Push to Email List') }}
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>

            <div x-cloak x-show="open" @click.away="open = false"
                class="absolute right-0 mt-2 w-72 bg-white dark:bg-admin-card border border-gray-100 dark:border-admin-border rounded-xl shadow-lg z-30 p-3">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-admin-text-secondary mb-2">{{ __('Select email list') }}</p>
                <form method="POST" action="{{ route('customer.scraper.push', $job) }}">
                    @csrf
                    <div class="space-y-1 max-h-52 overflow-y-auto mb-3">
                        @foreach($emailLists as $list)
                            <label class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 cursor-pointer">
                                <input type="radio" name="email_list_id" value="{{ $list->id }}" required class="text-[#1E5FEA]">
                                <span class="text-sm text-admin-text-primary">{{ $list->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <button type="submit"
                        class="w-full px-3 py-2 text-sm font-semibold text-white bg-[#1E5FEA] hover:bg-blue-700 rounded-lg transition-colors">
                        {{ __('Push Leads') }}
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
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

    @if(session('error'))
        <div class="flex items-center gap-3 px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-sm text-red-800 dark:text-red-300">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white dark:bg-admin-card border border-gray-100 dark:border-admin-border rounded-xl">
        @if($leads->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-14 h-14 bg-gray-50 dark:bg-white/5 rounded-2xl flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                </div>
                <p class="text-sm font-semibold text-admin-text-primary">{{ __('No leads found') }}</p>
                <p class="mt-1 text-sm text-admin-text-secondary">{{ __('The scrape may still be running or returned no results.') }}</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-admin-border">
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-admin-text-secondary">#</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-admin-text-secondary">{{ __('Name') }}</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-admin-text-secondary">{{ __('Email') }}</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-admin-text-secondary">{{ __('Phone') }}</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-admin-text-secondary">{{ __('Website') }}</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-admin-text-secondary">{{ __('Address') }}</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-admin-text-secondary">{{ __('Rating') }}</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-admin-text-secondary">{{ __('Category') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-admin-border">
                        @foreach($leads as $lead)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                            <td class="px-5 py-3 text-xs text-gray-400">{{ $leads->firstItem() + $loop->index }}</td>
                            <td class="px-5 py-3">
                                <span class="font-medium text-admin-text-primary">{{ $lead->name ?: '—' }}</span>
                            </td>
                            <td class="px-5 py-3">
                                @if($lead->email)
                                    <a href="mailto:{{ $lead->email }}" class="text-[#1E5FEA] hover:underline">{{ $lead->email }}</a>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-admin-text-secondary">{{ $lead->phone ?: '—' }}</td>
                            <td class="px-5 py-3">
                                @if($lead->website)
                                    <a href="{{ $lead->website }}" target="_blank" rel="noopener"
                                       class="text-[#1E5FEA] hover:underline text-xs flex items-center gap-1 max-w-[160px] truncate">
                                        {{ Str::limit(str_replace(['https://','http://'], '', $lead->website), 30) }}
                                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-admin-text-secondary text-xs max-w-[200px]">
                                {{ $lead->address ? Str::limit($lead->address, 50) : '—' }}
                            </td>
                            <td class="px-5 py-3">
                                @if($lead->rating)
                                    <div class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        <span class="text-sm font-medium text-admin-text-primary">{{ number_format($lead->rating, 1) }}</span>
                                        @if($lead->reviews_count)
                                            <span class="text-xs text-gray-400">({{ number_format($lead->reviews_count) }})</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-admin-text-secondary text-xs">{{ $lead->category ?: '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($leads->hasPages())
                <div class="px-5 py-4 border-t border-gray-100 dark:border-admin-border">
                    {{ $leads->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
