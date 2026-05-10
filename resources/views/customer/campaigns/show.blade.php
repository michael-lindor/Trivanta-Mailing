@extends('layouts.customer')

@section('title', $campaign->name)
@section('page-title', $campaign->name)

@php
    $statusColors = [
        'completed' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
        'running'   => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
        'queued'    => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300',
        'paused'    => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300',
        'failed'    => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
        'draft'     => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
        'scheduled' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
    ];
    $statusDots = [
        'completed' => 'bg-green-500',
        'running'   => 'bg-blue-500',
        'queued'    => 'bg-indigo-500',
        'paused'    => 'bg-yellow-500',
        'failed'    => 'bg-red-500',
        'draft'     => 'bg-gray-400',
        'scheduled' => 'bg-gray-400',
    ];
    $statusColor = $statusColors[$campaign->status] ?? 'bg-gray-100 text-gray-600';
    $statusDot   = $statusDots[$campaign->status] ?? 'bg-gray-400';
    $customerTimezone = auth('customer')->user()->timezone ?? config('app.timezone', 'UTC');
    $scheduledFor = $campaign->scheduled_at ?? $campaign->send_at;
    $quotaAutoResumeMessage = null;
    if (
        $campaign->status === 'paused'
        && ($autoResumeReason ?? null) === 'delivery_server_quota'
        && !empty($autoResumeAt)
    ) {
        $autoResumeTimezoneOffset = $autoResumeAt->copy()->setTimezone($customerTimezone)->format('P');
        $quotaAutoResumeMessage = 'Delivery server quota reached. Auto-resume scheduled at '
            . $autoResumeAt->copy()->setTimezone($customerTimezone)->format('h:i A')
            . ' (UTC ' . $autoResumeTimezoneOffset . ')';
    }
@endphp

@section('breadcrumbs')
    <nav aria-label="Breadcrumb" class="mb-0">
        <ol class="flex flex-wrap items-center gap-1.5 text-[12px] text-gray-500 dark:text-gray-500">
            <li>
                <a href="{{ route('customer.campaigns.index') }}" class="font-medium hover:text-gray-600 dark:hover:text-gray-300 transition-colors">Campaigns</a>
            </li>
            <li aria-hidden="true" class="text-gray-400 dark:text-gray-500">/</li>
            <li class="text-gray-500 dark:text-gray-400 truncate max-w-[240px]">{{ $campaign->name }}</li>
        </ol>
    </nav>
@endsection

@section('page-title-meta')
    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColor }}">
        <span class="w-1.5 h-1.5 rounded-full {{ $statusDot }}"></span>
        {{ ucfirst($campaign->status) }}
    </span>
@endsection

@section('page-title-details')
    @if($campaign->started_at)
        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-500 dark:text-gray-400">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span>
                {{ $campaign->started_at->format('M j, Y') }}
                @if($campaign->finished_at) – {{ $campaign->finished_at->format('M j, Y') }}@endif
            </span>
            @if($quotaAutoResumeMessage)
                <span class="text-[13px] font-medium text-amber-600 dark:text-amber-400">{{ $quotaAutoResumeMessage }}</span>
            @endif
        </div>
    @elseif($scheduledFor)
        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-500 dark:text-gray-400">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span>Scheduled for {{ $scheduledFor->timezone($customerTimezone)->format('M j, Y h:i A') }}</span>
            @if($quotaAutoResumeMessage)
                <span class="text-[13px] font-medium text-amber-600 dark:text-amber-400">{{ $quotaAutoResumeMessage }}</span>
            @endif
        </div>
    @elseif($quotaAutoResumeMessage)
        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-500 dark:text-gray-400">
            <span class="text-[13px] font-medium text-amber-600 dark:text-amber-400">{{ $quotaAutoResumeMessage }}</span>
        </div>
    @endif
@endsection

@section('page-actions')
    <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:flex-wrap sm:items-center sm:justify-end">
        @customercan('campaigns.permissions.can_edit_campaigns')
            <a href="{{ route('customer.campaigns.edit', $campaign) }}"
               class="inline-flex items-center justify-center w-full sm:w-9 h-9 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
               title="Edit campaign">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </a>
        @endcustomercan
        @customercan('campaigns.permissions.can_create_campaigns')
            <form method="POST" action="{{ route('customer.campaigns.duplicate', $campaign) }}" class="inline">
                @csrf
                <button type="submit"
                        class="inline-flex items-center justify-center w-full sm:w-9 h-9 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                        title="Duplicate campaign">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                </button>
            </form>
        @endcustomercan

        @if($campaign->isFailed() || $campaign->isCompleted())
            @customercan('campaigns.permissions.can_start_campaigns')
                <form method="POST" action="{{ route('customer.campaigns.rerun', $campaign) }}" class="inline" onsubmit="return confirm('This will reset the campaign to draft status. Are you sure?');">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Rerun
                    </button>
                </form>
            @endcustomercan
        @endif
        @if($campaign->canStart())
            @customercan('campaigns.permissions.can_start_campaigns')
                <form method="POST" action="{{ route('customer.campaigns.start', $campaign) }}" class="inline" onsubmit="return confirm('Are you sure you want to start this campaign?');">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-4 py-2 rounded-lg bg-[#1E5FEA] text-white text-sm font-semibold hover:bg-blue-700 transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Send
                    </button>
                </form>
            @endcustomercan
        @endif
        @if($campaign->canPause())
            @customercan('campaigns.permissions.can_start_campaigns')
                <form method="POST" action="{{ route('customer.campaigns.pause', $campaign) }}" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-4 py-2 rounded-lg bg-yellow-500 text-white text-sm font-semibold hover:bg-yellow-600 transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z"/></svg>
                        Pause
                    </button>
                </form>
            @endcustomercan
        @endif
        @if($campaign->canResume())
            @customercan('campaigns.permissions.can_start_campaigns')
                <form method="POST" action="{{ route('customer.campaigns.resume', $campaign) }}" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-4 py-2 rounded-lg bg-[#1E5FEA] text-white text-sm font-semibold hover:bg-blue-700 transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Resume
                    </button>
                </form>
            @endcustomercan
        @endif
    </div>
@endsection

@section('content')
<div class="space-y-5">
    {{-- Alert banners --}}
    @if(!empty($runPreflightIssues))
        <div class="flex items-start gap-3 p-4 bg-yellow-50 border border-yellow-200 rounded-xl dark:bg-yellow-900/20 dark:border-yellow-800">
            <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <div class="flex-1">
                <p class="text-sm font-semibold text-yellow-900 dark:text-yellow-100 mb-1">Action required before you can run this campaign</p>
                <ul class="text-sm list-disc list-inside space-y-0.5 text-yellow-800 dark:text-yellow-200">
                    @foreach($runPreflightIssues as $issue)<li>{{ $issue }}</li>@endforeach
                </ul>
            </div>
        </div>
    @endif
    @if($campaign->status === 'failed' && $campaign->failure_reason)
        <div class="flex items-start gap-3 p-4 bg-red-50 border border-red-200 rounded-xl dark:bg-red-900/20 dark:border-red-800">
            <svg class="w-5 h-5 text-red-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <div>
                <p class="text-sm font-semibold text-red-800 dark:text-red-200">Campaign Failed</p>
                <p class="text-sm text-red-700 dark:text-red-300 mt-0.5">{{ $campaign->failure_reason }}</p>
            </div>
        </div>
    @endif

    {{-- ── TAB NAV ── --}}
    @php
        $activeTab = request()->query('tab', 'overview');
        $tabs = [
            'overview' => [
                'label' => 'Overview',
                'icon'  => '<rect stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" x="3" y="3" width="7" height="7" rx="1"/><rect stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" x="14" y="3" width="7" height="7" rx="1"/><rect stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" x="3" y="14" width="7" height="7" rx="1"/><rect stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" x="14" y="14" width="7" height="7" rx="1"/>',
            ],
            'opens-clicks' => [
                'label' => 'Opens & Clicks',
                'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M2.062 12.348a1 1 0 010-.696 10.75 10.75 0 0119.876 0 1 1 0 010 .696 10.75 10.75 0 01-19.876 0"/><circle stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" cx="12" cy="12" r="3"/>',
            ],
            'subscribers' => [
                'label' => 'Subscribers & Activity',
                'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" cx="8.5" cy="7" r="4"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M20 8v6"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M23 11h-6"/>',
            ],
            'deliverability' => [
                'label' => 'Deliverability & Bounces',
                'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M20 13c0 5-3.5 7.5-8 9-4.5-1.5-8-4-8-9V6l8-4 8 4z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4"/>',
            ],
            'ab-testing' => [
                'label' => 'A/B Testing',
                'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10 2v7.31"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M14 9.3V2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8.5 2h7"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M14 9.3a6.5 6.5 0 11-4 0"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M5.58 16.5h12.85"/>',
            ],
            'inbox-rotation' => [
                'label' => 'Inbox Rotation',
                'icon'  => '<path d="M12 5H6a2 2 0 0 0-2 2v3"/><path d="m9 8 3-3-3-3"/><path d="M4 14v4a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/></svg>',
            ],
            'automations' => [
                'label' => 'Automations',
                'icon'  => '<rect stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" x="3" y="11" width="6" height="10" rx="2"/><rect stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" x="15" y="3" width="6" height="8" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 16h2a2 2 0 002-2v-1"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 8h-2a2 2 0 00-2 2v1"/>',
            ],
            'logs' => [
                'label' => 'Logs',
                'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-cog-icon lucide-file-cog"><path d="M15 8a1 1 0 0 1-1-1V2a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8z"/><path d="M20 8v12a2 2 0 0 1-2 2h-4.182"/><path d="m3.305 19.53.923-.382"/><path d="M4 10.592V4a2 2 0 0 1 2-2h8"/><path d="m4.228 16.852-.924-.383"/><path d="m5.852 15.228-.383-.923"/><path d="m5.852 20.772-.383.924"/><path d="m8.148 15.228.383-.923"/><path d="m8.53 21.696-.382-.924"/><path d="m9.773 16.852.922-.383"/><path d="m9.773 19.148.922.383"/><circle cx="7" cy="18" r="3"/></svg>',
            ],
        ];
    @endphp
    <div class="border-b border-gray-200 dark:border-gray-700 -ml-6 pl-6">
        <nav class="flex overflow-x-auto" aria-label="Campaign tabs">
            @foreach($tabs as $slug => $tab)
                @php $isActive = $activeTab === $slug; @endphp
                <a href="{{ route('customer.campaigns.show', array_merge([$campaign], $slug !== 'overview' ? ['tab' => $slug] : [])) }}"
                   class="group inline-flex items-center gap-2 whitespace-nowrap px-0 py-3 mr-6 text-sm font-medium rounded-none bg-transparent no-underline border-b-2 transition-all duration-150
                       {{ $isActive
                           ? '!border-primary-600 text-primary-600 visited:text-primary-600 dark:text-primary-400 dark:!border-primary-400'
                           : '!border-transparent text-gray-500 visited:text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:!border-gray-300 dark:hover:!border-gray-500' }}">
                    <svg class="w-4 h-4 shrink-0 transition-colors
                             {{ $isActive ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-600 dark:group-hover:text-gray-300' }}"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        {!! $tab['icon'] !!}
                    </svg>
                    {{ $tab['label'] }}
                </a>
            @endforeach
        </nav>
    </div>

    {{-- ═══════════════════════════════════ OVERVIEW TAB ═══════════════════════════════════ --}}
    @if($activeTab === 'overview')

    {{-- Campaign Insight Banner --}}
    @if($stats['sent_count'] > 0)
    <div class="flex items-start justify-between gap-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl">
        <div class="flex items-center gap-3">
            <div class="mt-0.5 w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-800/60 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-blue-600 dark:text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <p class="text-sm text-blue-800 dark:text-blue-200">
                <span class="font-semibold">Campaign Insight:</span>
                This campaign has a
                <strong>{{ number_format($stats['open_rate'], 1) }}% open rate</strong>
                and <strong>{{ number_format($stats['click_rate'], 1) }}% click rate</strong>
                from <strong>{{ number_format($stats['sent_count']) }}</strong> emails sent.
                @if($stats['delivery_rate'] >= 99)
                    Delivery is <strong>excellent at {{ number_format($stats['delivery_rate'], 1) }}%</strong>.
                @endif
            </p>
        </div>
        <a href="{{ route('customer.campaigns.recipients', $campaign) }}"
           class="shrink-0 text-xs font-semibold text-blue-700 dark:text-blue-300 border border-blue-300 dark:border-blue-600 rounded-lg px-3 py-1.5 hover:bg-blue-100 dark:hover:bg-blue-800/40 transition-colors whitespace-nowrap">
            View Recipients
        </a>
    </div>
    @endif

    {{-- ── 5 KPI STAT CARDS ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        {{-- Recipients --}}
        <a href="{{ route('customer.campaigns.recipients', $campaign) }}" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5 flex flex-col gap-3 overflow-hidden relative transition hover:border-blue-200 dark:hover:border-blue-600 hover:shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Recipients</span>
                <svg class="text-blue-600" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tight">{{ number_format($stats['total_recipients']) }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Total recipients</p>
            </div>
            <div class="h-10 -mx-5 -mb-5"></div>
        </a>

        {{-- Sent --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5 flex flex-col gap-3 overflow-hidden relative">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Sent</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-truck-electric-icon lucide-truck-electric"><path d="M14 19V7a2 2 0 0 0-2-2H9"/><path d="M15 19H9"/><path d="M19 19h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.62L18.3 9.38a1 1 0 0 0-.78-.38H14"/><path d="M2 13v5a1 1 0 0 0 1 1h2"/><path d="M4 3 2.15 5.15a.495.495 0 0 0 .35.86h2.15a.47.47 0 0 1 .35.86L3 9.02"/><circle cx="17" cy="19" r="2"/><circle cx="7" cy="19" r="2"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tight" id="sentCount">{{ number_format($stats['sent_count']) }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Total sent</p>
            </div>
            <div class="h-10 -mx-5 -mb-5">
                <canvas id="sparkSent" class="w-full h-full"></canvas>
            </div>
        </div>

        {{-- Delivered --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5 flex flex-col gap-3 overflow-hidden relative">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Delivered</span>
                <svg class="text-green-600" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package-check-icon lucide-package-check"><path d="M12 22V12"/><path d="m16 17 2 2 4-4"/><path d="M21 11.127V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.729l7 4a2 2 0 0 0 2 .001l1.32-.753"/><path d="M3.29 7 12 12l8.71-5"/><path d="m7.5 4.27 8.997 5.148"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tight" id="deliveredCount">{{ number_format($stats['delivered']) }}</p>
                <div class="flex items-center gap-1.5 mt-0.5">
                    <span class="text-xs font-medium text-green-600 dark:text-green-400">({{ number_format($stats['delivery_rate'], 1) }}%)</span>
                    <span class="text-xs text-gray-400 dark:text-gray-500">delivery rate</span>
                </div>
            </div>
            <div class="h-10 -mx-5 -mb-5">
                <canvas id="sparkDelivered" class="w-full h-full"></canvas>
            </div>
        </div>

        {{-- Total Opens --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5 flex flex-col gap-3 overflow-hidden relative">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Opens</span>
                <svg class="text-blue-600" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail-open-icon lucide-mail-open"><path d="M21.2 8.4c.5.38.8.97.8 1.6v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V10a2 2 0 0 1 .8-1.6l8-6a2 2 0 0 1 2.4 0l8 6Z"/><path d="m22 10-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 10"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tight" id="openedCount">{{ number_format($stats['opened_count']) }}</p>
                <div class="flex items-center gap-1.5 mt-0.5">
                    <span class="text-xs font-medium text-blue-600 dark:text-blue-400">({{ number_format($stats['open_rate'], 1) }}%)</span>
                    <span class="text-xs text-gray-400 dark:text-gray-500">open rate</span>
                </div>
            </div>
            <div class="h-10 -mx-5 -mb-5">
                <canvas id="sparkOpens" class="w-full h-full"></canvas>
            </div>
        </div>

        {{-- Total Clicks --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5 flex flex-col gap-3 overflow-hidden relative">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Clicks</span>
                <svg class="text-purple-600" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mouse-left-icon lucide-mouse-left"><path d="M12 7.318V10"/><path d="M5 10v5a7 7 0 0 0 14 0V9c0-3.527-2.608-6.515-6-7"/><circle cx="7" cy="4" r="2"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tight" id="clickedCount">{{ number_format($stats['clicked_count']) }}</p>
                <div class="flex items-center gap-1.5 mt-0.5">
                    <span class="text-xs font-medium text-purple-600 dark:text-purple-400">({{ number_format($stats['click_rate'], 1) }}%)</span>
                    <span class="text-xs text-gray-400 dark:text-gray-500">click rate</span>
                </div>
            </div>
            <div class="h-10 -mx-5 -mb-5">
                <canvas id="sparkClicks" class="w-full h-full"></canvas>
            </div>
        </div>
    </div>

    {{-- ── ENGAGEMENT TRENDS + CAMPAIGN DETAILS ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Engagement Trends --}}
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Engagement Trends</h3>
                <div class="flex gap-1.5" id="trendToggle">
                    <button data-view="opens-clicks" onclick="switchTrendView('opens-clicks')"
                        class="trend-btn px-3 py-1 rounded-lg text-xs font-medium bg-primary-600 text-white transition-colors">
                        Opens & Clicks
                    </button>
                    <button data-view="deliverability" onclick="switchTrendView('deliverability')"
                        class="trend-btn px-3 py-1 rounded-lg text-xs font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        Deliverability
                    </button>
                </div>
            </div>
            {{-- Legend --}}
            <div class="flex items-center gap-4 mb-3" id="trendLegend">
                <span class="flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                    <span class="w-3 h-0.5 bg-blue-500 rounded-full inline-block"></span> Opens
                </span>
                <span class="flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                    <span class="w-3 h-0.5 bg-purple-500 rounded-full inline-block"></span> Clicks
                </span>
            </div>
            <div style="height: 200px;">
                <canvas id="engagementChart"></canvas>
            </div>
        </div>

        {{-- Campaign Details --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Campaign Details</h3>
                @if($campaign->html_content)
                    <a href="{{ route('customer.campaigns.preview-html', $campaign) }}" target="_blank"
                       class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                @endif
            </div>

            @if($campaign->html_content)
                <div class="rounded-lg overflow-hidden border border-gray-100 dark:border-gray-700 mb-4 bg-gray-50 dark:bg-gray-900" style="height: 120px;">
                    <iframe
                        class="w-full h-full scale-[0.5] origin-top-left pointer-events-none"
                        style="width: 200%; height: 200%;"
                        sandbox="allow-same-origin"
                        srcdoc="{{ htmlspecialchars($campaign->html_content, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') }}"
                    ></iframe>
                </div>
            @endif

            <dl class="space-y-3">
                <div>
                    <dt class="text-xs text-gray-400 dark:text-gray-500">Subject</dt>
                    <dd class="mt-0.5 text-sm text-gray-800 dark:text-gray-200 font-medium">{{ $campaign->subject }}</dd>
                </div>
                @if($campaign->preheader)
                <div>
                    <dt class="text-xs text-gray-400 dark:text-gray-500">Preheader</dt>
                    <dd class="mt-0.5 text-sm text-gray-700 dark:text-gray-300">{{ \Illuminate\Support\Str::limit($campaign->preheader, 80) }}</dd>
                </div>
                @endif
                <div>
                    <dt class="text-xs text-gray-400 dark:text-gray-500">From</dt>
                    <dd class="mt-0.5 text-sm text-gray-700 dark:text-gray-300">
                        {{ $campaign->from_name }}
                        @if($campaign->from_email)<span class="text-gray-400">&lt;{{ $campaign->from_email }}&gt;</span>@endif
                    </dd>
                </div>
                @if($campaign->emailList)
                <div>
                    <dt class="text-xs text-gray-400 dark:text-gray-500">List</dt>
                    <dd class="mt-0.5 text-sm text-gray-700 dark:text-gray-300">{{ $campaign->emailList->name }}</dd>
                </div>
                @endif
                @if($scheduledFor)
                <div>
                    <dt class="text-xs text-gray-400 dark:text-gray-500">Scheduled For</dt>
                    <dd class="mt-0.5 text-sm text-gray-700 dark:text-gray-300">{{ $scheduledFor->timezone($customerTimezone)->format('M j, Y h:i A') }}</dd>
                </div>
                @endif
            </dl>
        </div>
    </div>

    {{-- ── BOUNCES + UNSUBSCRIBES + SPAM COMPLAINTS ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        {{-- Bounces --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5 flex items-start justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Bounces (Hard & Soft)</p>
                <p class="mt-1.5 text-2xl font-bold text-gray-900 dark:text-gray-100" id="bouncedCount">{{ number_format($stats['bounced_count']) }}</p>
                @if($stats['bounced_count'] > 0)
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">({{ number_format($stats['bounce_rate'], 1) }}%)</p>
                @endif
            </div>
            <div class="w-9 h-9 rounded-full bg-orange-50 dark:bg-orange-900/30 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                </svg>
            </div>
        </div>

        {{-- Unsubscribes --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5 flex items-start justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Unsubscribes</p>
                <p class="mt-1.5 text-2xl font-bold text-gray-900 dark:text-gray-100" id="unsubscribedCount">{{ number_format($stats['unsubscribed_count']) }}</p>
                @if($stats['sent_count'] > 0)
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">({{ number_format($stats['sent_count'] > 0 ? ($stats['unsubscribed_count'] / $stats['sent_count'] * 100) : 0, 2) }}%)</p>
                @endif
            </div>
            <div class="w-9 h-9 rounded-full bg-gray-50 dark:bg-gray-700 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
        </div>

        {{-- Spam Complaints --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5 flex items-start justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Spam Complaints</p>
                <p class="mt-1.5 text-2xl font-bold text-gray-900 dark:text-gray-100" id="complainedCount">{{ number_format($stats['complained_count']) }}</p>
                @if($stats['sent_count'] > 0)
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">({{ number_format($stats['sent_count'] > 0 ? ($stats['complained_count'] / $stats['sent_count'] * 100) : 0, 3) }}%)</p>
                @endif
            </div>
            <div class="w-9 h-9 rounded-full bg-yellow-50 dark:bg-yellow-900/30 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- ── TOP LINKS + LIVE ACTIVITY ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Top Links Clicked --}}
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Top Links Clicked</h3>
                <a href="{{ route('customer.campaigns.recipients', $campaign) }}" class="text-xs font-medium text-[#1E5FEA] hover:underline">View full report</a>
            </div>
            @if($stats['top_links']->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-700">
                                <th class="pb-2 text-left text-xs font-medium text-gray-400 dark:text-gray-500">Link URL</th>
                                <th class="pb-2 text-right text-xs font-medium text-gray-400 dark:text-gray-500">Unique Clicks</th>
                                <th class="pb-2 text-right text-xs font-medium text-gray-400 dark:text-gray-500">Total Clicks</th>
                                <th class="pb-2 text-right text-xs font-medium text-gray-400 dark:text-gray-500 pl-4">Click Share</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                            @php $maxClicks = $stats['top_links']->max('total_clicks') ?: 1; @endphp
                            @foreach($stats['top_links'] as $link)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="py-3 pr-4">
                                    <a href="{{ $link->url }}" target="_blank" rel="noopener"
                                       class="text-sm text-[#1E5FEA] hover:underline block truncate max-w-[220px]"
                                       title="{{ $link->url }}">
                                        {{ \Illuminate\Support\Str::limit($link->url, 45) }}
                                    </a>
                                </td>
                                <td class="py-3 text-right text-sm font-medium text-gray-700 dark:text-gray-300">{{ number_format($link->unique_clicks) }}</td>
                                <td class="py-3 text-right text-sm font-medium text-gray-700 dark:text-gray-300">{{ number_format($link->total_clicks) }}</td>
                                <td class="py-3 pl-4">
                                    @php $pct = $maxClicks > 0 ? round(($link->total_clicks / $maxClicks) * 100) : 0; @endphp
                                    <div class="flex items-center justify-end gap-2">
                                        <span class="text-xs font-medium text-gray-600 dark:text-gray-400 w-10 text-right">{{ number_format($stats['sent_count'] > 0 ? ($link->total_clicks / $stats['sent_count'] * 100) : 0, 1) }}%</span>
                                        <div class="w-16 h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                            <div class="h-full bg-[#1E5FEA] rounded-full" style="width: {{ $pct }}%"></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-10 text-center">
                    <svg class="w-8 h-8 text-gray-300 dark:text-gray-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                    <p class="text-sm text-gray-400 dark:text-gray-500">No link clicks recorded yet</p>
                </div>
            @endif
        </div>

        {{-- Live Activity --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Live Activity</h3>
                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-green-600 dark:text-green-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 {{ $campaign->isRunning() ? 'animate-pulse' : '' }}"></span>
                    {{ $campaign->isRunning() ? 'Live' : 'Latest' }}
                </span>
            </div>
            @if($liveActivity->count() > 0)
                <div class="space-y-3">
                    @foreach($liveActivity as $log)
                        @php
                            $recipientEmail = $log->recipient?->email ?? 'Unknown';
                            $initials = strtoupper(substr($recipientEmail, 0, 2));
                            $colorClasses = ['bg-blue-100 text-blue-700', 'bg-purple-100 text-purple-700', 'bg-green-100 text-green-700', 'bg-orange-100 text-orange-700', 'bg-pink-100 text-pink-700'];
                            $colorIdx = abs(crc32($recipientEmail)) % count($colorClasses);
                            $color = $colorClasses[$colorIdx];
                        @endphp
                        <div class="flex items-start gap-3">
                            <div class="w-7 h-7 rounded-full {{ $color }} flex items-center justify-center text-xs font-semibold shrink-0">
                                {{ $initials }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-medium text-gray-800 dark:text-gray-200 truncate">{{ $recipientEmail }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                    {{ $log->event === 'clicked' ? 'clicked a link' : 'opened the email' }}
                                    · {{ $log->created_at->diffForHumans() }}
                                </p>
                            </div>
                            <div class="w-2 h-2 rounded-full mt-1.5 shrink-0 {{ $log->event === 'clicked' ? 'bg-purple-400' : 'bg-blue-400' }}"></div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-10 text-center">
                    <svg class="w-8 h-8 text-gray-300 dark:text-gray-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <p class="text-sm text-gray-400 dark:text-gray-500">No activity yet</p>
                </div>
            @endif
        </div>
    </div>

    {{-- ── TOP DEVICES + TOP EMAIL CLIENTS ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        {{-- Top Devices --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">Top Devices</h3>
            @php
                $deviceData = [];
                foreach ($liveActivity as $log) {
                    if ($log->user_agent) {
                        $ua = strtolower($log->user_agent);
                        if (str_contains($ua, 'mobile') || str_contains($ua, 'android') || str_contains($ua, 'iphone')) {
                            $deviceData['Mobile'] = ($deviceData['Mobile'] ?? 0) + 1;
                        } elseif (str_contains($ua, 'tablet') || str_contains($ua, 'ipad')) {
                            $deviceData['Tablet'] = ($deviceData['Tablet'] ?? 0) + 1;
                        } else {
                            $deviceData['Desktop'] = ($deviceData['Desktop'] ?? 0) + 1;
                        }
                    }
                }
                $deviceTotal = array_sum($deviceData) ?: 1;
                $deviceColors = ['Mobile' => '#1E5FEA', 'Desktop' => '#8B5CF6', 'Tablet' => '#E5E7EB'];
                if (empty($deviceData)) {
                    $deviceData = ['Mobile' => 0, 'Desktop' => 0, 'Tablet' => 0];
                }
            @endphp
            <div class="flex items-center gap-6">
                <div class="relative w-32 h-32 shrink-0">
                    <canvas id="devicesChart" width="128" height="128"></canvas>
                    @if(array_sum($deviceData) > 0)
                        @php $topDevice = array_keys($deviceData, max($deviceData))[0]; $topPct = round(max($deviceData) / $deviceTotal * 100); @endphp
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                            <span class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $topPct }}%</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $topDevice }}</span>
                        </div>
                    @else
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <span class="text-xs text-gray-400">No data</span>
                        </div>
                    @endif
                </div>
                <div class="flex flex-col gap-2 flex-1">
                    @foreach($deviceData as $device => $count)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $deviceColors[$device] ?? '#9CA3AF' }}"></span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ $device }}</span>
                            </div>
                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $deviceTotal > 1 ? round($count / $deviceTotal * 100) : 0 }}%</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Top Email Clients --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Top Email Clients</h3>
                <span class="text-xs text-gray-400 dark:text-gray-500">Based on user agent</span>
            </div>
            @php
                $clientData = [];
                foreach ($liveActivity as $log) {
                    if ($log->user_agent) {
                        $ua = strtolower($log->user_agent);
                        if (str_contains($ua, 'applemail') || str_contains($ua, 'apple mail') || str_contains($ua, 'darwin')) {
                            $client = 'Apple Mail';
                        } elseif (str_contains($ua, 'gmail') || str_contains($ua, 'googleimageproxy')) {
                            $client = 'Gmail';
                        } elseif (str_contains($ua, 'outlook') || str_contains($ua, 'microsoft')) {
                            $client = 'Outlook';
                        } elseif (str_contains($ua, 'yahoo')) {
                            $client = 'Yahoo Mail';
                        } else {
                            $client = 'Other';
                        }
                        $clientData[$client] = ($clientData[$client] ?? 0) + 1;
                    }
                }
                arsort($clientData);
                $clientTotal = array_sum($clientData) ?: 1;
                $clientColors = ['Apple Mail' => '#1E5FEA', 'Gmail' => '#EF4444', 'Outlook' => '#3B82F6', 'Yahoo Mail' => '#8B5CF6', 'Other' => '#9CA3AF'];
            @endphp
            @if(!empty($clientData))
                <div class="space-y-3">
                    @foreach(array_slice($clientData, 0, 5, true) as $client => $count)
                        @php $pct = round($count / $clientTotal * 100); @endphp
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ $client }}</span>
                                <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $pct }}%</span>
                            </div>
                            <div class="h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all" style="width: {{ $pct }}%; background-color: {{ $clientColors[$client] ?? '#9CA3AF' }}"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-10 text-center">
                    <svg class="w-8 h-8 text-gray-300 dark:text-gray-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <p class="text-sm text-gray-400 dark:text-gray-500">No client data available</p>
                </div>
            @endif
        </div>
    </div>

    {{-- ── LIVE DELIVERY PROGRESS (running only) ── --}}
    @if($campaign->status === 'running' && $stats['total_recipients'] > 0)
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Delivery Progress</h3>
            <span class="text-xs text-gray-500 dark:text-gray-400" id="progressPercentage">
                {{ number_format(($stats['sent_count'] / $stats['total_recipients']) * 100, 1) }}%
            </span>
        </div>
        <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2 mb-3">
            <div class="bg-[#1E5FEA] h-2 rounded-full transition-all duration-500" id="progressBar"
                 style="width: {{ ($stats['sent_count'] / $stats['total_recipients']) * 100 }}%"></div>
        </div>
        <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
            <span id="progressText">{{ number_format($stats['sent_count']) }} / {{ number_format($stats['total_recipients']) }} sent</span>
            <span>Speed: <strong id="sendingSpeed">{{ number_format($stats['sending_speed'], 2) }}</strong>/s</span>
        </div>
    </div>
    @endif

    {{-- ── RECIPIENTS QUICK LINK ── --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5 flex items-center justify-between">
        <div>
            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Recipients</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">View per-recipient status, opens, and click details</p>
        </div>
        <a href="{{ route('customer.campaigns.recipients', $campaign) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#1E5FEA] text-white text-sm font-semibold hover:bg-blue-700 transition-colors shadow-sm">
            View All Recipients
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
    </div>

    @endif {{-- end overview tab --}}

    {{-- ═══════════════════════════════════ OPENS & CLICKS TAB ═══════════════════════════════════ --}}
    @if($activeTab === 'opens-clicks')
    @php
        $ctor = $stats['opened_count'] > 0
            ? round(($stats['clicked_count'] / $stats['opened_count']) * 100, 1)
            : 0;
        $avgOpensPerReader = $stats['opened_count'] > 0 && $totalOpenEvents > 0
            ? round($totalOpenEvents / $stats['opened_count'], 2)
            : 0;

        // Build device breakdown from agentGroups
        $ocDevices  = ['Mobile' => 0, 'Desktop' => 0, 'Tablet' => 0];
        $ocClients  = ['Apple Mail' => 0, 'Gmail' => 0, 'Outlook' => 0, 'Yahoo Mail' => 0, 'Other' => 0];
        foreach ($agentGroups as $ag) {
            $ua = strtolower($ag->user_agent);
            if (str_contains($ua, 'mobile') || str_contains($ua, 'android') || str_contains($ua, 'iphone'))
                $ocDevices['Mobile'] += $ag->cnt;
            elseif (str_contains($ua, 'tablet') || str_contains($ua, 'ipad'))
                $ocDevices['Tablet'] += $ag->cnt;
            else
                $ocDevices['Desktop'] += $ag->cnt;

            if (str_contains($ua, 'applemail') || str_contains($ua, 'darwin'))
                $ocClients['Apple Mail'] += $ag->cnt;
            elseif (str_contains($ua, 'gmail') || str_contains($ua, 'googleimageproxy'))
                $ocClients['Gmail'] += $ag->cnt;
            elseif (str_contains($ua, 'outlook') || str_contains($ua, 'microsoft'))
                $ocClients['Outlook'] += $ag->cnt;
            elseif (str_contains($ua, 'yahoo'))
                $ocClients['Yahoo Mail'] += $ag->cnt;
            else
                $ocClients['Other'] += $ag->cnt;
        }
        $totalDevices = max(array_sum($ocDevices), 1);
        $totalClients = max(array_sum($ocClients), 1);
        arsort($ocClients);

        // Heatmap helpers
        $days  = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $hours = [0, 4, 8, 12, 16, 20];
        $heatOpens = $heatmapData['opened'] ?? [];
        $maxHeat = 1;
        foreach ($heatOpens as $dayRow) {
            foreach ($dayRow as $cnt) { $maxHeat = max($maxHeat, $cnt); }
        }
    @endphp

    {{-- ── 4 KPI CARDS ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Unique Opens --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Unique Opens</p>
            <div class="flex items-end gap-2">
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 tracking-tight">{{ number_format($stats['opened_count']) }}</p>
                @if($stats['open_rate'] > 0)
                    <span class="mb-1 inline-flex items-center gap-0.5 text-xs font-semibold text-green-600 dark:text-green-400">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        {{ number_format($stats['open_rate'], 1) }}%
                    </span>
                @endif
            </div>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">of {{ number_format($stats['delivered']) }} delivered</p>
        </div>

        {{-- Total Opens --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Total Opens</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 tracking-tight">{{ number_format($totalOpenEvents) }}</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                @if($avgOpensPerReader > 0)
                    Average {{ $avgOpensPerReader }} opens per reader
                @else
                    Including repeat opens
                @endif
            </p>
        </div>

        {{-- Unique Clicks --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Unique Clicks</p>
            <div class="flex items-end gap-2">
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 tracking-tight">{{ number_format($stats['clicked_count']) }}</p>
                @if($stats['click_rate'] > 0)
                    <span class="mb-1 inline-flex items-center gap-0.5 text-xs font-semibold text-green-600 dark:text-green-400">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        {{ number_format($stats['click_rate'], 1) }}%
                    </span>
                @endif
            </div>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">of {{ number_format($stats['delivered']) }} delivered</p>
        </div>

        {{-- CTOR --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Click-to-Open Rate (CTOR)</p>
            <div class="flex items-end gap-1.5">
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 tracking-tight">{{ $ctor }}%</p>
                <span class="mb-1 text-xs text-gray-400 dark:text-gray-500">/ 100%</span>
            </div>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                @if($ctor >= 20) Highly engaged audience
                @elseif($ctor >= 10) Moderately engaged
                @else Low engagement
                @endif
            </p>
        </div>
    </div>

    {{-- ── OPEN VS CLICK PERFORMANCE CHART ── --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
        <div class="flex items-center justify-between mb-1">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Open vs Click Performance</h3>
            <div class="flex gap-1" id="ocViewToggle">
                <button data-view="daily" onclick="switchOcView('daily')"
                    class="oc-btn px-3 py-1 rounded-lg text-xs font-medium bg-primary-600 text-white transition-colors">
                    Daily
                </button>
                <button data-view="hourly" onclick="switchOcView('hourly')"
                    class="oc-btn px-3 py-1 rounded-lg text-xs font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    Hourly
                </button>
            </div>
        </div>
        <div class="flex items-center gap-4 mb-4">
            <span class="flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                <span class="w-5 h-0.5 bg-blue-500 inline-block rounded-full"></span> Opens
            </span>
            <span class="flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                <span class="w-5 h-0.5 bg-purple-500 inline-block rounded-full"></span> Clicks
            </span>
        </div>
        <div style="height: 220px;">
            <canvas id="ocPerformanceChart"></canvas>
        </div>
    </div>

    {{-- ── LINK PERFORMANCE TABLE ── --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                </svg>
                Link Performance
            </h3>
            <button onclick="document.getElementById('ocSearchRow').classList.toggle('hidden')"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 text-xs font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Search links
            </button>
        </div>
        <div id="ocSearchRow" class="hidden mb-3">
            <input type="text" id="ocLinkSearch" placeholder="Filter links..."
                oninput="filterOcLinks(this.value)"
                class="w-full text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-gray-700 dark:text-gray-300 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
        </div>
        @if($stats['top_links']->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full" id="ocLinksTable">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-700">
                            <th class="pb-3 text-left text-xs font-medium text-gray-400 dark:text-gray-500">Link URL</th>
                            <th class="pb-3 text-right text-xs font-medium text-gray-400 dark:text-gray-500 pr-4">Unique Clicks</th>
                            <th class="pb-3 text-right text-xs font-medium text-gray-400 dark:text-gray-500 pr-4">Total Clicks</th>
                            <th class="pb-3 text-right text-xs font-medium text-gray-400 dark:text-gray-500 pr-6">Click Rate</th>
                            <th class="pb-3 text-right text-xs font-medium text-gray-400 dark:text-gray-500">Last Clicked</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50" id="ocLinksBody">
                        @php $maxLinkClicks = $stats['top_links']->max('total_clicks') ?: 1; @endphp
                        @foreach($stats['top_links'] as $link)
                            @php
                                $ctr = $stats['delivered'] > 0 ? round($link->unique_clicks / $stats['delivered'] * 100, 1) : 0;
                                $barPct = $maxLinkClicks > 0 ? round($link->total_clicks / $maxLinkClicks * 100) : 0;
                            @endphp
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors oc-link-row">
                                <td class="py-3 pr-4">
                                    <a href="{{ $link->url }}" target="_blank" rel="noopener"
                                       class="text-sm font-medium text-primary-600 dark:text-primary-400 hover:underline block truncate max-w-[280px]"
                                       title="{{ $link->url }}">
                                        {{ \Illuminate\Support\Str::limit($link->url, 55) }}
                                    </a>
                                </td>
                                <td class="py-3 pr-4 text-right text-sm font-semibold text-gray-800 dark:text-gray-200">{{ number_format($link->unique_clicks) }}</td>
                                <td class="py-3 pr-4 text-right text-sm font-semibold text-gray-800 dark:text-gray-200">{{ number_format($link->total_clicks) }}</td>
                                <td class="py-3 pr-6">
                                    <div class="flex items-center justify-end gap-3">
                                        <span class="text-sm font-semibold {{ $ctr >= 10 ? 'text-primary-600 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300' }}">{{ $ctr }}%</span>
                                        <div class="w-24 h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                            <div class="h-full bg-primary-500 rounded-full" style="width: {{ $barPct }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 text-right text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap">
                                    {{ $link->last_clicked_at ? \Carbon\Carbon::parse($link->last_clicked_at)->diffForHumans() : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($stats['top_links']->count() >= 10)
                <div class="mt-4 text-center">
                    <a href="{{ route('customer.campaigns.recipients', $campaign) }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View all links</a>
                </div>
            @endif
        @else
            <div class="flex flex-col items-center justify-center py-12">
                <svg class="w-8 h-8 text-gray-300 dark:text-gray-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                </svg>
                <p class="text-sm text-gray-400 dark:text-gray-500">No link clicks recorded yet</p>
            </div>
        @endif
    </div>

    {{-- ── ENGAGEMENT HEATMAP + ENVIRONMENT DETAILS ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Engagement Heatmap --}}
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Engagement Time
                </h3>
                <div class="flex gap-1" id="heatmapToggle">
                    <button data-view="opens" onclick="switchHeatmap('opens')"
                        class="hm-btn px-3 py-1 rounded-lg text-xs font-medium bg-primary-600 text-white transition-colors">
                        Opens
                    </button>
                    <button data-view="clicks" onclick="switchHeatmap('clicks')"
                        class="hm-btn px-3 py-1 rounded-lg text-xs font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        Clicks
                    </button>
                </div>
            </div>

            {{-- Heatmap grid --}}
            @php
                $heatHours = ['12am','4am','8am','12pm','4pm','8pm'];
                $heatHourMap = [0,4,8,12,16,20];
                $heatDays = [0=>'Sun',1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat'];
                // Re-order to Mon first
                $heatDayOrder = [1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat',0=>'Sun'];
            @endphp
            <div class="overflow-x-auto">
                <div class="min-w-[420px]">
                    <div class="flex mb-1 pl-10">
                        @foreach($heatHours as $hl)
                            <div class="flex-1 text-center text-xs text-gray-400 dark:text-gray-500">{{ $hl }}</div>
                        @endforeach
                    </div>
                    @foreach($heatDayOrder as $dowIdx => $dayLabel)
                        <div class="flex items-center gap-1 mb-1">
                            <span class="w-9 text-right text-xs text-gray-400 dark:text-gray-500 shrink-0 pr-1">{{ $dayLabel }}</span>
                            @foreach($heatHourMap as $hIdx => $hr)
                                @php
                                    $cellCount = $heatOpens[$dowIdx][$hr] ?? 0;
                                    $intensity = $maxHeat > 0 ? $cellCount / $maxHeat : 0;
                                    if ($intensity <= 0)        $cellClass = 'bg-blue-50 dark:bg-gray-700/60';
                                    elseif ($intensity <= 0.25) $cellClass = 'bg-blue-100 dark:bg-blue-900/30';
                                    elseif ($intensity <= 0.5)  $cellClass = 'bg-blue-200 dark:bg-blue-800/50';
                                    elseif ($intensity <= 0.75) $cellClass = 'bg-blue-400 dark:bg-blue-700';
                                    else                        $cellClass = 'bg-blue-600 dark:bg-blue-500';
                                @endphp
                                <div class="hm-cell flex-1 h-8 rounded {{ $cellClass }} transition-colors cursor-default"
                                     data-dow="{{ $dowIdx }}"
                                     data-hr="{{ $hr }}"
                                     data-label="{{ $dayLabel }} {{ $heatHours[$hIdx] }}"
                                     title="{{ $dayLabel }} {{ $heatHours[$hIdx] }}: {{ $cellCount }} opens"></div>
                            @endforeach
                        </div>
                    @endforeach
                    <div class="flex items-center justify-end gap-2 mt-2 pt-2 border-t border-gray-50 dark:border-gray-700">
                        <span class="text-xs text-gray-400 dark:text-gray-500">Low</span>
                        <div class="flex gap-0.5">
                            @foreach(['bg-blue-50','bg-blue-100','bg-blue-200','bg-blue-400','bg-blue-600'] as $swatchClass)
                                <div class="w-4 h-3 rounded-sm {{ $swatchClass }}"></div>
                            @endforeach
                        </div>
                        <span class="text-xs text-gray-400 dark:text-gray-500">High</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Environment Details --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5 flex flex-col gap-5">
            {{-- Device split --}}
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Environment Details
                </h3>
                @php
                    $mobilePct  = round($ocDevices['Mobile']  / $totalDevices * 100);
                    $tabletPct  = round($ocDevices['Tablet']  / $totalDevices * 100);
                    $desktopPct = 100 - $mobilePct - $tabletPct;
                @endphp
                <div class="flex items-center justify-between text-xs font-medium mb-1.5">
                    <span class="text-gray-700 dark:text-gray-300">Mobile ({{ $mobilePct }}%)</span>
                    <span class="text-gray-500 dark:text-gray-400">Desktop ({{ $desktopPct }}%)</span>
                </div>
                <div class="flex w-full h-2.5 rounded-full overflow-hidden gap-px">
                    @if($mobilePct > 0)
                        <div class="h-full bg-primary-600 rounded-l-full" style="width: {{ $mobilePct }}%"></div>
                    @endif
                    @if($tabletPct > 0)
                        <div class="h-full bg-primary-300" style="width: {{ $tabletPct }}%"></div>
                    @endif
                    @if($desktopPct > 0)
                        <div class="h-full bg-gray-200 dark:bg-gray-600 rounded-r-full" style="width: {{ $desktopPct }}%"></div>
                    @endif
                </div>
                @if($tabletPct > 0)
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Tablet: {{ $tabletPct }}%</p>
                @endif
            </div>

            {{-- Email clients --}}
            <div>
                <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-3">Top Email Clients</p>
                @if(array_sum($ocClients) > 0)
                    <div class="space-y-3">
                        @php
                            $clientIcons = [
                                'Apple Mail' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/>',
                                'Gmail'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',
                                'Outlook'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',
                                'Yahoo Mail' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',
                                'Other'      => '<circle cx="12" cy="12" r="10" stroke-width="1.75"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8v4m0 4h.01"/>',
                            ];
                            $clientBarColors = ['Apple Mail'=>'bg-primary-600','Gmail'=>'bg-red-500','Outlook'=>'bg-blue-500','Yahoo Mail'=>'bg-purple-500','Other'=>'bg-gray-400'];
                        @endphp
                        @foreach(array_slice($ocClients, 0, 4, true) as $client => $count)
                            @if($count > 0)
                                @php $pct = round($count / $totalClients * 100, 1); @endphp
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-gray-400 dark:text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                {!! $clientIcons[$client] ?? $clientIcons['Other'] !!}
                                            </svg>
                                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $client }}</span>
                                        </div>
                                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $pct }}%</span>
                                    </div>
                                    <div class="h-1 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                        <div class="h-full {{ $clientBarColors[$client] ?? 'bg-gray-400' }} rounded-full" style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-400 dark:text-gray-500">No client data available</p>
                @endif
            </div>
        </div>
    </div>

    @endif {{-- end opens-clicks tab --}}

    {{-- ═══════════════════════════════════ SUBSCRIBERS & ACTIVITY TAB ═══════════════════════════════════ --}}
    @if($activeTab === 'subscribers')
    @php
        $subInsights = $subscriberInsights ?? [
            'active' => 0,
            'unengaged' => 0,
            'new_subscribers' => 0,
            'active_pct' => 0,
            'unengaged_pct' => 0,
            'audience_total' => 0,
        ];
        $engagers = $topEngagers ?? collect();
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
            <div class="flex items-start justify-between">
                <p class="text-sm text-gray-500 dark:text-gray-400">Active Users</p>
                <svg class="text-green-600" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-star-icon lucide-user-star"><path d="M16.051 12.616a1 1 0 0 1 1.909.024l.737 1.452a1 1 0 0 0 .737.535l1.634.256a1 1 0 0 1 .588 1.806l-1.172 1.168a1 1 0 0 0-.282.866l.259 1.613a1 1 0 0 1-1.541 1.134l-1.465-.75a1 1 0 0 0-.912 0l-1.465.75a1 1 0 0 1-1.539-1.133l.258-1.613a1 1 0 0 0-.282-.866l-1.156-1.153a1 1 0 0 1 .572-1.822l1.633-.256a1 1 0 0 0 .737-.535z"/><path d="M8 15H7a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/></svg>
            </div>
            <p class="mt-2 text-4xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($subInsights['active']) }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ number_format($subInsights['active_pct'], 1) }}% of recipients engaged</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
            <div class="flex items-start justify-between">
                <p class="text-sm text-gray-500 dark:text-gray-400">Unengaged</p>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ec3c3c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-round-minus-icon lucide-user-round-minus"><path d="M2 21a8 8 0 0 1 13.292-6"/><circle cx="10" cy="8" r="5"/><path d="M22 19h-6"/></svg>
            </div>
            <p class="mt-2 text-4xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($subInsights['unengaged']) }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ number_format($subInsights['unengaged_pct'], 1) }}% did not interact</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
            <div class="flex items-start justify-between">
                <p class="text-sm text-gray-500 dark:text-gray-400">New Subscribers</p>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2521f2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-plus-icon lucide-user-plus"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
            </div>
            <p class="mt-2 text-4xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($subInsights['new_subscribers']) }}</p>
            <p class="mt-1 text-sm text-green-600 dark:text-green-400">Joined during campaign period</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">
        <div class="xl:col-span-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between gap-3 mb-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Negative Feedback Timeline</h3>
                <div class="flex gap-1" id="subscriberFeedbackToggle">
                    <button data-mode="daily" onclick="switchSubscriberFeedbackMode('daily')"
                        class="sfb-btn px-3 py-1 rounded-lg text-xs font-medium bg-primary-600 text-white transition-colors">Daily</button>
                    <button data-mode="cumulative" onclick="switchSubscriberFeedbackMode('cumulative')"
                        class="sfb-btn px-3 py-1 rounded-lg text-xs font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">Cumulative</button>
                </div>
            </div>
            <div class="flex items-center justify-end gap-4 text-xs text-gray-500 dark:text-gray-400 mb-3">
                <span class="inline-flex items-center gap-1.5"><span class="w-3 h-0.5 bg-red-500 rounded-full"></span>Unsubscribes</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-3 h-0.5 bg-amber-500 rounded-full"></span>Complaints</span>
            </div>
            <div class="h-72">
                <canvas id="subscriberFeedbackChart"></canvas>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">Audience Breakdown</h3>
            <div class="flex items-center gap-5">
                <div class="relative w-40 h-40 shrink-0">
                    <canvas id="audienceSplitChart" width="160" height="160"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($subInsights['active_pct'], 0) }}%</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Engaged</span>
                    </div>
                </div>
                <div class="flex-1 space-y-3">
                    <div class="flex items-center justify-between gap-4 text-sm">
                        <span class="inline-flex items-center gap-2 text-gray-700 dark:text-gray-300"><span class="w-2.5 h-2.5 rounded-full bg-primary-600"></span>Engaged</span>
                        <span class="font-medium text-gray-800 dark:text-gray-200">{{ number_format($subInsights['active']) }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4 text-sm">
                        <span class="inline-flex items-center gap-2 text-gray-700 dark:text-gray-300"><span class="w-2.5 h-2.5 rounded-full bg-gray-200 dark:bg-gray-600"></span>Unengaged</span>
                        <span class="font-medium text-gray-800 dark:text-gray-200">{{ number_format($subInsights['unengaged']) }}</span>
                    </div>
                    <div class="pt-2 border-t border-gray-100 dark:border-gray-700 text-xs text-gray-500 dark:text-gray-400">
                        Total audience: {{ number_format($subInsights['audience_total']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-3 p-5 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Top Engagers</h3>
            <div class="flex items-center gap-2">
                <input type="text" oninput="filterTopEngagers(this.value)" placeholder="Search subscriber"
                    class="w-56 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20">
                <button type="button" onclick="exportTopEngagersCsv()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v10m0 0l-4-4m4 4l4-4M4 20h16"/></svg>
                    Export CSV
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm" id="topEngagersTable">
                <thead class="bg-gray-50 dark:bg-gray-700/30 text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-5 py-3 text-left font-medium">Subscriber</th>
                        <th class="px-5 py-3 text-left font-medium">Status</th>
                        <th class="px-5 py-3 text-left font-medium">Total Opens</th>
                        <th class="px-5 py-3 text-left font-medium">Total Clicks</th>
                        <th class="px-5 py-3 text-left font-medium">Last Activity</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($engagers as $engager)
                        @php
                            $opens = (int) ($engager->total_opens ?? 0);
                            $clicks = (int) ($engager->total_clicks ?? 0);
                            $activityScore = ($clicks * 2) + $opens;
                            if ($activityScore >= 12) {
                                $statusText = 'Highly Active';
                                $statusClass = 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300';
                            } elseif ($activityScore >= 5) {
                                $statusText = 'Active';
                                $statusClass = 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300';
                            } else {
                                $statusText = 'New';
                                $statusClass = 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300';
                            }
                            $fullName = trim(($engager->first_name ?? '') . ' ' . ($engager->last_name ?? ''));
                            $displayName = $fullName !== '' ? $fullName : $engager->email;
                            $lastActivity = $engager->last_activity_at ? \Carbon\Carbon::parse($engager->last_activity_at)->format('M j, g:i A') : '—';
                        @endphp
                        <tr class="engager-row border-t border-gray-100 dark:border-gray-700" data-search="{{ strtolower($displayName . ' ' . $engager->email) }}">
                            <td class="px-5 py-3.5">
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $displayName }}</span>
                                    @if($displayName !== $engager->email)
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $engager->email }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-3.5"><span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium {{ $statusClass }}">{{ $statusText }}</span></td>
                            <td class="px-5 py-3.5 text-gray-700 dark:text-gray-300">{{ number_format($opens) }}</td>
                            <td class="px-5 py-3.5 text-gray-700 dark:text-gray-300">{{ number_format($clicks) }}</td>
                            <td class="px-5 py-3.5 text-gray-500 dark:text-gray-400">{{ $lastActivity }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-400 dark:text-gray-500">No subscriber engagement data yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif {{-- end subscribers tab --}}

    {{-- ═══════════════════════════════════ DELIVERABILITY & BOUNCES TAB ═══════════════════════════════════ --}}
    @if($activeTab === 'deliverability')
    @php
        $d = $deliverabilityInsights ?? [
            'delivery_rate' => 0,
            'delivered' => 0,
            'total_bounces' => 0,
            'hard_bounces' => 0,
            'soft_bounces' => 0,
            'bounce_rate' => 0,
            'spam_reports' => 0,
            'spam_rate' => 0,
            'blocklisted' => 0,
        ];
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
            <div class="flex items-start justify-between">
                <p class="text-sm text-gray-500 dark:text-gray-400">Delivery Rate</p>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#14e121" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package-check-icon lucide-package-check"><path d="M12 22V12"/><path d="m16 17 2 2 4-4"/><path d="M21 11.127V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.729l7 4a2 2 0 0 0 2 .001l1.32-.753"/><path d="M3.29 7 12 12l8.71-5"/><path d="m7.5 4.27 8.997 5.148"/></svg>
            </div>
            <p class="mt-2 text-4xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($d['delivery_rate'], 1) }}%</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ number_format($d['delivered']) }} successfully delivered</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
            <div class="flex items-start justify-between">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Bounces</p>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ff3838" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-arrow-out-up-right-icon lucide-circle-arrow-out-up-right"><path d="M22 12A10 10 0 1 1 12 2"/><path d="M22 2 12 12"/><path d="M16 2h6v6"/></svg>
            </div>
            <p class="mt-2 text-4xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($d['total_bounces']) }} <span class="text-base text-gray-400">({{ number_format($d['bounce_rate'], 1) }}%)</span></p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ number_format($d['hard_bounces']) }} hard, {{ number_format($d['soft_bounces']) }} soft bounces</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
            <div class="flex items-start justify-between">
                <p class="text-sm text-gray-500 dark:text-gray-400">Spam Reports</p>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ffc038" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail-warning-icon lucide-mail-warning"><path d="M22 10.5V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v12c0 1.1.9 2 2 2h12.5"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/><path d="M20 14v4"/><path d="M20 22v.01"/></svg>
            </div>
            <p class="mt-2 text-4xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($d['spam_reports']) }} <span class="text-base text-gray-400">({{ number_format($d['spam_rate'], 1) }}%)</span></p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Within acceptable threshold (&lt; 0.3%)</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
            <div class="flex items-start justify-between">
                <p class="text-sm text-gray-500 dark:text-gray-400">Blocklisted</p>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#e2323b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shield-minus-icon lucide-shield-minus"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="M9 12h6"/></svg>
            </div>
            <p class="mt-2 text-4xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($d['blocklisted']) }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">IP and sending domain healthy</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">
        <div class="xl:col-span-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between gap-3 mb-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Bounce Activity Timeline</h3>
                <div class="flex gap-1" id="bounceTimelineToggle">
                    <button data-view="daily" onclick="switchBounceTimelineView('daily')"
                        class="bt-btn px-3 py-1 rounded-lg text-xs font-medium bg-primary-600 text-white transition-colors">Daily</button>
                    <button data-view="hourly" onclick="switchBounceTimelineView('hourly')"
                        class="bt-btn px-3 py-1 rounded-lg text-xs font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">Hourly</button>
                </div>
            </div>
            <div class="flex items-center justify-end gap-4 text-xs text-gray-500 dark:text-gray-400 mb-3">
                <span class="inline-flex items-center gap-1.5"><span class="w-3 h-0.5 bg-blue-500 rounded-full"></span>Soft Bounces</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-3 h-0.5 bg-red-500 rounded-full"></span>Hard Bounces</span>
            </div>
            <div class="h-72">
                <canvas id="bounceTimelineChart"></canvas>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">Bounce Reasons</h3>
            <div class="flex items-center gap-5">
                <div class="relative w-36 h-36 shrink-0">
                    <canvas id="bounceReasonsChart" width="144" height="144"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($d['total_bounces']) }}</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Total</span>
                    </div>
                </div>
                <div class="flex-1 space-y-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="inline-flex items-center gap-2 text-gray-700 dark:text-gray-300"><span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>Soft (Temp)</span>
                        <span class="font-medium text-gray-800 dark:text-gray-200">{{ number_format($d['soft_bounces']) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="inline-flex items-center gap-2 text-gray-700 dark:text-gray-300"><span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>Hard (Perm)</span>
                        <span class="font-medium text-gray-800 dark:text-gray-200">{{ number_format($d['hard_bounces']) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-3 p-5 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Inbox Provider Performance</h3>
            <button type="button" onclick="exportProviderPerformanceCsv()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v10m0 0l-4-4m4 4l4-4M4 20h16"/></svg>
                Export Data
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm" id="providerPerformanceTable">
                <thead class="bg-gray-50 dark:bg-gray-700/30 text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-5 py-3 text-left font-medium">Provider</th>
                        <th class="px-5 py-3 text-left font-medium">Sent</th>
                        <th class="px-5 py-3 text-left font-medium">Delivered</th>
                        <th class="px-5 py-3 text-left font-medium">Bounce Rate</th>
                        <th class="px-5 py-3 text-left font-medium">Health Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($providerPerformance ?? []) as $provider)
                        @php
                            $health = $provider['health'] ?? 'Excellent';
                            if ($health === 'Excellent') {
                                $healthClass = 'text-green-600 dark:text-green-400';
                                $barClass = 'bg-green-500';
                                $icon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/>';
                            } elseif ($health === 'Needs Attention') {
                                $healthClass = 'text-amber-600 dark:text-amber-400';
                                $barClass = 'bg-amber-500';
                                $icon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"/>';
                            } else {
                                $healthClass = 'text-red-600 dark:text-red-400';
                                $barClass = 'bg-red-500';
                                $icon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8v4m0 4h.01M4.93 19h14.14c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.2 16c-.77 1.33.19 3 1.73 3z"/>';
                            }
                        @endphp
                        <tr class="provider-row border-t border-gray-100 dark:border-gray-700">
                            <td class="px-5 py-3.5 font-medium text-gray-900 dark:text-gray-100">{{ $provider['provider'] }}</td>
                            <td class="px-5 py-3.5 text-gray-700 dark:text-gray-300">{{ number_format($provider['sent']) }}</td>
                            <td class="px-5 py-3.5 text-gray-700 dark:text-gray-300">{{ number_format($provider['delivered']) }} ({{ number_format($provider['delivery_pct'], 1) }}%)</td>
                            <td class="px-5 py-3.5 text-gray-700 dark:text-gray-300">
                                <div class="min-w-[110px]">
                                    <div class="text-xs mb-1">{{ number_format($provider['bounce_rate'], 1) }}%</div>
                                    <div class="h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                        <div class="h-full {{ $barClass }} rounded-full" style="width: {{ min(100, $provider['bounce_rate'] * 10) }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center gap-1.5 {{ $healthClass }}">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">{!! $icon !!}</svg>
                                    {{ $health }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-400 dark:text-gray-500">No provider-level data available yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif {{-- end deliverability tab --}}

    {{-- ═══════════════════════════════════ A/B TESTING TAB ═══════════════════════════════════ --}}
    @if($activeTab === 'ab-testing')
    @php
        $abConfig = $abTestConfig ?? [
            'test_type' => 'subject',
            'test_group_percent' => 20,
            'winner_group_percent' => 80,
            'winning_metric' => 'open_rate',
            'duration_hours' => 4,
        ];
        $abVariantsData = collect($abTestVariants ?? [])->values();
        $abTotalRecipients = max(0, (int) ($stats['total_recipients'] ?? 0));
        $abTestRecipients = (int) round($abTotalRecipients * ((int) ($abConfig['test_group_percent'] ?? 20) / 100));
        $abWinnerRecipients = max(0, $abTotalRecipients - $abTestRecipients);
    @endphp

    <div class="mb-2">
        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Configure A/B Test</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Set up your test variants and choose how to determine the winner.</p>
    </div>

    <form method="POST" action="{{ route('customer.campaigns.ab-test.store', $campaign) }}" id="abTestForm" data-total-recipients="{{ $abTotalRecipients }}">
        @csrf
        <input type="hidden" name="test_type" id="abTestTypeInput" value="{{ $abConfig['test_type'] ?? 'subject' }}">

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 items-start">
            <div class="xl:col-span-2 space-y-5">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">1. What would you like to test?</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        @php
                            $abTypes = [
                                'subject' => ['label' => 'Subject Line', 'desc' => 'Test different subject lines to see which gets more opens.'],
                                'content' => ['label' => 'Email Content', 'desc' => 'Test different designs, images, or copy variants.'],
                                'sender' => ['label' => 'From Name', 'desc' => 'Test sender names to improve brand recognition.'],
                            ];
                        @endphp
                        @foreach($abTypes as $typeKey => $typeMeta)
                            @php $isTypeActive = ($abConfig['test_type'] ?? 'subject') === $typeKey; @endphp
                            <button type="button" data-type="{{ $typeKey }}"
                                class="ab-type-card text-left rounded-xl border p-4 transition-all duration-150
                                       {{ $isTypeActive
                                            ? 'border-primary-500 bg-primary-50/70 dark:bg-primary-900/20 ring-1 ring-primary-500/40'
                                            : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600' }}">
                                <div class="text-base font-semibold {{ $isTypeActive ? 'text-primary-600 dark:text-primary-400' : 'text-gray-900 dark:text-gray-100' }}">{{ $typeMeta['label'] }}</div>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ $typeMeta['desc'] }}</p>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">2. Define your variants</h4>

                    <div id="abVariantsContainer" class="space-y-3">
                        @foreach($abVariantsData as $idx => $variant)
                            @php
                                $letter = chr(65 + $idx);
                                $variantLabel = $variant['name'] ?? ('Variant ' . $letter);
                                $splitPct = (int) ($variant['split_percentage'] ?? 0);
                                $variantRecipients = $abTestRecipients > 0 ? (int) round($abTestRecipients * ($splitPct / 100)) : 0;
                            @endphp
                            <div class="ab-variant-row border border-gray-100 dark:border-gray-700 rounded-xl p-3">
                                <div class="flex items-center justify-between gap-3 mb-2">
                                    <div class="inline-flex items-center gap-2 text-sm font-semibold text-primary-600 dark:text-primary-400">
                                        <span class="ab-variant-badge inline-flex items-center justify-center w-6 h-6 rounded-md bg-primary-100 dark:bg-primary-900/30">{{ $letter }}</span>
                                        <input type="text" name="variants[{{ $idx }}][name]" value="{{ $variantLabel }}" class="ab-variant-name-input bg-transparent border-0 p-0 text-sm font-semibold focus:outline-none focus:ring-0 text-primary-600 dark:text-primary-400">
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="ab-share text-xs text-gray-400 dark:text-gray-500">{{ $splitPct }}% of test group ({{ number_format($variantRecipients) }})</span>
                                        <button type="button" class="ab-remove-variant hidden text-gray-400 hover:text-red-500 transition-colors" title="Remove variant">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="ab-subject-field">
                                    <input type="text" name="variants[{{ $idx }}][subject]" value="{{ $variant['subject'] ?? '' }}"
                                        class="ab-subject-input w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2 text-sm text-gray-800 dark:text-gray-200 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20"
                                        placeholder="Variant subject line">
                                </div>
                                <div class="ab-content-fields mt-3">
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Template</label>
                                    <select name="variants[{{ $idx }}][template_id]"
                                        class="ab-template-select w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2 text-sm text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500/20">
                                        <option value="">Use campaign template</option>
                                        @foreach(($abTemplateOptions ?? []) as $tpl)
                                            <option value="{{ $tpl['id'] }}" {{ (int) ($variant['template_id'] ?? 0) === (int) $tpl['id'] ? 'selected' : '' }}>{{ $tpl['name'] }}</option>
                                        @endforeach
                                    </select>

                                    <div class="ab-template-preview mt-3 border border-dashed border-gray-200 dark:border-gray-700 rounded-xl p-3 bg-gray-50/60 dark:bg-gray-900/30">
                                        <div class="flex items-start gap-3">
                                            <img class="ab-template-thumb hidden w-14 h-14 rounded-lg object-cover border border-gray-200 dark:border-gray-700" alt="Template thumbnail">
                                            <div class="min-w-0 flex-1">
                                                <div class="ab-template-preview-name text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">No template selected</div>
                                                <p class="ab-template-preview-desc text-xs text-gray-500 dark:text-gray-400 mt-1">Select a template to preview the builder layout.</p>
                                                <a href="#" target="_blank" class="ab-template-preview-link hidden mt-2 items-center gap-1 text-xs text-primary-600 dark:text-primary-400 hover:underline">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 3h7m0 0v7m0-7L10 14"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5v14h14"/></svg>
                                                    Preview Builder
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="ab-sender-fields mt-3 space-y-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">From Name</label>
                                        <input type="text" name="variants[{{ $idx }}][from_name]" value="{{ $variant['from_name'] ?? '' }}"
                                            class="ab-from-name-input w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2 text-sm text-gray-700 dark:text-gray-300 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20"
                                            placeholder="Sender display name">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Delivery Server</label>
                                        <select name="variants[{{ $idx }}][delivery_server_id]"
                                            class="ab-delivery-server-select w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2 text-sm text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500/20">
                                            <option value="">Use campaign delivery server</option>
                                            @foreach(($abDeliveryServerOptions ?? []) as $server)
                                                <option value="{{ $server['id'] }}" {{ (int) ($variant['delivery_server_id'] ?? 0) === (int) $server['id'] ? 'selected' : '' }}>{{ $server['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">From Email</label>
                                        <input type="text" value="{{ $variant['from_email'] ?? '' }}"
                                            class="ab-from-email-display w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/60 px-3 py-2 text-sm text-gray-700 dark:text-gray-300"
                                            placeholder="Will be auto-filled from selected server" readonly>
                                        <input type="hidden" name="variants[{{ $idx }}][from_email]" value="{{ $variant['from_email'] ?? '' }}" class="ab-from-email-input">
                                    </div>
                                </div>

                                <input type="number" name="variants[{{ $idx }}][split_percentage]" value="{{ $splitPct }}" min="1" max="100" class="ab-split-input sr-only">
                            </div>
                        @endforeach
                    </div>

                    <template id="abVariantTemplate">
                        <div class="ab-variant-row border border-gray-100 dark:border-gray-700 rounded-xl p-3">
                            <div class="flex items-center justify-between gap-3 mb-2">
                                <div class="inline-flex items-center gap-2 text-sm font-semibold text-primary-600 dark:text-primary-400">
                                    <span class="ab-variant-badge inline-flex items-center justify-center w-6 h-6 rounded-md bg-primary-100 dark:bg-primary-900/30">A</span>
                                    <input type="text" name="variants[__INDEX__][name]" value="Variant A" class="ab-variant-name-input bg-transparent border-0 p-0 text-sm font-semibold focus:outline-none focus:ring-0 text-primary-600 dark:text-primary-400">
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="ab-share text-xs text-gray-400 dark:text-gray-500">0% of test group (0)</span>
                                    <button type="button" class="ab-remove-variant text-gray-400 hover:text-red-500 transition-colors" title="Remove variant">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </div>
                            <div class="ab-subject-field">
                                <input type="text" name="variants[__INDEX__][subject]" value=""
                                    class="ab-subject-input w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2 text-sm text-gray-800 dark:text-gray-200 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20"
                                    placeholder="Variant subject line">
                            </div>
                            <div class="ab-content-fields mt-3">
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Template</label>
                                <select name="variants[__INDEX__][template_id]"
                                    class="ab-template-select w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2 text-sm text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500/20">
                                    <option value="">Use campaign template</option>
                                    @foreach(($abTemplateOptions ?? []) as $tpl)
                                        <option value="{{ $tpl['id'] }}">{{ $tpl['name'] }}</option>
                                    @endforeach
                                </select>

                                <div class="ab-template-preview mt-3 border border-dashed border-gray-200 dark:border-gray-700 rounded-xl p-3 bg-gray-50/60 dark:bg-gray-900/30">
                                    <div class="flex items-start gap-3">
                                        <img class="ab-template-thumb hidden w-14 h-14 rounded-lg object-cover border border-gray-200 dark:border-gray-700" alt="Template thumbnail">
                                        <div class="min-w-0 flex-1">
                                            <div class="ab-template-preview-name text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">No template selected</div>
                                            <p class="ab-template-preview-desc text-xs text-gray-500 dark:text-gray-400 mt-1">Select a template to preview the builder layout.</p>
                                            <a href="#" target="_blank" class="ab-template-preview-link hidden mt-2 items-center gap-1 text-xs text-primary-600 dark:text-primary-400 hover:underline">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 3h7m0 0v7m0-7L10 14"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5v14h14"/></svg>
                                                Preview Builder
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="ab-sender-fields mt-3 space-y-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">From Name</label>
                                    <input type="text" name="variants[__INDEX__][from_name]" value=""
                                        class="ab-from-name-input w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2 text-sm text-gray-700 dark:text-gray-300 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20"
                                        placeholder="Sender display name">
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Delivery Server</label>
                                    <select name="variants[__INDEX__][delivery_server_id]"
                                        class="ab-delivery-server-select w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2 text-sm text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500/20">
                                        <option value="">Use campaign delivery server</option>
                                        @foreach(($abDeliveryServerOptions ?? []) as $server)
                                            <option value="{{ $server['id'] }}">{{ $server['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">From Email</label>
                                    <input type="text" value=""
                                        class="ab-from-email-display w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/60 px-3 py-2 text-sm text-gray-700 dark:text-gray-300"
                                        placeholder="Will be auto-filled from selected server" readonly>
                                    <input type="hidden" name="variants[__INDEX__][from_email]" value="" class="ab-from-email-input">
                                </div>
                            </div>

                            <input type="number" name="variants[__INDEX__][split_percentage]" value="0" min="1" max="100" class="ab-split-input sr-only">
                        </div>
                    </template>

                    <div class="mt-3 flex items-center justify-between gap-3">
                        <button type="button" id="abAddVariant"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Add another variant
                        </button>
                        <p id="abSplitTotal" class="text-xs text-gray-500 dark:text-gray-400">Total split: 100%</p>
                    </div>
                </div>
            </div>

            <div class="space-y-5 xl:sticky xl:top-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-6">3. Test Settings</h4>

                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm text-gray-700 dark:text-gray-300">Audience Split</span>
                            <span id="abSplitHeadline" class="text-sm font-semibold text-primary-600 dark:text-primary-400">{{ $abConfig['test_group_percent'] }}% Test / {{ $abConfig['winner_group_percent'] }}% Winner</span>
                        </div>
                        <input id="abAudienceSplitSlider" type="range" min="5" max="50" step="5" name="test_group_percent" value="{{ $abConfig['test_group_percent'] }}"
                            class="w-full accent-primary-600 cursor-pointer">
                        <div class="mt-2 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                            <span id="abTestGroupText">Test Group: {{ number_format($abTestRecipients) }}</span>
                            <span id="abWinnerGroupText">Winner Group: {{ number_format($abWinnerRecipients) }}</span>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-700 space-y-4">
                        <div>
                            <label class="block text-sm text-gray-700 dark:text-gray-300 mb-2">Winning Metric</label>
                            <select name="winning_metric" class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2 text-sm text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500/20">
                                <option value="open_rate" {{ ($abConfig['winning_metric'] ?? 'open_rate') === 'open_rate' ? 'selected' : '' }}>Open Rate</option>
                                <option value="click_rate" {{ ($abConfig['winning_metric'] ?? 'open_rate') === 'click_rate' ? 'selected' : '' }}>Click Rate</option>
                                <option value="conversion_rate" {{ ($abConfig['winning_metric'] ?? 'open_rate') === 'conversion_rate' ? 'selected' : '' }}>Conversion Rate</option>
                                <option value="ctor" {{ ($abConfig['winning_metric'] ?? 'open_rate') === 'ctor' ? 'selected' : '' }}>Click-to-Open Rate (CTOR)</option>
                                <option value="revenue_per_email" {{ ($abConfig['winning_metric'] ?? 'open_rate') === 'revenue_per_email' ? 'selected' : '' }}>Revenue per Email</option>
                                <option value="unsubscribe_rate" {{ ($abConfig['winning_metric'] ?? 'open_rate') === 'unsubscribe_rate' ? 'selected' : '' }}>Unsubscribe Rate</option>
                                <option value="bounce_rate" {{ ($abConfig['winning_metric'] ?? 'open_rate') === 'bounce_rate' ? 'selected' : '' }}>Lowest Bounce Rate</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-700 dark:text-gray-300 mb-2">Test Duration</label>
                            <select name="duration_hours" class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2 text-sm text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500/20">
                                @foreach([1,2,4,8,12,24] as $hours)
                                    <option value="{{ $hours }}" {{ (int) ($abConfig['duration_hours'] ?? 4) === $hours ? 'selected' : '' }}>{{ $hours }} {{ $hours === 1 ? 'Hour' : 'Hours' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <p class="text-xs text-gray-500 dark:text-gray-400">The winning variant will automatically be sent to the remaining recipients after the selected test duration.</p>
                    </div>
                </div>

                <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-lg bg-primary-600 hover:bg-primary-700 text-white font-medium transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.868v4.264a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Start A/B Test
                </button>
            </div>
        </div>
    </form>
    @endif {{-- end ab-testing tab --}}

    {{-- ═══════════════════════════════════ INBOX ROTATION TAB ═══════════════════════════════════ --}}
    @if($activeTab === 'inbox-rotation')
    @php
        $rotation = $inboxRotationData ?? [];
        $rotationServers = collect($inboxRotationServers ?? []);
        $rotationEnabled = (bool) ($rotation['enabled'] ?? false);
        $rotationSelectedCount = $rotationServers->count();
        $rotationDailyCap = $rotation['daily_cap'] ?? null;
        $rotationMinDelay = $rotation['min_delay'] ?? null;
        $rotationMaxDelay = $rotation['max_delay'] ?? null;
        $rotationDays = collect($rotation['schedule_days'] ?? [])->filter()->values();
        $rotationStartTime = $rotation['start_time'] ?? null;
        $rotationEndTime = $rotation['end_time'] ?? null;
        $rotationTimezone = $rotation['timezone'] ?? null;
        $rotationPauseOnBounce = $rotation['pause_on_bounce'] ?? null;
        $rotationMaxBounceRate = $rotation['max_bounce_rate'] ?? null;
        $rotationExcludeGenericRoles = $rotation['exclude_generic_roles'] ?? null;
        $rotationHasSchedule = $rotationDays->isNotEmpty() || $rotationStartTime || $rotationEndTime || $rotationTimezone;
        $selectedRotationServer = $selectedInboxRotationServer ?? null;
        $selectedRotationLogs = collect($selectedInboxRotationLogs ?? []);
        $selectedRotationLogStats = $selectedInboxRotationLogStats ?? [];
        $selectedRotationLogCounts = $selectedInboxRotationLogCounts ?? ['all' => 0, 'sends' => 0, 'errors' => 0, 'system' => 0];
        $rotationEventFilter = $selectedRotationLogStats['event_filter'] ?? 'all';
        $rotationSearch = $selectedRotationLogStats['search'] ?? '';
    @endphp

    <div class="space-y-5">
        @if($selectedRotationServer)
            <div class="space-y-5">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div class="min-w-0">
                        <a href="{{ route('customer.campaigns.show', ['campaign' => $campaign, 'tab' => 'inbox-rotation']) }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 19l-7-7 7-7"/></svg>
                            Back to inboxes
                        </a>
                        <div class="mt-2 flex flex-wrap items-center gap-3">
                            <h3 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 break-all">{{ $selectedRotationServer->from_email ?: $selectedRotationServer->name }}</h3>
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $rotationEnabled ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">{{ $rotationEnabled ? 'Running' : 'Disabled' }}</span>
                        </div>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $selectedRotationServer->name }}{{ $selectedRotationServer->from_email ? ' • ' . $selectedRotationServer->from_email : '' }}</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <x-button href="{{ route('customer.campaigns.edit', $campaign) }}" variant="secondary">Manage Rotation</x-button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Sent Today</p>
                        <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format((int) ($selectedRotationLogStats['sent_count'] ?? 0)) }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Across {{ number_format((int) ($selectedRotationLogStats['recipient_count'] ?? 0)) }} recipients tied to this inbox.</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Delivered</p>
                        <p class="mt-2 text-3xl font-semibold text-emerald-600 dark:text-emerald-400">{{ number_format((int) ($selectedRotationLogStats['delivered_count'] ?? 0)) }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Accepted events that reached delivered status.</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Open Rate</p>
                        <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format((float) ($selectedRotationLogStats['open_rate'] ?? 0), 1) }}%</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ number_format((int) ($selectedRotationLogStats['opened_count'] ?? 0)) }} unique opens</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Bounces</p>
                        <p class="mt-2 text-3xl font-semibold text-red-500 dark:text-red-400">{{ number_format((int) ($selectedRotationLogStats['bounced_count'] ?? 0)) }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Unique bounced recipients for this inbox.</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Activity Logs</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Review send, engagement, and error events associated with this inbox rotation path.</p>
                        </div>

                        <form method="GET" action="{{ route('customer.campaigns.show', $campaign) }}" class="flex flex-col gap-3 xl:flex-row xl:items-center">
                            <input type="hidden" name="tab" value="inbox-rotation">
                            <input type="hidden" name="inbox_rotation_server" value="{{ $selectedRotationServer->id }}">

                            <div class="flex flex-wrap items-center gap-2">
                                @foreach(['all' => 'All Events', 'sends' => 'Sends', 'errors' => 'Errors', 'system' => 'System'] as $filterKey => $filterLabel)
                                    <button type="submit" name="rotation_event" value="{{ $filterKey }}"
                                        class="inline-flex items-center rounded-lg px-3 py-2 text-sm font-medium transition-colors {{ $rotationEventFilter === $filterKey ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600' }}">
                                        {{ $filterLabel }}
                                        <span class="ml-2 text-xs {{ $rotationEventFilter === $filterKey ? 'text-white/80' : 'text-gray-400 dark:text-gray-400' }}">{{ number_format((int) ($selectedRotationLogCounts[$filterKey] ?? 0)) }}</span>
                                    </button>
                                @endforeach
                            </div>

                            <div class="flex items-center gap-2">
                                <input type="text" name="rotation_search" value="{{ $rotationSearch }}" placeholder="Search recipient..."
                                    class="w-full xl:w-64 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2 text-sm text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500/20">
                                <button type="submit" class="inline-flex items-center rounded-lg bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-800 dark:bg-primary-600 dark:hover:bg-primary-700">Apply</button>
                            </div>
                        </form>
                    </div>

                    @if($selectedRotationLogs->isEmpty())
                        <div class="px-5 py-10 text-center">
                            <p class="text-sm text-gray-500 dark:text-gray-400">No log events matched this inbox yet.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[860px]">
                                <thead>
                                    <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/40">
                                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Time</th>
                                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Event</th>
                                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Recipient</th>
                                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Details</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @foreach($selectedRotationLogs as $event)
                                        @php
                                            $eventName = \Illuminate\Support\Str::headline((string) $event->event);
                                            $eventBadge = match ($event->event) {
                                                'failed', 'blocked_by_spam_filter' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                                                'bounced', 'complained' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                                                'opened', 'clicked' => 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300',
                                                'accepted', 'delivered', 'replied' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                                                default => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                                            };
                                            $recipientDisplay = trim((string) (($event->recipient->first_name ?? '') . ' ' . ($event->recipient->last_name ?? '')));
                                            $detailText = (string) ($event->meta['message'] ?? '');

                                            if ($detailText === '') {
                                                $detailText = match ($event->event) {
                                                    'accepted' => 'Email accepted by sending server',
                                                    'delivered' => 'Delivery confirmed by provider webhook',
                                                    'opened' => 'Recipient opened the email',
                                                    'clicked' => 'Recipient clicked a tracked link',
                                                    'bounced' => 'Message bounced for this recipient',
                                                    'failed' => $event->error_message ?: 'Failed to send via this inbox',
                                                    'blocked_by_spam_filter' => $event->error_message ?: 'Blocked before sending because of spam score',
                                                    'complained' => 'Recipient marked the message as spam',
                                                    default => $event->error_message ?: 'Campaign event recorded',
                                                };
                                            }
                                        @endphp
                                        <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-700/30 transition-colors">
                                            <td class="px-5 py-4 align-top text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                                <div>{{ optional($event->created_at)->format('M j, Y') }}</div>
                                                <div class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ optional($event->created_at)->format('g:i A') }}</div>
                                            </td>
                                            <td class="px-5 py-4 align-top">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $eventBadge }}">{{ $eventName }}</span>
                                            </td>
                                            <td class="px-5 py-4 align-top text-sm text-gray-700 dark:text-gray-300">
                                                @if($recipientDisplay !== '')
                                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $recipientDisplay }}</div>
                                                @endif
                                                <div class="{{ $recipientDisplay !== '' ? 'mt-1 text-xs text-gray-500 dark:text-gray-400' : 'font-medium text-gray-900 dark:text-gray-100' }}">
                                                    {{ $event->recipient?->email ?? ($event->meta['email'] ?? 'System') }}
                                                </div>
                                            </td>
                                            <td class="px-5 py-4 align-top text-sm text-gray-700 dark:text-gray-300">
                                                <div>{{ $detailText }}</div>
                                                @if(!empty($event->url))
                                                    <div class="mt-1 text-xs break-all text-blue-600 dark:text-blue-400">{{ $event->url }}</div>
                                                @endif
                                                @if(!empty($event->error_message) && $event->error_message !== $detailText)
                                                    <div class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $event->error_message }}</div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Inbox Rotation</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Review which delivery servers are assigned to this campaign's rotation pool and how traffic is distributed.</p>
                </div>

                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $rotationEnabled ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">
                        {{ $rotationEnabled ? 'Enabled' : 'Disabled' }}
                    </span>
                    <x-button href="{{ route('customer.campaigns.edit', $campaign) }}" variant="secondary">Manage Rotation</x-button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
                <p class="text-sm text-gray-500 dark:text-gray-400">Assigned Inboxes</p>
                <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $rotationSelectedCount }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Delivery servers currently included in this rotation pool.</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
                <p class="text-sm text-gray-500 dark:text-gray-400">Daily Cap</p>
                <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $rotationDailyCap ? number_format((int) $rotationDailyCap) : '—' }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Per-inbox daily limit from your campaign rotation settings.</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
                <p class="text-sm text-gray-500 dark:text-gray-400">Send Delay</p>
                <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">
                    @if($rotationMinDelay !== null || $rotationMaxDelay !== null)
                        {{ $rotationMinDelay ?? '—' }} - {{ $rotationMaxDelay ?? '—' }}
                    @else
                        —
                    @endif
                </p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Minutes between sends when rotation is active.</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
                <p class="text-sm text-gray-500 dark:text-gray-400">Bounce Protection</p>
                <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $rotationPauseOnBounce ? 'On' : 'Off' }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ $rotationMaxBounceRate !== null ? 'Threshold: ' . rtrim(rtrim(number_format((float) $rotationMaxBounceRate, 2), '0'), '.') . '%' : 'No bounce threshold configured.' }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 items-start">
            <div class="xl:col-span-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between gap-3">
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Assigned Inboxes</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Delivery servers selected for this campaign's inbox rotation pool.</p>
                    </div>
                    <span class="text-xs font-medium text-gray-400 dark:text-gray-500">{{ $rotationSelectedCount }} selected</span>
                </div>

                @if($rotationServers->isEmpty())
                    <div class="px-5 py-10 text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">No rotation inboxes are currently assigned to this campaign.</p>
                        <a href="{{ route('customer.campaigns.edit', $campaign) }}" class="mt-2 inline-flex items-center text-sm font-medium text-primary-600 dark:text-primary-400 hover:underline">Edit campaign rotation settings</a>
                    </div>
                @else
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($rotationServers as $server)
                            @php $isSelectedRotationServer = $selectedRotationServer && (int) $selectedRotationServer->id === (int) $server->id; @endphp
                            <a href="{{ route('customer.campaigns.show', ['campaign' => $campaign, 'tab' => 'inbox-rotation', 'inbox_rotation_server' => $server->id]) }}"
                               class="block px-5 py-4 transition-colors {{ $isSelectedRotationServer ? 'bg-primary-50/70 dark:bg-primary-900/10' : 'hover:bg-gray-50/70 dark:hover:bg-gray-700/30' }}">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="min-w-0">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ $server->name }}</div>
                                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400 break-all">{{ $server->from_email ?: 'No from email configured' }}</div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if($isSelectedRotationServer)
                                            <span class="inline-flex items-center rounded-full bg-primary-100 px-2.5 py-1 text-xs font-medium text-primary-700 dark:bg-primary-900/30 dark:text-primary-300">Viewing Logs</span>
                                        @endif
                                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">Selected</span>
                                        @if($rotationDailyCap)
                                            <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">Cap {{ number_format((int) $rotationDailyCap) }}/day</span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="space-y-5">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Schedule</h4>
                    <div class="mt-4 space-y-3 text-sm text-gray-600 dark:text-gray-300">
                        <div>
                            <div class="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500">Active Days</div>
                            <div class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $rotationDays->isNotEmpty() ? $rotationDays->implode(', ') : 'Not configured' }}</div>
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500">Time Window</div>
                            <div class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">
                                @if($rotationStartTime || $rotationEndTime)
                                    {{ $rotationStartTime ?: '—' }} - {{ $rotationEndTime ?: '—' }}
                                @else
                                    Not configured
                                @endif
                            </div>
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500">Timezone</div>
                            <div class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $rotationTimezone ?: 'Not configured' }}</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Safety & Limits</h4>
                    <dl class="mt-4 space-y-3">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Pause on high bounce rate</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $rotationPauseOnBounce ? 'Enabled' : 'Disabled' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Maximum bounce rate</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $rotationMaxBounceRate !== null ? rtrim(rtrim(number_format((float) $rotationMaxBounceRate, 2), '0'), '.') . '%' : 'Not configured' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Exclude generic roles</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $rotationExcludeGenericRoles ? 'Enabled' : 'Disabled' }}</dd>
                        </div>
                    </dl>
                </div>

                @if(!$rotationEnabled || !$rotationHasSchedule)
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 dark:border-amber-900/40 dark:bg-amber-900/20">
                        <p class="text-sm text-amber-800 dark:text-amber-200">
                            {{ !$rotationEnabled ? 'Inbox rotation is currently turned off for this campaign.' : 'Rotation is enabled, but the sending schedule is only partially configured.' }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif {{-- end inbox-rotation tab --}}

    {{-- ═══════════════════════════════════ AUTOMATIONS TAB ═══════════════════════════════════ --}}
    @if($activeTab === 'automations')
    @php
        $automationItems = collect($campaignAutomations ?? []);
    @endphp

    <div class="space-y-5">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Automations</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Create a new automation or open any automation in the full-screen builder.</p>
                </div>

                <div class="flex items-center gap-2">
                    <x-button href="{{ route('customer.automations.index') }}" variant="secondary">View All</x-button>
                    <x-button href="{{ route('customer.automations.create') }}" variant="primary">New Automation</x-button>
                </div>
            </div>

            <form method="POST" action="{{ route('customer.automations.store') }}" class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-end">
                @csrf
                <div class="flex-1">
                    <label for="campaignAutomationName" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Quick Create</label>
                    <input
                        id="campaignAutomationName"
                        type="text"
                        name="name"
                        value="{{ old('name', $campaign->name . ' Automation') }}"
                        placeholder="Enter automation name"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                        required
                    >
                </div>
                <x-button type="submit" variant="primary" class="sm:shrink-0">Create & Open Builder</x-button>
            </form>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Your Recent Automations</h4>
            </div>

            @if($automationItems->isEmpty())
                <div class="px-5 py-10 text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">No automations found yet.</p>
                    <a href="{{ route('customer.automations.create') }}" class="mt-2 inline-flex items-center text-sm font-medium text-primary-600 dark:text-primary-400 hover:underline">Create your first automation</a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/40">
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Automation</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Status</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Runs</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Active Runs</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($automationItems as $automation)
                                <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-700/30 transition-colors">
                                    <td class="px-5 py-3">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $automation->name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Updated {{ $automation->updated_at?->diffForHumans() }}</div>
                                    </td>
                                    <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-300">{{ ucfirst($automation->status ?? 'draft') }}</td>
                                    <td class="px-5 py-3 text-right text-sm text-gray-700 dark:text-gray-300">{{ (int) ($automation->runs_total ?? 0) }}</td>
                                    <td class="px-5 py-3 text-right text-sm text-gray-700 dark:text-gray-300">{{ (int) ($automation->runs_active ?? 0) }}</td>
                                    <td class="px-5 py-3 text-right">
                                        <x-button href="{{ route('customer.automations.edit', $automation) }}" variant="primary" class="text-xs">Open Builder</x-button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
    @endif {{-- end automations tab --}}

    {{-- ═══════════════════════════════════ LOGS TAB ═══════════════════════════════════ --}}
    @if($activeTab === 'logs')
    <div class="space-y-5">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Campaign Logs</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Review pause, fail, and delivery events to understand campaign state and errors.</p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColor }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $statusDot }}"></span>
                    {{ ucfirst($campaignLogsData['status'] ?? $campaign->status) }}
                </span>
            </div>

            <div class="p-5 space-y-4">
                @if(!empty($campaignLogsData['failure_reason']))
                    <div class="rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 p-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-red-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                            <div>
                                <h4 class="text-sm font-semibold text-red-800 dark:text-red-200">Failure reason</h4>
                                <p class="mt-1 text-sm text-red-700 dark:text-red-300">{{ $campaignLogsData['failure_reason'] }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if(!empty($campaignLogsData['preflight_issues']))
                    <div class="rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 p-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <div>
                                <h4 class="text-sm font-semibold text-amber-800 dark:text-amber-200">Preflight issues</h4>
                                <ul class="mt-2 space-y-1 text-sm text-amber-700 dark:text-amber-300">
                                    @foreach($campaignLogsData['preflight_issues'] as $issue)
                                        <li>{{ $issue }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                    <div class="bg-gray-50 dark:bg-gray-900/30 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Top failure breakdown</h4>
                        </div>
                        <div class="p-4">
                            @if(($campaignLogsData['error_breakdown'] ?? collect())->isEmpty())
                                <p class="text-sm text-gray-500 dark:text-gray-400">No failure reasons have been recorded yet.</p>
                            @else
                                <div class="space-y-3">
                                    @foreach($campaignLogsData['error_breakdown'] as $error)
                                        <div class="flex items-start justify-between gap-3">
                                            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $error->failure_reason }}</p>
                                            <span class="shrink-0 inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300">{{ number_format($error->count) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-900/30 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Recent event activity</h4>
                        </div>
                        <div class="p-4">
                            @if(($campaignLogsData['recent_events'] ?? collect())->isEmpty())
                                <p class="text-sm text-gray-500 dark:text-gray-400">No campaign log events have been recorded yet.</p>
                            @else
                                <div class="space-y-3 max-h-[520px] overflow-y-auto pr-1">
                                    @foreach($campaignLogsData['recent_events'] as $event)
                                        @php
                                            $eventName = \Illuminate\Support\Str::headline((string) $event->event);
                                            $eventBadge = match ($event->event) {
                                                'failed', 'blocked_by_spam_filter' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                                                'bounced' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                                                'opened', 'clicked', 'accepted', 'delivered', 'replied' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                                                default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                            };
                                            $recipientName = trim((string) (($event->recipient->first_name ?? '') . ' ' . ($event->recipient->last_name ?? '')));
                                        @endphp
                                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-3">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{ $eventBadge }}">{{ $eventName }}</span>
                                                        <span class="text-xs text-gray-400 dark:text-gray-500">{{ optional($event->created_at)->diffForHumans() }}</span>
                                                    </div>
                                                    @if($recipientName !== '' || !empty($event->recipient?->email))
                                                        <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                                                            {{ $recipientName !== '' ? $recipientName : $event->recipient?->email }}
                                                            @if($recipientName !== '' && !empty($event->recipient?->email))
                                                                <span class="text-gray-400 dark:text-gray-500">&lt;{{ $event->recipient->email }}&gt;</span>
                                                            @endif
                                                        </p>
                                                    @endif
                                                    @if(!empty($event->error_message))
                                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $event->error_message }}</p>
                                                    @endif
                                                    @if(!empty($event->url))
                                                        <p class="mt-1 text-xs break-all text-blue-600 dark:text-blue-400">{{ $event->url }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif {{-- end logs tab --}}
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    const isDark = () => document.documentElement.classList.contains('dark');
    const gridColor = () => isDark() ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
    const textColor = () => isDark() ? '#9CA3AF' : '#6B7280';

    const engagementData = @json($engagementChartData);
    const deliverabilityChartData = @json($deliverabilityChartData ?? ['labels'=>[],'delivered'=>[],'bounced'=>[]]);
    const deviceData = @json($deviceData ?? []);
    const subscriberFeedbackData = @json($subscriberFeedbackChartData ?? ['labels'=>[],'unsubscribes'=>[],'complaints'=>[]]);
    const audienceBreakdownData = @json($subscriberInsights ?? ['active'=>0,'unengaged'=>0]);
    const bounceTimelineData = @json($bounceTimelineData ?? []);
    const deliverabilityInsights = @json($deliverabilityInsights ?? ['soft_bounces'=>0,'hard_bounces'=>0]);
    const abTestConfigData = @json($abTestConfig ?? []);
    const abTemplateOptionsData = @json($abTemplateOptions ?? []);
    const abDeliveryServerOptionsData = @json($abDeliveryServerOptions ?? []);

    function makeSparkline(id, color, dataPoints) {
        const el = document.getElementById(id);
        if (!el) return;
        new Chart(el, {
            type: 'line',
            data: {
                labels: dataPoints.map((_, i) => i),
                datasets: [{
                    data: dataPoints,
                    borderColor: color,
                    borderWidth: 1.5,
                    pointRadius: 0,
                    fill: true,
                    backgroundColor: color + '20',
                    tension: 0.4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                scales: { x: { display: false }, y: { display: false } },
                animation: false,
            }
        });
    }

    function initCharts() {
        if (typeof Chart === 'undefined') { setTimeout(initCharts, 100); return; }

        const opens  = engagementData.opens  || [];
        const clicks = engagementData.clicks || [];
        const n = Math.max(opens.length, clicks.length, 7);

        // Sparklines - build cumulative arrays from engagement data
        const pad = (arr, len) => { const a = [...arr]; while(a.length < len) a.unshift(0); return a; };
        const sentArr     = opens.length ? opens.map((v, i) => opens.slice(0, i+1).reduce((s,x)=>s+x,0)) : Array(n).fill(0).map((_,i)=>i);
        const delivArr    = opens.length ? opens.map((v, i) => Math.round(opens.slice(0, i+1).reduce((s,x)=>s+x,0) * 0.994)) : Array(n).fill(0).map((_,i)=>i);
        const opensArr    = pad(opens, n);
        const clicksArr   = pad(clicks, n);

        makeSparkline('sparkSent',      '#6B7280', sentArr);
        makeSparkline('sparkDelivered', '#10B981', delivArr);
        makeSparkline('sparkOpens',     '#3B82F6', opensArr);
        makeSparkline('sparkClicks',    '#8B5CF6', clicksArr);

        // Engagement Trends chart
        const engEl = document.getElementById('engagementChart');
        if (engEl) {
            const labels = engagementData.labels && engagementData.labels.length
                ? engagementData.labels
                : ['No Data'];
            const openVals  = engagementData.opens  && engagementData.opens.length  ? engagementData.opens  : [0];
            const clickVals = engagementData.clicks && engagementData.clicks.length ? engagementData.clicks : [0];

            window.__engChart = new Chart(engEl, {
                type: 'line',
                data: {
                    labels,
                    datasets: [
                        {
                            label: 'Opens',
                            data: openVals,
                            borderColor: '#3B82F6',
                            backgroundColor: '#3B82F620',
                            borderWidth: 2,
                            pointRadius: 3,
                            pointBackgroundColor: '#3B82F6',
                            fill: true,
                            tension: 0.4,
                        },
                        {
                            label: 'Clicks',
                            data: clickVals,
                            borderColor: '#8B5CF6',
                            backgroundColor: '#8B5CF620',
                            borderWidth: 2,
                            pointRadius: 3,
                            pointBackgroundColor: '#8B5CF6',
                            fill: true,
                            tension: 0.4,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: isDark() ? '#1F2937' : '#fff',
                            titleColor: textColor(),
                            bodyColor: textColor(),
                            borderColor: gridColor(),
                            borderWidth: 1,
                        }
                    },
                    scales: {
                        x: {
                            grid: { color: gridColor() },
                            ticks: { color: textColor(), font: { size: 11 }, maxTicksLimit: 7 },
                        },
                        y: {
                            grid: { color: gridColor() },
                            ticks: { color: textColor(), font: { size: 11 } },
                            beginAtZero: true,
                        }
                    }
                }
            });

            window.switchTrendView('opens-clicks');
        }

        function updateTemplatePreview(row) {
            if (!row) return;
            const select = row.querySelector('.ab-template-select');
            const nameEl = row.querySelector('.ab-template-preview-name');
            const descEl = row.querySelector('.ab-template-preview-desc');
            const thumbEl = row.querySelector('.ab-template-thumb');
            const linkEl = row.querySelector('.ab-template-preview-link');
            if (!select || !nameEl || !descEl || !thumbEl || !linkEl) return;

            const selectedId = Number(select.value || 0);
            const selectedTpl = abTemplateOptionsData.find(t => Number(t.id) === selectedId) || null;

            if (!selectedTpl) {
                nameEl.textContent = 'No template selected';
                descEl.textContent = 'Select a template to preview the builder layout.';
                thumbEl.classList.add('hidden');
                thumbEl.src = '';
                linkEl.classList.add('hidden');
                linkEl.classList.remove('inline-flex');
                linkEl.href = '#';
                return;
            }

            nameEl.textContent = selectedTpl.name || 'Selected template';
            descEl.textContent = selectedTpl.description || 'Template selected for this variant.';

            if (selectedTpl.thumbnail) {
                thumbEl.src = selectedTpl.thumbnail;
                thumbEl.classList.remove('hidden');
            } else {
                thumbEl.classList.add('hidden');
                thumbEl.src = '';
            }

            if (selectedTpl.preview_url) {
                linkEl.href = selectedTpl.preview_url;
                linkEl.classList.remove('hidden');
                linkEl.classList.add('inline-flex');
            } else {
                linkEl.classList.add('hidden');
                linkEl.classList.remove('inline-flex');
                linkEl.href = '#';
            }
        }

        function updateSenderPreview(row) {
            if (!row) return;
            const serverSelect = row.querySelector('.ab-delivery-server-select');
            const emailDisplay = row.querySelector('.ab-from-email-display');
            const emailInput = row.querySelector('.ab-from-email-input');
            if (!serverSelect || !emailDisplay || !emailInput) return;

            const selectedId = Number(serverSelect.value || 0);
            const selectedServer = abDeliveryServerOptionsData.find(s => Number(s.id) === selectedId) || null;
            const serverEmail = selectedServer?.from_email || '';

            emailDisplay.value = serverEmail;
            emailInput.value = serverEmail;
        }

        function applyTestTypeState(selectedType) {
            const type = selectedType || (typeInput?.value || 'subject');
            const showContent = type === 'content';
            const showSender = type === 'sender';
            const showSubject = type === 'subject';

            variantRows().forEach((row) => {
                const subjectField = row.querySelector('.ab-subject-field');
                const contentFields = row.querySelector('.ab-content-fields');
                const senderFields = row.querySelector('.ab-sender-fields');
                if (subjectField) subjectField.classList.toggle('hidden', !showSubject);
                if (contentFields) contentFields.classList.toggle('hidden', !showContent);
                if (senderFields) senderFields.classList.toggle('hidden', !showSender);
            });

            if (winningMetricSelect && type === 'content') {
                winningMetricSelect.value = 'click_rate';
            }
        }

        // OC Performance chart
        buildOcChart(ocDailyData.labels, ocDailyData.opens, ocDailyData.clicks);

        // Subscribers tab charts
        buildSubscriberFeedbackChart('daily');
        initAudienceSplitChart();

        // Deliverability tab charts
        buildBounceTimelineChart('daily');
        initBounceReasonsChart();

        // A/B testing tab interactions
        initAbTestingUi();

        // Devices donut chart
        const devEl = document.getElementById('devicesChart');
        if (devEl) {
            const devLabels = Object.keys(deviceData).length ? Object.keys(deviceData) : ['No Data'];
            const devValues = Object.values(deviceData).length ? Object.values(deviceData) : [1];
            const devColors = devLabels.map(l => ({'Mobile':'#1E5FEA','Desktop':'#8B5CF6','Tablet':'#E5E7EB'}[l] || '#9CA3AF'));
            new Chart(devEl, {
                type: 'doughnut',
                data: {
                    labels: devLabels,
                    datasets: [{ data: devValues, backgroundColor: devColors, borderWidth: 0, hoverOffset: 4 }]
                },
                options: {
                    responsive: false,
                    cutout: '70%',
                    plugins: { legend: { display: false }, tooltip: { enabled: Object.keys(deviceData).length > 0 } },
                    animation: { duration: 600 },
                }
            });
        }
    }

    // Trend view toggle
    window.switchTrendView = function(view) {
        document.querySelectorAll('.trend-btn').forEach(btn => {
            if (btn.dataset.view === view) {
                btn.classList.add('bg-primary-600', 'text-white');
                btn.classList.remove('text-gray-500', 'dark:text-gray-400', 'hover:bg-gray-100', 'dark:hover:bg-gray-700');
            } else {
                btn.classList.remove('bg-primary-600', 'text-white');
                btn.classList.add('text-gray-500', 'dark:text-gray-400', 'hover:bg-gray-100', 'dark:hover:bg-gray-700');
            }
        });

        const legend = document.getElementById('trendLegend');
        if (legend) {
            if (view === 'deliverability') {
                legend.innerHTML = `
                    <span class="flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                        <span class="w-3 h-0.5 bg-green-500 rounded-full inline-block"></span> Delivered
                    </span>
                    <span class="flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                        <span class="w-3 h-0.5 bg-red-500 rounded-full inline-block"></span> Bounced
                    </span>
                `;
            } else {
                legend.innerHTML = `
                    <span class="flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                        <span class="w-3 h-0.5 bg-blue-500 rounded-full inline-block"></span> Opens
                    </span>
                    <span class="flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                        <span class="w-3 h-0.5 bg-purple-500 rounded-full inline-block"></span> Clicks
                    </span>
                `;
            }
        }

        if (!window.__engChart) {
            return;
        }

        if (view === 'deliverability') {
            const labels = deliverabilityChartData.labels && deliverabilityChartData.labels.length
                ? deliverabilityChartData.labels
                : ['No Data'];
            const deliveredVals = deliverabilityChartData.delivered && deliverabilityChartData.delivered.length
                ? deliverabilityChartData.delivered
                : [0];
            const bouncedVals = deliverabilityChartData.bounced && deliverabilityChartData.bounced.length
                ? deliverabilityChartData.bounced
                : [0];

            window.__engChart.data.labels = labels;
            window.__engChart.data.datasets = [
                {
                    label: 'Delivered',
                    data: deliveredVals,
                    borderColor: '#10B981',
                    backgroundColor: '#10B98120',
                    borderWidth: 2,
                    pointRadius: 3,
                    pointBackgroundColor: '#10B981',
                    fill: true,
                    tension: 0.4,
                },
                {
                    label: 'Bounced',
                    data: bouncedVals,
                    borderColor: '#EF4444',
                    backgroundColor: '#EF444420',
                    borderWidth: 2,
                    pointRadius: 3,
                    pointBackgroundColor: '#EF4444',
                    fill: true,
                    tension: 0.4,
                }
            ];
        } else {
            const labels = engagementData.labels && engagementData.labels.length
                ? engagementData.labels
                : ['No Data'];
            const openVals = engagementData.opens && engagementData.opens.length
                ? engagementData.opens
                : [0];
            const clickVals = engagementData.clicks && engagementData.clicks.length
                ? engagementData.clicks
                : [0];

            window.__engChart.data.labels = labels;
            window.__engChart.data.datasets = [
                {
                    label: 'Opens',
                    data: openVals,
                    borderColor: '#3B82F6',
                    backgroundColor: '#3B82F620',
                    borderWidth: 2,
                    pointRadius: 3,
                    pointBackgroundColor: '#3B82F6',
                    fill: true,
                    tension: 0.4,
                },
                {
                    label: 'Clicks',
                    data: clickVals,
                    borderColor: '#8B5CF6',
                    backgroundColor: '#8B5CF620',
                    borderWidth: 2,
                    pointRadius: 3,
                    pointBackgroundColor: '#8B5CF6',
                    fill: true,
                    tension: 0.4,
                }
            ];
        }

        window.__engChart.update();
    };

    // Subscribers feedback timeline chart
    let subscriberFeedbackChart = null;

    function cumulativeValues(arr) {
        let run = 0;
        return arr.map(v => { run += Number(v || 0); return run; });
    }

    function buildSubscriberFeedbackChart(mode) {
        const el = document.getElementById('subscriberFeedbackChart');
        if (!el) return;

        const labels = subscriberFeedbackData.labels && subscriberFeedbackData.labels.length
            ? subscriberFeedbackData.labels
            : ['No data'];
        const dailyUnsubs = subscriberFeedbackData.unsubscribes && subscriberFeedbackData.unsubscribes.length
            ? subscriberFeedbackData.unsubscribes
            : [0];
        const dailyComplaints = subscriberFeedbackData.complaints && subscriberFeedbackData.complaints.length
            ? subscriberFeedbackData.complaints
            : [0];

        const unsubs = mode === 'cumulative' ? cumulativeValues(dailyUnsubs) : dailyUnsubs;
        const complaints = mode === 'cumulative' ? cumulativeValues(dailyComplaints) : dailyComplaints;

        if (subscriberFeedbackChart) {
            subscriberFeedbackChart.destroy();
            subscriberFeedbackChart = null;
        }

        subscriberFeedbackChart = new Chart(el, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Unsubscribes',
                        data: unsubs,
                        borderColor: '#EF4444',
                        backgroundColor: '#EF44441A',
                        borderWidth: 2,
                        pointRadius: 3,
                        fill: true,
                        tension: 0.35,
                    },
                    {
                        label: 'Complaints',
                        data: complaints,
                        borderColor: '#F59E0B',
                        backgroundColor: '#F59E0B1A',
                        borderWidth: 2,
                        pointRadius: 3,
                        fill: true,
                        tension: 0.35,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: isDark() ? '#1F2937' : '#fff',
                        titleColor: textColor(),
                        bodyColor: textColor(),
                        borderColor: gridColor(),
                        borderWidth: 1,
                    }
                },
                scales: {
                    x: {
                        grid: { color: gridColor() },
                        ticks: { color: textColor(), font: { size: 11 }, maxTicksLimit: 8 },
                    },
                    y: {
                        grid: { color: gridColor() },
                        ticks: { color: textColor(), font: { size: 11 } },
                        beginAtZero: true,
                    }
                }
            }
        });
    }

    window.switchSubscriberFeedbackMode = function(mode) {
        document.querySelectorAll('.sfb-btn').forEach(btn => {
            if (btn.dataset.mode === mode) {
                btn.classList.add('bg-primary-600', 'text-white');
                btn.classList.remove('text-gray-500', 'dark:text-gray-400', 'hover:bg-gray-100', 'dark:hover:bg-gray-700');
            } else {
                btn.classList.remove('bg-primary-600', 'text-white');
                btn.classList.add('text-gray-500', 'dark:text-gray-400', 'hover:bg-gray-100', 'dark:hover:bg-gray-700');
            }
        });
        buildSubscriberFeedbackChart(mode);
    };

    function initAudienceSplitChart() {
        const el = document.getElementById('audienceSplitChart');
        if (!el) return;
        if (window.__audienceSplitChart) {
            window.__audienceSplitChart.destroy();
            window.__audienceSplitChart = null;
        }

        const active = Number(audienceBreakdownData.active || 0);
        const unengaged = Number(audienceBreakdownData.unengaged || 0);
        const values = (active + unengaged) > 0 ? [active, unengaged] : [1, 0];

        window.__audienceSplitChart = new Chart(el, {
            type: 'doughnut',
            data: {
                labels: ['Engaged', 'Unengaged'],
                datasets: [{
                    data: values,
                    backgroundColor: ['#2563EB', isDark() ? '#4B5563' : '#E5E7EB'],
                    borderWidth: 0,
                    hoverOffset: 4,
                }]
            },
            options: {
                responsive: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: (active + unengaged) > 0 },
                },
                animation: { duration: 600 },
            }
        });
    }

    // Top engagers interactions
    window.filterTopEngagers = function(query) {
        const q = (query || '').toLowerCase().trim();
        document.querySelectorAll('.engager-row').forEach(row => {
            const hay = row.dataset.search || '';
            row.style.display = hay.includes(q) ? '' : 'none';
        });
    };

    window.exportTopEngagersCsv = function() {
        const table = document.getElementById('topEngagersTable');
        if (!table) return;

        const rows = Array.from(table.querySelectorAll('tbody tr.engager-row'));
        const lines = [['Subscriber', 'Email', 'Status', 'Total Opens', 'Total Clicks', 'Last Activity']];

        rows.forEach(row => {
            if (row.style.display === 'none') return;
            const cells = row.querySelectorAll('td');
            const subscriber = cells[0]?.querySelector('span.font-medium')?.textContent?.trim() || '';
            const email = cells[0]?.querySelector('span.text-xs')?.textContent?.trim() || subscriber;
            const status = cells[1]?.textContent?.trim() || '';
            const opens = cells[2]?.textContent?.trim() || '0';
            const clicks = cells[3]?.textContent?.trim() || '0';
            const lastActivity = cells[4]?.textContent?.trim() || '';
            lines.push([subscriber, email, status, opens, clicks, lastActivity]);
        });

        const csv = lines.map(cols => cols.map(v => `"${String(v).replace(/"/g, '""')}"`).join(',')).join('\n');
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'top-engagers.csv';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };

    // Deliverability charts and interactions
    let bounceTimelineChart = null;

    function buildBounceTimelineChart(view) {
        const el = document.getElementById('bounceTimelineChart');
        if (!el) return;

        const bucket = bounceTimelineData[view] || { labels: ['No data'], soft: [0], hard: [0] };
        const labels = bucket.labels && bucket.labels.length ? bucket.labels : ['No data'];
        const soft = bucket.soft && bucket.soft.length ? bucket.soft : [0];
        const hard = bucket.hard && bucket.hard.length ? bucket.hard : [0];

        if (bounceTimelineChart) {
            bounceTimelineChart.destroy();
            bounceTimelineChart = null;
        }

        bounceTimelineChart = new Chart(el, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Soft Bounces',
                        data: soft,
                        borderColor: '#3B82F6',
                        backgroundColor: '#3B82F61A',
                        borderWidth: 2,
                        pointRadius: 3,
                        fill: true,
                        tension: 0.35,
                    },
                    {
                        label: 'Hard Bounces',
                        data: hard,
                        borderColor: '#EF4444',
                        backgroundColor: '#EF44441A',
                        borderWidth: 2,
                        pointRadius: 3,
                        fill: true,
                        tension: 0.35,
                    },
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: isDark() ? '#1F2937' : '#fff',
                        titleColor: textColor(),
                        bodyColor: textColor(),
                        borderColor: gridColor(),
                        borderWidth: 1,
                    },
                },
                scales: {
                    x: {
                        grid: { color: gridColor() },
                        ticks: { color: textColor(), font: { size: 11 }, maxTicksLimit: 10 },
                    },
                    y: {
                        grid: { color: gridColor() },
                        ticks: { color: textColor(), font: { size: 11 } },
                        beginAtZero: true,
                    },
                },
            },
        });
    }

    window.switchBounceTimelineView = function(view) {
        document.querySelectorAll('.bt-btn').forEach(btn => {
            if (btn.dataset.view === view) {
                btn.classList.add('bg-primary-600', 'text-white');
                btn.classList.remove('text-gray-500', 'dark:text-gray-400', 'hover:bg-gray-100', 'dark:hover:bg-gray-700');
            } else {
                btn.classList.remove('bg-primary-600', 'text-white');
                btn.classList.add('text-gray-500', 'dark:text-gray-400', 'hover:bg-gray-100', 'dark:hover:bg-gray-700');
            }
        });
        buildBounceTimelineChart(view);
    };

    function initBounceReasonsChart() {
        const el = document.getElementById('bounceReasonsChart');
        if (!el) return;

        if (window.__bounceReasonsChart) {
            window.__bounceReasonsChart.destroy();
            window.__bounceReasonsChart = null;
        }

        const soft = Number(deliverabilityInsights.soft_bounces || 0);
        const hard = Number(deliverabilityInsights.hard_bounces || 0);
        const values = (soft + hard) > 0 ? [soft, hard] : [1, 0];

        window.__bounceReasonsChart = new Chart(el, {
            type: 'doughnut',
            data: {
                labels: ['Soft', 'Hard'],
                datasets: [{
                    data: values,
                    backgroundColor: ['#3B82F6', '#EF4444'],
                    borderWidth: 0,
                    hoverOffset: 4,
                }],
            },
            options: {
                responsive: false,
                cutout: '70%',
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: (soft + hard) > 0 },
                },
                animation: { duration: 600 },
            },
        });
    }

    window.exportProviderPerformanceCsv = function() {
        const table = document.getElementById('providerPerformanceTable');
        if (!table) return;

        const rows = Array.from(table.querySelectorAll('tbody tr.provider-row'));
        const lines = [['Provider', 'Sent', 'Delivered', 'Delivery %', 'Bounce Rate %', 'Health Status']];

        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length < 5) return;
            const provider = cells[0].textContent.trim();
            const sent = cells[1].textContent.trim().replace(/,/g, '');
            const deliveredText = cells[2].textContent.trim();
            const deliveredMatch = deliveredText.match(/([\d,]+)\s*\(([^\)]+)\)/);
            const delivered = deliveredMatch ? deliveredMatch[1].replace(/,/g, '') : deliveredText;
            const deliveryPct = deliveredMatch ? deliveredMatch[2].replace('%', '') : '';
            const bounceRate = cells[3].querySelector('div.text-xs')?.textContent?.trim().replace('%', '') || '';
            const health = cells[4].textContent.trim();
            lines.push([provider, sent, delivered, deliveryPct, bounceRate, health]);
        });

        const csv = lines.map(cols => cols.map(v => `"${String(v).replace(/"/g, '""')}"`).join(',')).join('\n');
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'provider-performance.csv';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };

    // A/B testing tab interactions
    function initAbTestingUi() {
        const form = document.getElementById('abTestForm');
        if (!form) return;

        const variantsContainer = document.getElementById('abVariantsContainer');
        const variantTemplate = document.getElementById('abVariantTemplate');
        const addVariantBtn = document.getElementById('abAddVariant');
        const splitTotalEl = document.getElementById('abSplitTotal');
        const typeInput = document.getElementById('abTestTypeInput');
        const slider = document.getElementById('abAudienceSplitSlider');
        const splitHeadline = document.getElementById('abSplitHeadline');
        const testGroupText = document.getElementById('abTestGroupText');
        const winnerGroupText = document.getElementById('abWinnerGroupText');
        const winningMetricSelect = form.querySelector('select[name="winning_metric"]');

        const totalRecipients = Number(form.dataset.totalRecipients || 0);
        const maxVariants = 5;
        const minVariants = 2;

        function variantRows() {
            return Array.from(variantsContainer.querySelectorAll('.ab-variant-row'));
        }

        function updateTemplatePreview(row) {
            if (!row) return;
            const select = row.querySelector('.ab-template-select');
            const nameEl = row.querySelector('.ab-template-preview-name');
            const descEl = row.querySelector('.ab-template-preview-desc');
            const thumbEl = row.querySelector('.ab-template-thumb');
            const linkEl = row.querySelector('.ab-template-preview-link');
            if (!select || !nameEl || !descEl || !thumbEl || !linkEl) return;

            const selectedId = Number(select.value || 0);
            const selectedTpl = abTemplateOptionsData.find(t => Number(t.id) === selectedId) || null;

            if (!selectedTpl) {
                nameEl.textContent = 'No template selected';
                descEl.textContent = 'Select a template to preview the builder layout.';
                thumbEl.classList.add('hidden');
                thumbEl.src = '';
                linkEl.classList.add('hidden');
                linkEl.classList.remove('inline-flex');
                linkEl.href = '#';
                return;
            }

            nameEl.textContent = selectedTpl.name || 'Selected template';
            descEl.textContent = selectedTpl.description || 'Template selected for this variant.';

            if (selectedTpl.thumbnail) {
                thumbEl.src = selectedTpl.thumbnail;
                thumbEl.classList.remove('hidden');
            } else {
                thumbEl.classList.add('hidden');
                thumbEl.src = '';
            }

            if (selectedTpl.preview_url) {
                linkEl.href = selectedTpl.preview_url;
                linkEl.classList.remove('hidden');
                linkEl.classList.add('inline-flex');
            } else {
                linkEl.classList.add('hidden');
                linkEl.classList.remove('inline-flex');
                linkEl.href = '#';
            }
        }

        function updateSenderPreview(row) {
            if (!row) return;
            const serverSelect = row.querySelector('.ab-delivery-server-select');
            const emailDisplay = row.querySelector('.ab-from-email-display');
            const emailInput = row.querySelector('.ab-from-email-input');
            if (!serverSelect || !emailDisplay || !emailInput) return;

            const selectedId = Number(serverSelect.value || 0);
            const selectedServer = abDeliveryServerOptionsData.find(s => Number(s.id) === selectedId) || null;
            const serverEmail = selectedServer?.from_email || '';

            emailDisplay.value = serverEmail;
            emailInput.value = serverEmail;
        }

        function applyTestTypeState(selectedType) {
            const type = selectedType || (typeInput?.value || 'subject');
            const showContent = type === 'content';
            const showSender = type === 'sender';
            const showSubject = type === 'subject';

            variantRows().forEach((row) => {
                const subjectField = row.querySelector('.ab-subject-field');
                const contentFields = row.querySelector('.ab-content-fields');
                const senderFields = row.querySelector('.ab-sender-fields');
                if (subjectField) subjectField.classList.toggle('hidden', !showSubject);
                if (contentFields) contentFields.classList.toggle('hidden', !showContent);
                if (senderFields) senderFields.classList.toggle('hidden', !showSender);
            });

            if (winningMetricSelect && type === 'content') {
                winningMetricSelect.value = 'click_rate';
            }
        }

        function updateSliderSummary() {
            if (!slider) return;
            const testPct = Number(slider.value || abTestConfigData.test_group_percent || 20);
            const winnerPct = 100 - testPct;
            const testRecipients = Math.round(totalRecipients * (testPct / 100));
            const winnerRecipients = Math.max(0, totalRecipients - testRecipients);

            if (splitHeadline) splitHeadline.textContent = `${testPct}% Test / ${winnerPct}% Winner`;
            if (testGroupText) testGroupText.textContent = `Test Group: ${new Intl.NumberFormat().format(testRecipients)}`;
            if (winnerGroupText) winnerGroupText.textContent = `Winner Group: ${new Intl.NumberFormat().format(winnerRecipients)}`;

            return testRecipients;
        }

        function refreshVariantUI() {
            const rows = variantRows();
            const testRecipients = updateSliderSummary() || 0;
            let splitTotal = 0;

            rows.forEach((row, idx) => {
                const letter = String.fromCharCode(65 + idx);

                const badge = row.querySelector('.ab-variant-badge');
                if (badge) badge.textContent = letter;

                const nameInput = row.querySelector('.ab-variant-name-input');
                if (nameInput) {
                    nameInput.name = `variants[${idx}][name]`;
                    if (!nameInput.value.trim() || /^Variant\s+[A-Z]$/i.test(nameInput.value.trim())) {
                        nameInput.value = `Variant ${letter}`;
                    }
                }

                const subjectInput = row.querySelector('.ab-subject-input');
                if (subjectInput) subjectInput.name = `variants[${idx}][subject]`;

                const templateSelect = row.querySelector('.ab-template-select');
                if (templateSelect) {
                    templateSelect.name = `variants[${idx}][template_id]`;
                    templateSelect.onchange = () => updateTemplatePreview(row);
                }

                const fromNameInput = row.querySelector('.ab-from-name-input');
                if (fromNameInput) fromNameInput.name = `variants[${idx}][from_name]`;

                const serverSelect = row.querySelector('.ab-delivery-server-select');
                if (serverSelect) {
                    serverSelect.name = `variants[${idx}][delivery_server_id]`;
                    serverSelect.onchange = () => updateSenderPreview(row);
                }

                const fromEmailInput = row.querySelector('.ab-from-email-input');
                if (fromEmailInput) fromEmailInput.name = `variants[${idx}][from_email]`;

                const splitInput = row.querySelector('.ab-split-input');
                if (splitInput) {
                    splitInput.name = `variants[${idx}][split_percentage]`;
                    splitTotal += Number(splitInput.value || 0);
                }

                const shareText = row.querySelector('.ab-share');
                if (shareText && splitInput) {
                    const pct = Number(splitInput.value || 0);
                    const recipients = Math.round(testRecipients * (pct / 100));
                    shareText.textContent = `${pct}% of test group (${new Intl.NumberFormat().format(recipients)})`;
                }

                const removeBtn = row.querySelector('.ab-remove-variant');
                if (removeBtn) {
                    removeBtn.classList.toggle('hidden', rows.length <= minVariants);
                    removeBtn.onclick = () => {
                        if (variantRows().length <= minVariants) return;
                        row.remove();
                        distributeSplitEvenly();
                        refreshVariantUI();
                    };
                }

                updateTemplatePreview(row);
                updateSenderPreview(row);
            });

            if (splitTotalEl) {
                splitTotalEl.textContent = `Total split: ${splitTotal}%`;
                splitTotalEl.classList.toggle('text-red-500', splitTotal !== 100);
                splitTotalEl.classList.toggle('dark:text-red-400', splitTotal !== 100);
                splitTotalEl.classList.toggle('text-gray-500', splitTotal === 100);
                splitTotalEl.classList.toggle('dark:text-gray-400', splitTotal === 100);
            }

            if (addVariantBtn) addVariantBtn.disabled = rows.length >= maxVariants;
        }

        function distributeSplitEvenly() {
            const rows = variantRows();
            if (!rows.length) return;

            const base = Math.floor(100 / rows.length);
            let rem = 100 - (base * rows.length);
            rows.forEach(row => {
                const input = row.querySelector('.ab-split-input');
                if (!input) return;
                input.value = base + (rem > 0 ? 1 : 0);
                if (rem > 0) rem--;
            });
        }

        addVariantBtn?.addEventListener('click', () => {
            const rows = variantRows();
            if (rows.length >= maxVariants || !variantTemplate) return;

            const nextIndex = rows.length;
            const html = variantTemplate.innerHTML.replaceAll('__INDEX__', String(nextIndex));
            variantsContainer.insertAdjacentHTML('beforeend', html);
            distributeSplitEvenly();
            applyTestTypeState(typeInput?.value || 'subject');
            refreshVariantUI();
        });

        slider?.addEventListener('input', refreshVariantUI);

        document.querySelectorAll('.ab-type-card').forEach(card => {
            card.addEventListener('click', () => {
                const selectedType = card.dataset.type || 'subject';
                if (typeInput) typeInput.value = selectedType;

                document.querySelectorAll('.ab-type-card').forEach(other => {
                    other.classList.remove('border-primary-500', 'bg-primary-50/70', 'dark:bg-primary-900/20', 'ring-1', 'ring-primary-500/40');
                    other.classList.add('border-gray-200', 'dark:border-gray-700');
                    const title = other.querySelector('div');
                    if (title) {
                        title.classList.remove('text-primary-600', 'dark:text-primary-400');
                        title.classList.add('text-gray-900', 'dark:text-gray-100');
                    }
                });

                card.classList.add('border-primary-500', 'bg-primary-50/70', 'dark:bg-primary-900/20', 'ring-1', 'ring-primary-500/40');
                card.classList.remove('border-gray-200', 'dark:border-gray-700');
                const title = card.querySelector('div');
                if (title) {
                    title.classList.add('text-primary-600', 'dark:text-primary-400');
                    title.classList.remove('text-gray-900', 'dark:text-gray-100');
                }

                applyTestTypeState(selectedType);
            });
        });

        form.addEventListener('submit', (e) => {
            const rows = variantRows();
            if (rows.length < minVariants) {
                e.preventDefault();
                alert('Please add at least two variants.');
                return;
            }

            let totalSplit = 0;
            rows.forEach(row => {
                totalSplit += Number(row.querySelector('.ab-split-input')?.value || 0);
            });

            if (totalSplit !== 100) {
                e.preventDefault();
                alert(`Variant split must add up to 100%. Current: ${totalSplit}%`);
            }
        });

        if (!variantRows().some(row => Number(row.querySelector('.ab-split-input')?.value || 0) > 0)) {
            distributeSplitEvenly();
        }
        applyTestTypeState(typeInput?.value || 'subject');
        refreshVariantUI();
    }

    // ── OPENS & CLICKS TAB ──
    const heatmapData = @json($heatmapData ?? ['opened'=>[],'clicked'=>[]]);

    // Heatmap cell data: [dow][hr] for opens and clicks
    // dow: 0=Sun,1=Mon...6=Sat (reordered to Mon-Sun display: 1..6,0)
    const heatDayOrder = [1,2,3,4,5,6,0];
    const heatHourMap  = [0,4,8,12,16,20];

    function getHeatMax(event) {
        let max = 1;
        const data = heatmapData[event] || {};
        Object.values(data).forEach(dayRow => {
            Object.values(dayRow).forEach(cnt => { if (cnt > max) max = cnt; });
        });
        return max;
    }

    function heatColor(intensity) {
        if (intensity <= 0)    return ['bg-blue-50', 'dark:bg-gray-700/60'];
        if (intensity <= 0.25) return ['bg-blue-100', 'dark:bg-blue-900/30'];
        if (intensity <= 0.5)  return ['bg-blue-200', 'dark:bg-blue-800/50'];
        if (intensity <= 0.75) return ['bg-blue-400', 'dark:bg-blue-700'];
        return ['bg-blue-600', 'dark:bg-blue-500'];
    }

    window.switchHeatmap = function(view) {
        document.querySelectorAll('.hm-btn').forEach(btn => {
            if (btn.dataset.view === view) {
                btn.classList.add('bg-primary-600', 'text-white');
                btn.classList.remove('text-gray-500', 'dark:text-gray-400', 'hover:bg-gray-100', 'dark:hover:bg-gray-700');
            } else {
                btn.classList.remove('bg-primary-600', 'text-white');
                btn.classList.add('text-gray-500', 'dark:text-gray-400', 'hover:bg-gray-100', 'dark:hover:bg-gray-700');
            }
        });
        const eventKey = view === 'opens' ? 'opened' : 'clicked';
        const max = getHeatMax(eventKey);
        const data = heatmapData[eventKey] || {};
        const cells = document.querySelectorAll('.hm-cell');
        cells.forEach(cell => {
            const dow = parseInt(cell.dataset.dow);
            const hr  = parseInt(cell.dataset.hr);
            const cnt = (data[dow] && data[dow][hr]) ? data[dow][hr] : 0;
            const intensity = max > 0 ? cnt / max : 0;
            const [lightClass, darkClass] = heatColor(intensity);
            cell.className = cell.className
                .replace(/bg-blue-\d+|dark:bg-gray-\d+\/\d+|dark:bg-blue-\d+(?:\/\d+)?/g, '').trim();
            cell.classList.add(lightClass, darkClass);
            const label = cell.dataset.label || '';
            cell.title = `${label}: ${cnt} ${view}`;
        });
    };

    // OC Performance chart (daily/hourly)
    let ocChart = null;
    const ocDailyData = {
        labels: engagementData.labels && engagementData.labels.length ? engagementData.labels : ['No data'],
        opens:  engagementData.opens  && engagementData.opens.length  ? engagementData.opens  : [0],
        clicks: engagementData.clicks && engagementData.clicks.length ? engagementData.clicks : [0],
    };

    // Build hourly data from heatmap (sum all days per hour 0-23)
    function buildHourlyData(eventKey) {
        const data = heatmapData[eventKey] || {};
        const hourTotals = {};
        for (let h = 0; h < 24; h++) hourTotals[h] = 0;
        Object.values(data).forEach(dayRow => {
            Object.entries(dayRow).forEach(([hr, cnt]) => {
                hourTotals[parseInt(hr)] = (hourTotals[parseInt(hr)] || 0) + cnt;
            });
        });
        return { labels: Object.keys(hourTotals).map(h => h + ':00'), values: Object.values(hourTotals) };
    }

    function buildOcChart(labels, opens, clicks) {
        const el = document.getElementById('ocPerformanceChart');
        if (!el) return;
        if (ocChart) { ocChart.destroy(); ocChart = null; }
        ocChart = new Chart(el, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Opens',
                        data: opens,
                        borderColor: '#3B82F6',
                        backgroundColor: '#3B82F614',
                        borderWidth: 2,
                        pointRadius: 4,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#3B82F6',
                        pointBorderWidth: 2,
                        fill: true,
                        tension: 0.35,
                    },
                    {
                        label: 'Clicks',
                        data: clicks,
                        borderColor: '#8B5CF6',
                        backgroundColor: '#8B5CF614',
                        borderWidth: 2,
                        pointRadius: 4,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#8B5CF6',
                        pointBorderWidth: 2,
                        fill: true,
                        tension: 0.35,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: isDark() ? '#1F2937' : '#fff',
                        titleColor: textColor(),
                        bodyColor: textColor(),
                        borderColor: gridColor(),
                        borderWidth: 1,
                    }
                },
                scales: {
                    x: {
                        grid: { color: gridColor() },
                        ticks: { color: textColor(), font: { size: 11 }, maxTicksLimit: 10 },
                    },
                    y: {
                        grid: { color: gridColor() },
                        ticks: { color: textColor(), font: { size: 11 } },
                        beginAtZero: true,
                    }
                }
            }
        });
    }

    window.switchOcView = function(view) {
        document.querySelectorAll('.oc-btn').forEach(btn => {
            if (btn.dataset.view === view) {
                btn.classList.add('bg-primary-600', 'text-white');
                btn.classList.remove('text-gray-500', 'dark:text-gray-400', 'hover:bg-gray-100', 'dark:hover:bg-gray-700');
            } else {
                btn.classList.remove('bg-primary-600', 'text-white');
                btn.classList.add('text-gray-500', 'dark:text-gray-400', 'hover:bg-gray-100', 'dark:hover:bg-gray-700');
            }
        });
        if (view === 'hourly') {
            const openH  = buildHourlyData('opened');
            const clickH = buildHourlyData('clicked');
            buildOcChart(openH.labels, openH.values, clickH.values);
        } else {
            buildOcChart(ocDailyData.labels, ocDailyData.opens, ocDailyData.clicks);
        }
    };

    // Link search/filter
    window.filterOcLinks = function(query) {
        const q = query.toLowerCase();
        document.querySelectorAll('.oc-link-row').forEach(row => {
            const url = row.querySelector('a')?.textContent?.toLowerCase() || '';
            row.style.display = url.includes(q) ? '' : 'none';
        });
    };

    // Live stats refresh (running campaigns)
    let refreshInterval;
    function updateCampaignStats() {
        fetch('{{ route('customer.campaigns.stats', $campaign) }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) return;
            const s = data.stats;
            const fmt = v => new Intl.NumberFormat().format(v);
            const el = id => document.getElementById(id);
            if (el('sentCount'))       el('sentCount').textContent      = fmt(s.sent_count);
            if (el('deliveredCount'))  el('deliveredCount').textContent = fmt(s.delivered);
            if (el('openedCount'))     el('openedCount').textContent    = fmt(s.opened_count);
            if (el('clickedCount'))    el('clickedCount').textContent   = fmt(s.clicked_count);
            if (el('bouncedCount'))    el('bouncedCount').textContent   = fmt(s.bounced_count);
            if (el('unsubscribedCount')) el('unsubscribedCount').textContent = fmt(s.unsubscribed_count);
            if (el('complainedCount')) el('complainedCount').textContent = fmt(s.complained_count);
            if (s.total_recipients > 0) {
                const pct = (s.sent_count / s.total_recipients) * 100;
                if (el('progressBar'))        el('progressBar').style.width = pct + '%';
                if (el('progressPercentage')) el('progressPercentage').textContent = pct.toFixed(1) + '%';
                if (el('progressText'))       el('progressText').textContent = fmt(s.sent_count) + ' / ' + fmt(s.total_recipients) + ' sent';
            }
            if (el('sendingSpeed')) el('sendingSpeed').textContent = s.sending_speed.toFixed(2);
            if (s.status === 'completed' || s.status === 'failed' || s.status === 'paused') {
                clearInterval(refreshInterval);
            }
        })
        .catch(() => {});
    }

    document.addEventListener('DOMContentLoaded', initCharts);
    document.addEventListener('turbo:load', initCharts);
    if (document.readyState !== 'loading') initCharts();

    document.addEventListener('DOMContentLoaded', function () {
        if ('{{ $campaign->status }}' === 'running') {
            refreshInterval = setInterval(updateCampaignStats, 3000);
        }
    });
    window.addEventListener('beforeunload', function () {
        if (refreshInterval) clearInterval(refreshInterval);
    });
})();
</script>
@endpush
@endsection
