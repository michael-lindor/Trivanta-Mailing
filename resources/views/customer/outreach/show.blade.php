@extends('layouts.customer')

@section('title', $campaign->name)
@section('page-title', $campaign->name)

@section('breadcrumbs')
    <nav aria-label="Breadcrumb" class="mb-0">
        <ol class="flex items-center gap-2 text-[12px] text-gray-500 dark:text-admin-text-secondary">
            <li><a href="{{ route('customer.outreach.index') }}" class="font-medium hover:text-[#1E5FEA] transition-colors">{{ __('Campaigns') }}</a></li>
            <li>/</li>
            <li class="text-gray-900 dark:text-admin-text-primary truncate max-w-xs">{{ $campaign->name }}</li>
        </ol>
    </nav>
@endsection

@section('page-title-meta')
    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full border flex-shrink-0 {{ $campaign->status_color }}">
        @if($campaign->status === 'active')<span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>@endif
        {{ ucfirst($campaign->status) }}
    </span>
@endsection

@section('page-actions')
    <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:flex-wrap sm:items-center sm:justify-end">
        @if($campaign->status === 'active')
            <form method="POST" action="{{ route('customer.outreach.campaigns.pause', $campaign) }}">
                @csrf
                <button type="submit" class="inline-flex w-full sm:w-auto items-center justify-center gap-2 px-3.5 py-2 text-sm font-medium text-gray-700 dark:text-admin-text-primary border border-gray-200 dark:border-admin-border bg-white dark:bg-white/5 hover:bg-gray-50 dark:hover:bg-white/10 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0 1 18 0z"/></svg>
                    {{ __('Pause Campaign') }}
                </button>
            </form>
        @elseif(in_array($campaign->status, ['paused', 'draft']))
            <form method="POST" action="{{ route('customer.outreach.campaigns.resume', $campaign) }}">
                @csrf
                <button type="submit" class="inline-flex w-full sm:w-auto items-center justify-center gap-2 px-3.5 py-2 text-sm font-semibold text-white bg-[#1E5FEA] hover:bg-blue-700 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 0 0 1.555.832l3.197-2.132a1 1 0 0 0 0-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg>
                    {{ __('Start Campaign') }}
                </button>
            </form>
        @endif

        @if($tab === 'analytics')
            <button type="button" class="inline-flex w-full sm:w-auto items-center justify-center gap-2 px-3.5 py-2 text-sm font-medium text-gray-700 dark:text-admin-text-primary border border-gray-200 dark:border-admin-border bg-white dark:bg-white/5 hover:bg-gray-50 dark:hover:bg-white/10 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z"/></svg>
                {{ __('Export Report') }}
            </button>
        @endif
        @if(in_array($tab, ['leads', 'sequences', 'schedule', 'options']))
            <button type="button" onclick="document.getElementById('save-form-{{ $tab }}').requestSubmit()"
                class="inline-flex w-full sm:w-auto items-center justify-center gap-2 px-3.5 py-2 text-sm font-semibold text-white bg-[#1E5FEA] hover:bg-blue-700 rounded-lg transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                {{ __('Save Changes') }}
            </button>
        @endif
    </div>
@endsection

@section('content')
<div class="space-y-5">

    {{-- Flash --}}
    @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-sm text-green-800 dark:text-green-300">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-sm text-red-800 dark:text-red-300">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Tabs --}}
    <div class="border-b border-gray-200 dark:border-admin-border -ml-6 pl-6">
        <nav class="-mb-px flex gap-1 overflow-x-auto">
            @php
                $tabs = [
                    'analytics' => ['label' => __('Analytics'), 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chart-no-axes-column-increasing-icon lucide-chart-no-axes-column-increasing"><path d="M5 21v-6"/><path d="M12 21V9"/><path d="M19 21V3"/></svg>'],
                    'leads'     => ['label' => __('Leads') . ' (' . number_format($campaign->leads_count) . ')', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users-icon lucide-users"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><path d="M16 3.128a4 4 0 0 1 0 7.744"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><circle cx="9" cy="7" r="4"/></svg>'],
                    'sequences' => ['label' => __('Sequences'), 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-list-ordered-icon lucide-list-ordered"><path d="M11 5h10"/><path d="M11 12h10"/><path d="M11 19h10"/><path d="M4 4h1v5"/><path d="M4 9h2"/><path d="M6.5 20H3.4c0-1 2.6-1.925 2.6-3.5a1.5 1.5 0 0 0-2.6-1.02"/></svg>'],
                    'schedule'  => ['label' => __('Schedule'), 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calendar-clock-icon lucide-calendar-clock"><path d="M16 14v2.2l1.6 1"/><path d="M16 2v4"/><path d="M21 7.5V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h3.5"/><path d="M3 10h5"/><path d="M8 2v4"/><circle cx="16" cy="16" r="6"/></svg>'],
                    'options'   => ['label' => __('Options'), 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-sliders-horizontal-icon lucide-sliders-horizontal"><path d="M10 5H3"/><path d="M12 19H3"/><path d="M14 3v4"/><path d="M16 17v4"/><path d="M21 12h-9"/><path d="M21 19h-5"/><path d="M21 5h-7"/><path d="M8 10v4"/><path d="M8 12H3"/></svg>'],
                    'logs'      => ['label' => __('Logs'), 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-cog-icon lucide-file-cog"><path d="M15 8a1 1 0 0 1-1-1V2a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8z"/><path d="M20 8v12a2 2 0 0 1-2 2h-4.182"/><path d="m3.305 19.53.923-.382"/><path d="M4 10.592V4a2 2 0 0 1 2-2h8"/><path d="m4.228 16.852-.924-.383"/><path d="m5.852 15.228-.383-.923"/><path d="m5.852 20.772-.383.924"/><path d="m8.148 15.228.383-.923"/><path d="m8.53 21.696-.382-.924"/><path d="m9.773 16.852.922-.383"/><path d="m9.773 19.148.922.383"/><circle cx="7" cy="18" r="3"/></svg>'],
                ];
            @endphp
            @foreach($tabs as $key => $meta)
                <a href="{{ route('customer.outreach.campaigns.show', [$campaign, 'tab' => $key]) }}"
                   class="flex items-center gap-2 px-0 py-3 mr-6 text-sm font-medium border-b-2 whitespace-nowrap transition-colors {{ $tab === $key ? '' : '!border-transparent text-gray-500 dark:text-admin-text-secondary hover:text-gray-700 dark:hover:text-admin-text-primary hover:border-gray-300' }}"
                   @if($tab === $key) style="border-color: var(--tw-brand, #1E5FEA); color: var(--tw-brand, #1E5FEA);" @endif>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $meta['icon'] !!}</svg>
                    {{ $meta['label'] }}
                </a>
            @endforeach
        </nav>
    </div>

    {{-- Tab Content --}}
    @if(in_array($tab, ['sequences', 'schedule', 'options']))
        <div class="w-3/5 mx-auto">
            @include('customer.outreach.tabs.' . $tab)
        </div>
    @else
        @include('customer.outreach.tabs.' . $tab)
    @endif

</div>
@endsection
