@php
    $_listName   = $list->display_name ?? $list->name;
    $_confirmed  = (int) ($list->confirmed_subscribers_count ?? 0);
    $_lastActive = $list->last_subscriber_at ?? $list->updated_at ?? null;

    $_tab = match(true) {
        request()->routeIs('customer.lists.subscribers.*') => 'Subscribers',
        request()->routeIs('customer.lists.segments.*')    => 'Segments',
        request()->routeIs('customer.lists.analytics')     => 'Analytics',
        request()->routeIs('customer.lists.forms.*')       => 'Forms',
        request()->routeIs('customer.lists.settings*')     => 'Settings',
        default                                            => null,
    };

    $_primaryUrl   = $primaryActionUrl   ?? route('customer.segments.create', ['list_id' => $list->id]);
    $_primaryLabel = $primaryActionLabel ?? 'Create Segment';
    $_showImport   = $showImportAction ?? true;
@endphp

@section('page-title', $_listName)

@section('breadcrumbs')
    <nav aria-label="Breadcrumb" class="mb-0">
        <ol class="flex flex-wrap items-center gap-1.5 text-[12px] text-gray-500 dark:text-gray-400">
            <li>
                <a href="{{ route('customer.lists.index') }}"
                   class="font-medium text-gray-600 transition hover:text-primary-600 dark:text-gray-300 dark:hover:text-primary-400">
                    Audience
                </a>
            </li>
            <li aria-hidden="true" class="text-gray-300 dark:text-gray-600">/</li>
            @if($_tab)
                <li>
                    <a href="{{ route('customer.lists.show', $list) }}"
                       class="font-medium text-gray-600 transition hover:text-primary-600 dark:text-gray-300 dark:hover:text-primary-400">
                        {{ $_listName }}
                    </a>
                </li>
                <li aria-hidden="true" class="text-gray-300 dark:text-gray-600">/</li>
                <li class="font-medium text-gray-900 dark:text-gray-100">{{ $_tab }}</li>
            @else
                <li class="font-medium text-gray-900 dark:text-gray-100">{{ $_listName }}</li>
            @endif
        </ol>
    </nav>
@endsection

@section('page-title-meta')
    <span class="inline-flex items-center rounded-full bg-primary-100 px-3 py-1 text-sm font-semibold text-primary-700 dark:bg-primary-900/40 dark:text-primary-300">
        {{ number_format($_confirmed) }}
    </span>
@endsection

@section('page-title-details')
    <div class="flex flex-wrap items-center gap-3">
        <span class="inline-flex items-center rounded-full bg-primary-50 px-3 py-1 text-xs font-semibold text-primary-700 dark:bg-primary-900/30 dark:text-primary-300">
            Active
        </span>
        @if($_lastActive)
            <span class="text-xs text-gray-400 dark:text-gray-500">Updated {{ $_lastActive->diffForHumans() }}</span>
        @endif
    </div>
@endsection

@section('page-actions')
    <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:flex-wrap sm:items-center sm:justify-end">
        @if($_showImport)
            <a href="{{ route('customer.lists.subscribers.import', $list) }}"
               class="inline-flex w-full sm:w-auto items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V4.5"/></svg>
                Import
            </a>
        @endif
        <a href="{{ route('customer.lists.subscribers.export', $list) }}"
           class="inline-flex w-full sm:w-auto items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 10.5 12 6m0 0 4.5 4.5M12 6v12"/></svg>
            Export
        </a>
        <a href="{{ $_primaryUrl }}"
           class="inline-flex w-full sm:w-auto items-center justify-center gap-1.5 rounded-lg bg-primary-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-700">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            {{ $_primaryLabel }}
        </a>
    </div>
@endsection
