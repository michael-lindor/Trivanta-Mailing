@php
    $isOverview    = request()->routeIs('customer.lists.show');
    $isSubscribers = request()->routeIs('customer.lists.subscribers.*');
    $isSegments    = request()->routeIs('customer.lists.segments.*');
    $isAnalytics   = request()->routeIs('customer.lists.analytics');
    $isForms       = request()->routeIs('customer.lists.forms.*');
    $isSettings    = request()->routeIs('customer.lists.settings*');

    $subscriberCount = $list->subscribers_count ?? 0;
    $subLabel = $subscriberCount >= 1_000_000
        ? round($subscriberCount / 1_000_000, 1) . 'M'
        : ($subscriberCount >= 1_000 ? round($subscriberCount / 1_000, 1) . 'k' : (string) $subscriberCount);

    $segCount = $segmentsCount ?? $list->segments()->count();

    $tabBase    = 'inline-flex items-center gap-1.5 whitespace-nowrap border-b-2 py-3.5 px-0.5 text-sm font-medium transition-colors';
    $tabActive  = 'border-primary-500 text-primary-600 dark:border-primary-400 dark:text-primary-400';
    $tabInactive = '!border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200';
@endphp

<div class="border-b border-gray-200 dark:border-gray-700">
    <nav class="-mb-px flex flex-wrap gap-x-7" aria-label="List tabs">
        {{-- Overview --}}
        <a href="{{ route('customer.lists.show', $list) }}"
           class="{{ $tabBase }} {{ $isOverview ? $tabActive : $tabInactive }}">
            Overview
        </a>

        {{-- Subscribers --}}
        <a href="{{ route('customer.lists.subscribers.index', $list) }}"
           class="{{ $tabBase }} {{ $isSubscribers ? $tabActive : $tabInactive }}">
            Subscribers
            @if($subscriberCount > 0)
                <span class="rounded-full px-1.5 py-0.5 text-xs font-semibold leading-none
                    {{ $isSubscribers
                        ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300'
                        : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">
                    {{ $subLabel }}
                </span>
            @endif
        </a>

        {{-- Segments --}}
        <a href="{{ route('customer.lists.segments.index', $list) }}"
           class="{{ $tabBase }} {{ $isSegments ? $tabActive : $tabInactive }}">
            Segments
            @if($segCount > 0)
                <span class="rounded-full px-1.5 py-0.5 text-xs font-semibold leading-none
                    {{ $isSegments
                        ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300'
                        : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">
                    {{ $segCount }}
                </span>
            @endif
        </a>

        {{-- Analytics --}}
        <a href="{{ route('customer.lists.analytics', $list) }}"
           class="{{ $tabBase }} {{ $isAnalytics ? $tabActive : $tabInactive }}">
            Analytics
        </a>

        {{-- Forms --}}
        <a href="{{ route('customer.lists.forms.index', $list) }}"
           class="{{ $tabBase }} {{ $isForms ? $tabActive : $tabInactive }}">
            Forms
        </a>

        {{-- Settings (icon only, right-aligned on larger screens) --}}
        @customercan('lists.permissions.can_edit_lists')
        <a href="{{ route('customer.lists.settings', $list) }}"
           class="{{ $tabBase }} {{ $isSettings ? $tabActive : $tabInactive }} ml-auto">
            Settings
        </a>
        @endcustomercan
    </nav>
</div>
