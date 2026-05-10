@extends('layouts.customer')

@section('title', 'Create Campaign')
@section('page-title', 'Create Campaign')

@section('force-sidebar-collapsed')

@section('page-header')
<div class="lg:pl-14 -ml-6 sm:px-6 py-2 bg-white dark:bg-admin-sidebar border-b border-gray-100 dark:border-admin-border fixed w-full left-[40px] top-[0px] z-20">
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-2 min-w-0 text-sm">
            <a href="{{ route('customer.campaigns.index') }}" class="inline-flex items-center gap-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Campaigns
            </a>
            <span class="text-gray-300 dark:text-gray-600">/</span>
            <span id="campaign-header-title" class="font-semibold text-gray-900 dark:text-white truncate">New Campaign</span>
            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-300">Draft</span>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
            <a href="{{ route('customer.campaigns.index') }}" class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Discard</a>
            @customercan('campaigns.permissions.can_create_campaigns')
            <button type="button" id="btn-save-draft" class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Save as Draft</button>
            @endcustomercan
            @customercan('campaigns.permissions.can_create_campaigns')
            <button type="button" class="rounded-lg bg-indigo-600 px-4 py-1.5 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors" onclick="document.getElementById('btn-save')?.click()">Review &amp; Send</button>
            @endcustomercan
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    #editor-container {
        height: calc(100vh - 310px);
        min-height: 700px;
    }

    /* ── Accordion Steps ── */
    .accordion-step {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #fff;
        overflow: hidden;
        transition: border-color 160ms ease, box-shadow 160ms ease;
    }
    .dark .accordion-step { background: #1f2937; border-color: #374151; }

    .accordion-step.is-active {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.10);
    }
    .accordion-step.is-complete {
        border-color: #bbf7d0;
    }
    .dark .accordion-step.is-complete { border-color: rgba(16,185,129,0.30); }
    .accordion-step.is-pending { opacity: 0.58; }

    /* Header */
    .accordion-header {
        display: flex; align-items: center; justify-content: space-between;
        gap: 0.75rem; width: 100%; padding: 16px 18px;
        text-align: left; background: transparent; border: none; cursor: pointer;
    }
    .accordion-header:focus { outline: none; }
    .accordion-step.is-pending .accordion-header { cursor: default; }

    /* Badge */
    .accordion-badge {
        width: 30px; height: 30px; border-radius: 9999px;
        display: inline-flex; align-items: center; justify-content: center;
        flex-shrink: 0; border: 2px solid #d1d5db; background: #fff;
    }
    .accordion-badge .badge-num { font-size: 12px; font-weight: 700; color: #6b7280; line-height: 1; }
    .accordion-badge .badge-check { display: none; }

    .accordion-step.is-active .accordion-badge { border-color: #6366f1; background: #6366f1; }
    .accordion-step.is-active .accordion-badge .badge-num { color: #fff; }

    .accordion-step.is-complete .accordion-badge { border-color: #10b981; background: #10b981; }
    .accordion-step.is-complete .accordion-badge .badge-num { display: none; }
    .accordion-step.is-complete .accordion-badge .badge-check { display: flex; color: #fff; }

    /* Body */
    .accordion-body { display: none; padding: 0 18px 18px; }
    .accordion-step.is-active .accordion-body { display: block; }

    /* Footer */
    .accordion-footer {
        display: flex; justify-content: flex-end; gap: 0.75rem;
        padding-top: 1rem; margin-top: 1rem; border-top: 1px solid #e5e7eb;
    }
    .dark .accordion-footer { border-color: #374151; }

    /* Summary */
    .accordion-summary { color: #6b7280; font-size: 12px; }
    .accordion-step.is-complete .accordion-summary { color: #059669; }

    /* Edit button */
    .accordion-edit-btn {
        font-size: 12px; font-weight: 600; padding: 4px 12px;
        border-radius: 8px; border: 1px solid #e5e7eb;
        color: #64748b; background: #fff; cursor: pointer; transition: all 100ms;
    }
    .dark .accordion-edit-btn { background: #1f2937; border-color: #374151; color: #9ca3af; }
    .accordion-step.is-pending .accordion-edit-btn { display: none; }
    .accordion-step.is-active .accordion-edit-btn { border-color: #c7d2fe; color: #4f46e5; background: #eef2ff; }
    .accordion-step.is-complete .accordion-edit-btn { border-color: #a7f3d0; color: #059669; }

    /* ── Deliverability panel ── */
    .deliverability-panel {
        background: #fff;
        border: 1px solid #e5e7eb;
        overflow: hidden;
        margin-top: -10px;
    }
    .dark .deliverability-panel { background: #1f2937; border-color: #374151; }

    .wizard-shell {
        width: 500px;
        max-width: 100%;
        margin: 50px auto;
    }

    @media (min-width: 768px) {
        .wizard-shell {
            width: 600px;
        }
    }

    .wizard-left-column {
        width: 100%;
    }

    .wizard-field-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #0f172a;
        margin-bottom: 0.5rem;
    }

    .wizard-field-help {
        margin-top: 0.35rem;
        font-size: 12px;
        color: #64748b;
    }

    .tag-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        background: #f8fafc;
        color: #475569;
        font-size: 12px;
        line-height: 1;
        padding: 0.45rem 0.625rem;
    }

    .tag-pill button {
        border: none;
        background: transparent;
        color: inherit;
        padding: 0;
        line-height: 1;
        cursor: pointer;
    }

    .tag-input-wrap {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
        min-height: 48px;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        padding: 0.5rem 0.75rem;
        background: #fff;
    }

    .tag-input-wrap input {
        border: none;
        outline: none;
        min-width: 120px;
        flex: 1 1 120px;
        font-size: 13px;
        background: transparent;
    }

    .segmented-choice {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.35rem;
        border-radius: 8px;
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
        width: 100%;
        max-width: 360px;
    }

    .segmented-choice input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .segmented-choice label {
        flex: 1 1 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 7px;
        padding: 7px 20px;
        font-size: 14px;
        width: 100%;
        font-weight: 600;
        color: #111827;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .segmented-choice input:checked + label {
        background: #ffffff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.14);
        border: 1px solid #d1d5db;
    }

    .server-mode-panel.hidden {
        display: none;
    }

    /* ── Wizard grid ── */
    @@media (min-width: 1024px) {
        #campaign-wizard-grid { grid-template-columns: minmax(0,660px) 256px; }
    }
</style>
@endpush

@section('content')
<div class="wizard-shell">
<div class="grid gap-8 items-start" style="grid-template-columns: minmax(0,1fr); display: grid;" id="campaign-wizard-grid">
    <div class="min-w-0 wizard-left-column">
    @if(!empty($runPreflightIssues))
        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 text-yellow-900 rounded-lg dark:bg-yellow-900/20 dark:border-yellow-800 dark:text-yellow-100">
            <div class="flex items-start">
                <svg class="w-5 h-5 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold mb-1">Action required before you can run campaigns</h3>
                    <ul class="text-sm list-disc list-inside space-y-1">
                        @foreach($runPreflightIssues as $issue)
                            <li>{{ $issue }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Set up your campaign</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Complete the steps below to prepare your email for sending.</p>
        </div>
        <form id="unlayer-form" method="POST" action="{{ route('customer.campaigns.store') }}" class="space-y-6">
            @csrf

            <input type="hidden" name="wizard_step" id="wizard_step" value="{{ old('wizard_step', request('step', 1)) }}">
            <input type="hidden" name="status" id="campaign_status" value="{{ old('status', 'draft') }}">
            <input type="hidden" name="type" id="type" value="{{ old('type', 'regular') }}">

            <div class="space-y-2" data-accordion-wizard>

                {{-- Step 1: Campaign Details --}}
                <div class="accordion-step" data-accordion-step="1">
                    <button type="button" class="accordion-header" data-accordion-trigger="1">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="accordion-badge">
                                <span class="badge-num">1</span>
                                <svg class="badge-check w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">Campaign details and audience</p>
                                <p class="text-xs accordion-summary" data-step-summary="1">Campaign name, list, and segments</p>
                            </div>
                        </div>
                        <span class="accordion-edit-btn">Edit</span>
                    </button>
                    <div class="accordion-body">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div data-campaign-step="1" class="contents">
                            <div class="sm:col-span-2">
                                <label for="name" class="wizard-field-label">
                                    Campaign Name <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="name"
                                    id="name"
                                    value="{{ old('name') }}"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                >
                                <p class="wizard-field-help">This is for your internal reference only.</p>
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label for="audience_selection" class="wizard-field-label">Audience List</label>
                                @php
                                    $selectedListId = (string) old('list_id', '');
                                    $selectedSegments = collect(old('segments', []))->map(fn ($value) => (string) $value)->all();
                                    $selectedAudienceValue = $selectedListId !== ''
                                        ? (!empty($selectedSegments)
                                            ? 'segment:' . $selectedListId . ':' . $selectedSegments[0]
                                            : 'list:' . $selectedListId)
                                        : '';
                                @endphp
                                <input type="hidden" name="list_id" id="list_id" value="{{ $selectedListId }}">
                                <div id="segments-hidden-inputs">
                                    @foreach($selectedSegments as $segmentId)
                                        <input type="hidden" name="segments[]" value="{{ $segmentId }}">
                                    @endforeach
                                </div>
                                <select
                                    name="audience_selection"
                                    id="audience_selection"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                >
                                    <option value="">Select a list...</option>
                                    <optgroup label="Lists">
                                        @foreach($emailLists as $list)
                                            <option value="list:{{ $list->id }}" {{ $selectedAudienceValue === 'list:' . $list->id ? 'selected' : '' }}>
                                                {{ $list->name }} ({{ number_format($list->subscribers_count ?? 0) }} recipients)
                                            </option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Segments">
                                        @foreach($emailLists as $list)
                                            @foreach(($list->segments ?? collect()) as $segment)
                                                <option value="segment:{{ $list->id }}:{{ $segment->id }}" {{ $selectedAudienceValue === 'segment:' . $list->id . ':' . $segment->id ? 'selected' : '' }}>
                                                    {{ $list->name }} › {{ $segment->name }} ({{ number_format($segment->subscribers_count ?? 0) }} recipients)
                                                </option>
                                            @endforeach
                                        @endforeach
                                    </optgroup>
                                </select>
                                <p class="wizard-field-help">Choose the audience that will receive this campaign.</p>
                                @error('list_id')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                                @error('segments')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            </div>
                        </div>
                        <div class="accordion-footer">
                            <button type="button" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700" data-step-save>Save & Continue</button>
                        </div>
                    </div>
                </div>

                {{-- Step 2: Sender & Server Details --}}
                <div class="accordion-step" data-accordion-step="2">
                    <button type="button" class="accordion-header" data-accordion-trigger="2">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="accordion-badge">
                                <span class="badge-num">2</span>
                                <svg class="badge-check w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">Sender &amp; Server Details</p>
                                <p class="text-xs accordion-summary" data-step-summary="2">Choose sender identity and infrastructure</p>
                            </div>
                        </div>
                        <span class="accordion-edit-btn">Edit</span>
                    </button>
                    <div class="accordion-body">
                        <div class="grid grid-cols-1 gap-6">
                            <div data-campaign-step="2" class="contents sm:col-span-2">
                                @php
                                    $serverMode = old('inbox_rotation_enabled') ? 'rotation' : 'single';
                                @endphp
                                <div>
                                    <label class="wizard-field-label">Delivery Server</label>
                                    <div class="segmented-choice" role="radiogroup" aria-label="Delivery server mode">
                                        <div class="w-1/2">
                                            <input type="radio" name="delivery_server_mode" id="delivery_mode_single" value="single" {{ $serverMode === 'single' ? 'checked' : '' }}>
                                            <label for="delivery_mode_single">Single server</label>
                                        </div>
                                        <div class="w-1/2">
                                            <input type="radio" name="delivery_server_mode" id="delivery_mode_rotation" value="rotation" {{ $serverMode === 'rotation' ? 'checked' : '' }}>
                                            <label for="delivery_mode_rotation">Rotational server</label>
                                        </div>
                                    </div>
                                </div>

                                <div id="single-server-panel" class="server-mode-panel {{ $serverMode === 'single' ? '' : 'hidden' }}">
                                    <div class="flex items-center gap-2 mt-1 mb-1">
                                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Server</span>
                                        <span id="delivery-ping-badge" class="hidden text-xs font-medium px-2 py-0.5 rounded-full transition-all"></span>
                                    </div>
                                    <select
                                        name="delivery_server_id"
                                        id="delivery_server_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                    >
                                        <option value="">Use Default (Auto-select)</option>
                                        @foreach($deliveryServers as $server)
                                            <option value="{{ $server->id }}" data-type="{{ $server->type }}" data-from-email="{{ $server->from_email }}" {{ old('delivery_server_id') == $server->id ? 'selected' : '' }}>
                                                {{ $server->name }}{{ $server->customer_id ? '' : ' (System)' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Select one delivery server for this campaign. If not selected, the system will auto-select an active server.
                                    </p>
                                    @error('delivery_server_id')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div id="rotation-server-panel" class="server-mode-panel {{ $serverMode === 'rotation' ? '' : 'hidden' }}">
                                    <input type="hidden" name="inbox_rotation_enabled" id="inbox_rotation_enabled" value="{{ $serverMode === 'rotation' ? '1' : '0' }}">
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        Rotate sends across multiple delivery servers to distribute quotas and sending load.
                                    </p>

                                    <label class="mt-3 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Rotation Delivery Servers
                                    </label>
                            @php
                                $rotationDeliveryServerOptions = collect($deliveryServers ?? [])->map(function ($server) {
                                    $ownerLabel = $server->customer_id ? '' : ' (System)';

                                    return [
                                        'id' => (string) $server->id,
                                        'label' => $server->name . $ownerLabel,
                                    ];
                                })->values()->all();
                            @endphp
                            <x-tag-multiselect
                                name="inbox_rotation_server_ids[]"
                                :options="$rotationDeliveryServerOptions"
                                :selected="old('inbox_rotation_server_ids', [])"
                                placeholder="Select rotation delivery servers"
                                search-placeholder="Search servers..."
                            />
                                    @error('inbox_rotation_server_ids')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        <div>
                            <label for="reply_server_id" class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Reply Server
                                <span id="reply-ping-badge" class="hidden text-xs font-medium px-2 py-0.5 rounded-full transition-all"></span>
                            </label>
                            <select
                                name="reply_server_id"
                                id="reply_server_id"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                                <option value="">Use Default (Env / Disabled)</option>
                                @foreach($replyServers as $server)
                                    <option value="{{ $server->id }}" {{ old('reply_server_id') == $server->id ? 'selected' : '' }}>
                                        {{ $server->name }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Select a reply tracking server for this campaign. If not selected, reply tracking uses the global configuration.
                            </p>
                            @error('reply_server_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="sending_domain_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Sending Domain
                            </label>
                            <select
                                name="sending_domain_id"
                                id="sending_domain_id"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                                <option value="">Use Email List's Sending Domain (or Default)</option>
                                @foreach($sendingDomains as $domain)
                                    <option value="{{ $domain->id }}" {{ old('sending_domain_id') == $domain->id ? 'selected' : '' }}>
                                        {{ $domain->domain }} {{ $domain->status === 'verified' ? '✓' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Select a verified sending domain for this campaign. If not selected, the email list's sending domain will be used.
                            </p>
                            @error('sending_domain_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="tracking_domain_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Tracking Domain
                            </label>
                            <select
                                name="tracking_domain_id"
                                id="tracking_domain_id"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                                <option value="">Use Default (App Domain)</option>
                                @foreach($trackingDomains as $domain)
                                    <option value="{{ $domain->id }}" {{ old('tracking_domain_id') == $domain->id ? 'selected' : '' }}>
                                        {{ $domain->domain }} {{ $domain->status === 'verified' ? '✓' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Select a verified tracking domain for opens/clicks tracking.
                            </p>
                            @error('tracking_domain_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="bounce_server_id" class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Bounce Server
                                <span id="bounce-ping-badge" class="hidden text-xs font-medium px-2 py-0.5 rounded-full transition-all"></span>
                            </label>
                            <select
                                name="bounce_server_id"
                                id="bounce_server_id"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                                <option value="">Use Default (None)</option>
                                @foreach($bounceServers as $server)
                                    <option value="{{ $server->id }}" {{ old('bounce_server_id') == $server->id ? 'selected' : '' }}>
                                        {{ $server->name }}{{ $server->customer_id ? '' : ' (System)' }} ({{ $server->hostname }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Select a bounce server to track bounces for this campaign.
                            </p>
                            @error('bounce_server_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="from_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                From Name
                            </label>
                            <input
                                type="text"
                                name="from_name"
                                id="from_name"
                                value="{{ old('from_name') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                            @error('from_name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="from_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                From Email
                            </label>
                            <input
                                type="email"
                                name="from_email"
                                id="from_email"
                                value="{{ old('from_email') }}"
                                placeholder="{{ auth('customer')->user()->email }}"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                            <div id="ses-from-email-warning" class="mt-2 hidden">
                                <div class="p-3 bg-yellow-50 border border-yellow-200 text-yellow-900 rounded-lg dark:bg-yellow-900/20 dark:border-yellow-800 dark:text-yellow-100 text-sm">
                                    It may fail if From email is not verified, Use Amazon SES verified email here.
                                </div>
                            </div>
                            @error('from_email')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="accordion-footer">
                            <button type="button" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700" data-step-cancel>Back</button>
                            <button type="button" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700" data-step-save>Save & Continue</button>
                        </div>
                    </div>
                    </div>
                </div>
                
                {{-- Step 3: Subject Line --}}
                <div class="accordion-step" data-accordion-step="3">
                    <button type="button" class="accordion-header" data-accordion-trigger="3">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="accordion-badge">
                                <span class="badge-num">3</span>
                                <svg class="badge-check w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">Subject Line</p>
                                <p class="text-xs accordion-summary" data-step-summary="3">Write a catchy subject line</p>
                            </div>
                        </div>
                        <span class="accordion-edit-btn">Edit</span>
                    </button>
                    <div class="accordion-body" data-campaign-step="3">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label for="subject" class="wizard-field-label">
                                    Subject <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="subject"
                                    id="subject"
                                    value="{{ old('subject') }}"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                >
                                <p class="wizard-field-help">Keep it concise and clear for better open rates.</p>
                                @error('subject')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="accordion-footer">
                            <button type="button" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700" data-step-cancel>Back</button>
                            <button type="button" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700" data-step-save>Save & Continue</button>
                        </div>
                    </div>
                </div>

                {{-- Step 4: Email Content --}}
                <div class="accordion-step" data-accordion-step="4">
                    <button type="button" class="accordion-header" data-accordion-trigger="4">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="accordion-badge">
                                <span class="badge-num">4</span>
                                <svg class="badge-check w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">Email Content</p>
                                <p class="text-xs accordion-summary" data-step-summary="4">Design the layout and write the copy for your email.</p>
                            </div>
                        </div>
                        <span class="accordion-edit-btn">Edit</span>
                    </button>
                    <div class="accordion-body" data-campaign-step="4">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label for="template_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Email Template
                                </label>
                                <select
                                    name="template_id"
                                    id="template_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                >
                                    <option value="">Select a template...</option>
                                    @foreach($templates as $template)
                                        <option value="{{ $template->id }}" {{ old('template_id') == $template->id ? 'selected' : '' }}>
                                            {{ $template->name }} ({{ ucfirst($template->type) }})
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Select a template to use for this campaign.
                                    @customercan('templates.permissions.can_create_templates')
                                        You can also <a href="{{ route('customer.templates.unlayer.create') }}" target="_blank" class="text-primary-600 hover:text-primary-700">create a new template</a>.
                                    @endcustomercan
                                </p>
                                @error('template_id')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="signature_template_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Signature
                                </label>
                                <select
                                    name="signature_template_id"
                                    id="signature_template_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                >
                                    <option value="">None</option>
                                    @foreach($signatureTemplates as $template)
                                        <option value="{{ $template->id }}" {{ old('signature_template_id') == $template->id ? 'selected' : '' }}>
                                            {{ $template->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('signature_template_id')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="footer_template_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Footer Template
                                </label>
                                <select
                                    name="footer_template_id"
                                    id="footer_template_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                >
                                    <option value="">None</option>
                                    @foreach($footerTemplates as $template)
                                        <option value="{{ $template->id }}" {{ old('footer_template_id') == $template->id ? 'selected' : '' }}>
                                            {{ $template->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('footer_template_id')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <input type="hidden" name="html_content" id="html_content" value="{{ old('html_content') }}">
                            <input type="hidden" name="plain_text_content" id="plain_text_content" value="{{ old('plain_text_content') }}">
                            @php
                                $templateDataValue = old('template_data');
                                if ($templateDataValue === null) {
                                    $templateDataValue = '';
                                }
                                if (is_array($templateDataValue) || is_object($templateDataValue)) {
                                    $templateDataValue = json_encode($templateDataValue) ?: '';
                                }
                            @endphp
                            <input type="hidden" name="template_data" id="grapesjs_data" value="{{ $templateDataValue }}">

                            <div class="sm:col-span-2">
                                <label class="flex items-center">
                                    <input
                                        type="checkbox"
                                        name="enable_spintax"
                                        value="1"
                                        {{ old('enable_spintax', false) ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700"
                                    >
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Enable Spintax</span>
                                </label>

                                <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-md dark:bg-blue-900/20 dark:border-blue-800 hidden" id="spintax-help">
                                    <p class="text-sm text-blue-800 dark:text-blue-200">
                                        <strong>Spintax Format:</strong> Use {option1|option2|option3} to randomly select different text for each email.<br>
                                        Example: {Hello|Hi|Hey} {there|world}, {check out|see|discover} our {amazing|great|fantastic} {offer|deal|promotion}!
                                    </p>
                                </div>
                            </div>

                            <div class="sm:col-span-2">
                                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Personalization Tags</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Click a tag to copy</div>
                                    </div>
                                    <div class="mt-3" data-campaign-tags></div>
                                </div>
                            </div>

                            <div class="sm:col-span-2">
                                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                                    @php
                                        $campaignUnlayerDesign = null;
                                        $rawCampaignUnlayer = old('template_data');
                                        if (is_string($rawCampaignUnlayer) && trim($rawCampaignUnlayer) !== '') {
                                            $decoded = json_decode($rawCampaignUnlayer, true);
                                            $campaignUnlayerDesign = is_array($decoded) ? $decoded : null;
                                        }
                                    @endphp
                                    <script type="application/json" id="unlayer-design-json">@json($campaignUnlayerDesign)</script>
                                    <div
                                        id="editor-container"
                                        data-unlayer-editor
                                        data-unlayer-display-mode="email"
                                        data-unlayer-project-id="{{ $unlayerProjectId }}"
                                        data-unlayer-design-script-id="unlayer-design-json"
                                    ></div>
                                </div>
                            </div>

                            <div>
                                <label for="send_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Send At (Optional)
                                </label>
                                <input
                                    type="datetime-local"
                                    name="send_at"
                                    id="send_at"
                                    value="{{ old('send_at') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                >
                                @error('send_at')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <input type="hidden" name="spam_scoring_enabled" value="1">
                                <div class="flex items-center space-x-6">
                                    <label class="flex items-center">
                                        <input
                                            type="checkbox"
                                            name="track_opens"
                                            value="1"
                                            {{ old('track_opens', true) ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700"
                                        >
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Track Opens</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input
                                            type="checkbox"
                                            name="track_clicks"
                                            value="1"
                                            {{ old('track_clicks', true) ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700"
                                        >
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Track Clicks</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-footer">
                            <button type="button" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700" data-step-cancel>Back</button>
                            @customercan('campaigns.permissions.can_create_campaigns')
                                <button type="button" id="btn-save" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700">Create Campaign</button>
                            @endcustomercan
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="min-w-0 lg:top-[60px] fixed right-[0] w-[400px]">
        <div class="deliverability-panel">
            {{-- Panel header --}}
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-2.5">
                <svg class="w-5 h-5 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Deliverability</h3>
            </div>

            {{-- Idle / Loading state --}}
            <div id="spam-help" class="px-5 py-8 text-center">
                <div class="relative inline-flex items-center justify-center mb-4">
                    <svg width="110" height="110" style="transform:rotate(-90deg)">
                        <circle cx="55" cy="55" r="50" fill="none" stroke="#f3f4f6" stroke-width="9"/>
                        <circle cx="55" cy="55" r="50" fill="none" stroke="#e5e7eb" stroke-width="9" stroke-dasharray="276.5" stroke-dashoffset="0"/>
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-3xl font-bold text-gray-300 dark:text-gray-600">--</span>
                        <span class="text-[10px] uppercase tracking-widest font-semibold text-gray-400 mt-0.5">Score</span>
                    </div>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed max-w-[220px] mx-auto">
                    Fill in your subject and content to get a deliverability score.
                </p>
                <div id="spam-check-loading" class="hidden mt-3 text-xs font-medium text-indigo-600 dark:text-indigo-400 animate-pulse">
                    Analyzing campaign&hellip;
                </div>
                <div id="spam-check-error" class="hidden mt-3 p-2 text-xs rounded-lg border border-red-200 bg-red-50 text-red-700 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-200 text-left"></div>
            </div>

            {{-- Results (shown after check) --}}
            <div id="spam-check-results" class="hidden">
                {{-- Score ring --}}
                <div class="px-5 pt-6 pb-4 text-center border-b border-gray-100 dark:border-gray-700">
                    <div class="relative inline-flex items-center justify-center mb-3">
                        <svg id="score-ring-svg" width="110" height="110" style="transform:rotate(-90deg)">
                            <circle cx="55" cy="55" r="50" fill="none" stroke="#f3f4f6" stroke-width="6"/>
                            <circle id="score-ring-arc" cx="55" cy="55" r="50" fill="none" stroke="#10b981" stroke-width="6" stroke-dasharray="276.5" stroke-dashoffset="276.5" stroke-linecap="round"/>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span id="spam-score-percent" class="text-3xl font-bold text-gray-900 dark:text-white leading-none">0</span>
                            <span id="spam-risk-badge" class="text-[10px] uppercase tracking-widest font-bold text-emerald-600 dark:text-emerald-400 mt-1">GOOD</span>
                        </div>
                    </div>
                    <p id="spam-score-description" class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed max-w-[210px] mx-auto"></p>
                    <p class="hidden"><span id="spam-score-points">0</span></p>
                </div>

                {{-- Analysis checks --}}
                <div class="px-5 py-4 h-[65vh] overflow-y-auto pb-[100px]">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-3">Analysis Checks</p>
                    <div id="spam-check-list" class="space-y-3.5"></div>
                </div>
            </div>

            {{-- Re-run footer --}}
            <div class="px-5 py-3 border-t bg-white dark:bg-black border-gray-100 dark:border-gray-700 fixed bottom-0 w-[400px]">
                <button type="button" id="spam-rerun-btn" class="w-full flex items-center justify-center gap-1.5 text-xs font-medium text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-100 transition-colors py-0.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Re-run Analysis
                </button>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
// Initialize help toggles for spintax and spam scoring
const initFeatureHelp = () => {
    const spintaxCheckbox = document.querySelector('input[name="enable_spintax"]');
    const spintaxHelp = document.getElementById('spintax-help');
    const spamHelp = document.getElementById('spam-help');

    if (spintaxCheckbox && spintaxHelp) {
        const toggleSpintaxHelp = () => {
            spintaxHelp.classList.toggle('hidden', !spintaxCheckbox.checked);
        };
        spintaxCheckbox.addEventListener('change', toggleSpintaxHelp);
        toggleSpintaxHelp(); // Set initial state
    }

    if (spamHelp) {
        spamHelp.classList.remove('hidden');
    }
};

const initSpamScoreChecker = () => {
    const form = document.getElementById('unlayer-form');
    const resultsEl = document.getElementById('spam-check-results');
    const loadingEl = document.getElementById('spam-check-loading');
    const helpEl = document.getElementById('spam-help');
    const errorEl = document.getElementById('spam-check-error');
    const percentEl = document.getElementById('spam-score-percent');
    const pointsEl = document.getElementById('spam-score-points');
    const descEl = document.getElementById('spam-score-description');
    const badgeEl = document.getElementById('spam-risk-badge');
    const checkListEl = document.getElementById('spam-check-list');
    const rerunBtn = document.getElementById('spam-rerun-btn');

    if (!form || !resultsEl || !checkListEl) {
        return;
    }

    if (form.dataset.spamCheckerBound === '1') {
        return;
    }
    form.dataset.spamCheckerBound = '1';

    const routeUrl = '{{ route('customer.campaigns.spam-preview') }}';
    const csrf = (form.querySelector('input[name="_token"]') || {}).value || '';
    let autoCheckTimer = null;
    let activeRequestId = 0;

    const toneToBadgeClasses = {
        positive: 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200',
        warning: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-200',
        danger: 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200',
    };

    const toneToRowClasses = {
        positive: 'border-green-200 bg-green-50 dark:border-green-900/50 dark:bg-green-900/20',
        warning: 'border-yellow-200 bg-yellow-50 dark:border-yellow-900/50 dark:bg-yellow-900/20',
        danger: 'border-red-200 bg-red-50 dark:border-red-900/50 dark:bg-red-900/20',
    };

    const toneToRecommendationClasses = {
        positive: 'border-green-200 bg-green-50 text-green-800 dark:border-green-900/50 dark:bg-green-900/20 dark:text-green-200',
        warning: 'border-yellow-200 bg-yellow-50 text-yellow-800 dark:border-yellow-900/50 dark:bg-yellow-900/20 dark:text-yellow-200',
        danger: 'border-red-200 bg-red-50 text-red-800 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-200',
    };

    const escapeHtml = (value) => String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');

    const collectCampaignContent = () => {
        const subject = (document.getElementById('subject') || {}).value || '';
        const htmlInput = document.getElementById('html_content');
        const plainInput = document.getElementById('plain_text_content');
        const deliverySelect = document.getElementById('delivery_server_id');
        const deliveryOption = deliverySelect ? deliverySelect.options[deliverySelect.selectedIndex] : null;
        const replyServerSelect = document.getElementById('reply_server_id');

        const payload = {
            subject,
            html_content: htmlInput ? String(htmlInput.value || '') : '',
            plain_text_content: plainInput ? String(plainInput.value || '') : '',
            from_name: ((document.getElementById('from_name') || {}).value || '').trim(),
            from_email: ((document.getElementById('from_email') || {}).value || '').trim(),
            reply_to: ((document.getElementById('reply_to') || {}).value || '').trim(),
            delivery_server_id: deliverySelect ? String(deliverySelect.value || '').trim() : '',
            delivery_server_type: deliveryOption ? String(deliveryOption.dataset.type || '').trim() : '',
            delivery_server_from_email: deliveryOption ? String(deliveryOption.dataset.fromEmail || '').trim() : '',
            reply_server_id: replyServerSelect ? String(replyServerSelect.value || '').trim() : '',
        };

        if (!window.unlayer || typeof window.unlayer.exportHtml !== 'function') {
            return Promise.resolve(payload);
        }

        return new Promise((resolve) => {
            let settled = false;
            const finish = () => {
                if (settled) {
                    return;
                }
                settled = true;
                resolve(payload);
            };

            const timeoutId = window.setTimeout(() => {
                finish();
            }, 1800);

            try {
                window.unlayer.exportHtml((data) => {
                    payload.html_content = String((data && data.html) ? data.html : payload.html_content);
                    const derivedPlain = payload.html_content
                        .replace(/<style[\s\S]*?<\/style>/gi, ' ')
                        .replace(/<script[\s\S]*?<\/script>/gi, ' ')
                        .replace(/<[^>]+>/g, ' ')
                        .replace(/\s+/g, ' ')
                        .trim();

                    payload.plain_text_content = derivedPlain || payload.plain_text_content;
                    if (htmlInput) htmlInput.value = payload.html_content;
                    if (plainInput) plainInput.value = payload.plain_text_content;

                    if (settled) {
                        return;
                    }
                    window.clearTimeout(timeoutId);
                    finish();
                });
            } catch (e) {
                window.clearTimeout(timeoutId);
                finish();
            }
        });
    };

    const setLoading = (loading) => {
        loadingEl.classList.toggle('hidden', !loading);
    };

    const renderGauge = (deliverabilityScore) => {
        const deliverability = Math.max(0, Math.min(100, Number(deliverabilityScore || 0)));
        const circumference = 276.5;
        const offset = circumference * (1 - deliverability / 100);
        const ringEl = document.getElementById('score-ring-arc');
        if (ringEl) {
            ringEl.setAttribute('stroke-dashoffset', offset.toFixed(1));
            const color = deliverability >= 90 ? '#10b981' : deliverability >= 50 ? '#f59e0b' : '#ef4444';
            ringEl.setAttribute('stroke', color);
        }
        if (percentEl) percentEl.textContent = String(deliverability);
        if (pointsEl) pointsEl.textContent = String(deliverability);
    };

    const showError = (message) => {
        errorEl.textContent = message;
        errorEl.classList.remove('hidden');
    };

    const hideError = () => {
        errorEl.classList.add('hidden');
        errorEl.textContent = '';
    };

    const hideResults = () => {
        resultsEl.classList.add('hidden');
        if (helpEl) helpEl.classList.remove('hidden');
    };

    const renderResults = (result) => {
        const tone = String(result.risk_tone || '').toLowerCase() || 'warning';
        const deliverabilityScore = Math.max(0, Math.min(100, Number(result.deliverability_score || 0)));
        const incompleteAnalysis = Boolean(result.incomplete_analysis);
        const threshold = Number(result.blocking_threshold || 0);
        const score = Number(result.score || 0);
        const shouldBlock = Boolean(result.should_block);

        renderGauge(deliverabilityScore);

        if (badgeEl) {
            if (tone === 'positive') {
                badgeEl.className = 'text-[10px] uppercase tracking-widest font-bold text-emerald-600 dark:text-emerald-400 mt-1';
                badgeEl.textContent = result.assessment || 'GOOD';
            } else if (tone === 'danger') {
                badgeEl.className = 'text-[10px] uppercase tracking-widest font-bold text-red-500 dark:text-red-400 mt-1';
                badgeEl.textContent = result.assessment || 'POOR';
            } else {
                badgeEl.className = 'text-[10px] uppercase tracking-widest font-bold text-amber-500 dark:text-amber-400 mt-1';
                badgeEl.textContent = result.assessment || 'WARNING';
            }
        }

        if (descEl) {
            descEl.textContent = incompleteAnalysis
                ? 'Score: ' + deliverabilityScore + ' — Missing critical setup. Fix required setup first.'
                : shouldBlock
                ? 'Score: ' + deliverabilityScore + ' — Deliverability is too weak right now. Improve the highlighted items to get above ' + threshold + '.'
                : deliverabilityScore >= 90
                ? (score <= 0
                    ? 'Score: ' + deliverabilityScore + ' — Looks clean. You can proceed and still run a final check after any content edits.'
                    : 'Score: ' + deliverabilityScore + ' — Excellent deliverability outlook. Keep the current setup and content quality for best inbox placement.')
                : deliverabilityScore >= 70
                ? 'Score: ' + deliverabilityScore + ' — Good deliverability outlook. Resolve highlighted warnings to improve consistency.'
                : deliverabilityScore >= 50
                ? 'Score: ' + deliverabilityScore + ' — Risky deliverability outlook. Review the checks below before sending.'
                : 'Score: ' + deliverabilityScore + ' — Poor deliverability outlook. Fix the critical issues below before sending.';
        }

        const checks = Array.isArray(result.checks) ? result.checks : [];
        checkListEl.innerHTML = checks.map((check) => {
            const checkTone = String(check.tone || 'warning').toLowerCase();
            const remarks = Array.isArray(check.remarks) ? check.remarks : [];
            const safeLabel = escapeHtml(check.label || 'Check');
            const safeText = remarks.length > 0 ? escapeHtml(remarks[0].text || '') : '';

            let icon, wrapClass = '', labelClass = 'text-xs font-semibold text-gray-900 dark:text-gray-100', textClass = 'text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 leading-relaxed';
            if (checkTone === 'pending') {
                icon = `<svg class="w-5 h-5 text-gray-300 dark:text-gray-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-dasharray="3 3"/></svg>`;
                labelClass = 'text-xs font-semibold text-gray-400 dark:text-gray-500';
                textClass = 'text-[11px] text-gray-400 dark:text-gray-500 mt-0.5 leading-relaxed';
            } else if (checkTone === 'positive') {
                icon = `<svg class="w-5 h-5 text-emerald-500 flex-shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>`;
            } else if (checkTone === 'danger') {
                icon = `<svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>`;
                wrapClass = ' rounded-xl p-3 bg-red-50 dark:bg-red-900/20';
            } else {
                icon = `<svg class="w-5 h-5 text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>`;
                wrapClass = ' rounded-xl p-3 bg-amber-50 dark:bg-amber-900/20';
            }

            const tip = (checkTone !== 'positive' && checkTone !== 'pending' && safeText)
                ? `<a href="#" class="inline-block mt-1.5 text-[11px] font-semibold text-amber-600 dark:text-amber-400 hover:underline">View Tips</a>`
                : '';

            return `<div class="flex gap-2.5${wrapClass}">${icon}<div class="min-w-0 flex-1"><p class="${labelClass}">${safeLabel}</p>${safeText ? `<p class="${textClass}">${safeText}</p>` : ''}${tip}</div></div>`;
        }).join('');

        if (helpEl) helpEl.classList.add('hidden');
        resultsEl.classList.remove('hidden');
    };

    const runSpamCheck = async () => {
        hideError();
        setLoading(true);
        const requestId = ++activeRequestId;

        try {
            const payload = await collectCampaignContent();

            const response = await fetch(routeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json();
            if (!response.ok || !data || !data.result) {
                throw new Error((data && data.message) ? data.message : 'Failed to calculate spam score.');
            }

            if (requestId !== activeRequestId) {
                return;
            }

            renderResults(data.result);
        } catch (error) {
            showError(error && error.message ? error.message : 'Spam checker failed. Please try again.');
        } finally {
            if (requestId === activeRequestId) {
                setLoading(false);
            }
        }
    };

    const scheduleAutoCheck = () => {
        if (autoCheckTimer) {
            clearTimeout(autoCheckTimer);
        }
        autoCheckTimer = setTimeout(() => {
            runSpamCheck();
        }, 700);
    };

    [
        'subject',
        'from_name',
        'from_email',
        'reply_to',
        'delivery_server_id',
        'reply_server_id',
        'template_id',
        'list_id',
    ].forEach((id) => {
        const field = document.getElementById(id);
        if (!field) {
            return;
        }

        const eventName = field.tagName === 'SELECT' ? 'change' : 'input';
        field.addEventListener(eventName, scheduleAutoCheck);
        if (eventName !== 'change') {
            field.addEventListener('change', scheduleAutoCheck);
        }
    });

    document.addEventListener('campaign:spam-input-changed', scheduleAutoCheck);

    const bindUnlayerAutoCheck = () => {
        if (!window.unlayer || typeof window.unlayer.addEventListener !== 'function') {
            return false;
        }

        if (window.__mailpurseSpamUnlayerBound === true) {
            return true;
        }

        window.unlayer.addEventListener('design:updated', () => {
            scheduleAutoCheck();
        });
        window.__mailpurseSpamUnlayerBound = true;
        return true;
    };

    if (!bindUnlayerAutoCheck()) {
        let attempts = 0;
        const poll = setInterval(() => {
            attempts += 1;
            if (bindUnlayerAutoCheck() || attempts > 80) {
                clearInterval(poll);
            }
        }, 250);
    }

    if (rerunBtn) {
        rerunBtn.addEventListener('click', () => {
            hideResults();
            runSpamCheck();
        });
    }

    hideResults();
    runSpamCheck();
};

const initCampaignTemplateLoader = () => {
    const templateSelect = document.getElementById('template_id');
    const htmlContent = document.getElementById('html_content');
    const plainTextContent = document.getElementById('plain_text_content');
    const designInput = document.getElementById('grapesjs_data');
    const baseUrl = '{{ url("/customer/templates") }}';

    if (!templateSelect) {
        return;
    }

    if (templateSelect.dataset.templateLoaderBound === '1') {
        return;
    }
    templateSelect.dataset.templateLoaderBound = '1';

    const waitForUnlayer = (timeoutMs = 20000) => new Promise((resolve, reject) => {
        const start = Date.now();
        const tick = () => {
            if (window.__mailpurseUnlayerReady === true && window.unlayer && typeof window.unlayer.loadDesign === 'function') {
                resolve();
                return;
            }
            if (Date.now() - start >= timeoutMs) {
                reject(new Error('Unlayer editor is not ready yet.'));
                return;
            }
            setTimeout(tick, 75);
        };
        tick();
    });

    const ensureUnlayerInitVisible = () => {
        try {
            if (typeof window.__mailpurseSetupUnlayerEditors === 'function') {
                window.__mailpurseSetupUnlayerEditors();
            }
        } catch (e) {
        }
    };

    const loadTemplate = (templateId) => {
        if (!templateId) {
            if (htmlContent) {
                htmlContent.value = '';
            }
            if (plainTextContent) {
                plainTextContent.value = '';
            }
            if (designInput) {
                designInput.value = '';
            }
            window.__mailpurseUnlayerPendingDesign = null;
            document.dispatchEvent(new CustomEvent('campaign:spam-input-changed'));
            return;
        }

        fetch(`${baseUrl}/${templateId}/content`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (htmlContent && data.html_content) {
                htmlContent.value = data.html_content;
            }
            if (plainTextContent && data.plain_text_content) {
                plainTextContent.value = data.plain_text_content;
            }

            document.dispatchEvent(new CustomEvent('campaign:spam-input-changed'));

            if (data && data.builder === 'unlayer' && data.builder_data) {
                const design = data.builder_data;
                if (designInput) {
                    try {
                        designInput.value = JSON.stringify(design);
                    } catch (e) {
                        designInput.value = '';
                    }
                }

                window.__mailpurseUnlayerPendingDesign = design;
                ensureUnlayerInitVisible();

                if (window.__mailpurseUnlayerReady === true && window.unlayer && typeof window.unlayer.loadDesign === 'function') {
                    try {
                        window.unlayer.loadDesign(design);
                        window.__mailpurseUnlayerPendingDesign = null;
                    } catch (e) {
                    }
                    return;
                }

                waitForUnlayer()
                    .then(() => {
                        try {
                            window.unlayer.loadDesign(design);
                            window.__mailpurseUnlayerPendingDesign = null;
                        } catch (e) {
                        }
                    })
                    .catch(() => {
                    });
            }
        })
        .catch(error => {
            console.error('Error loading template:', error);
        });
    };

    templateSelect.addEventListener('change', function() {
        loadTemplate(this.value);
    });

    if (templateSelect.value) {
        loadTemplate(templateSelect.value);
    }
};

document.addEventListener('DOMContentLoaded', initCampaignTemplateLoader);
document.addEventListener('turbo:load', initCampaignTemplateLoader);
</script>

<script type="application/json" id="campaign-tags-json">@json($campaignTagsByList)</script>
<script>
const initCampaignPersonalizationTags = () => {
    const listSelect = document.getElementById('list_id');
    const containers = document.querySelectorAll('[data-campaign-tags]');
    if (!containers.length) {
        return;
    }

    if (containers[0].dataset.tagsBound === '1') {
        return;
    }
    containers.forEach((c) => { c.dataset.tagsBound = '1'; });

    const jsonEl = document.getElementById('campaign-tags-json');
    let byList = {};
    try {
        byList = jsonEl ? JSON.parse((jsonEl.textContent || '').trim() || '{}') : {};
    } catch (e) {
        byList = {};
    }

    function fallbackCopyText(text) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.setAttribute('readonly', '');
        textarea.style.position = 'fixed';
        textarea.style.top = '-9999px';
        textarea.style.left = '-9999px';
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
        } finally {
            textarea.remove();
        }
    }

    async function copyText(text) {
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(text);
            return;
        }
        fallbackCopyText(text);
    }

    function renderTags() {
        const listId = listSelect ? String(listSelect.value || '') : '';
        const config = (byList && listId && byList[listId] && typeof byList[listId] === 'object') ? byList[listId] : null;
        const standard = config && Array.isArray(config.standard) ? config.standard : [];
        const custom = config && Array.isArray(config.custom) ? config.custom : [];
        const tags = [...standard, ...custom];

        const html = `
            <div class='flex flex-wrap gap-2'>
                ${tags.map((t, i) => {
                    const safeLabel = String(t.label).replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    const safeTag = String(t.tag).replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    return `<button type='button' class='px-2.5 py-1.5 rounded-md border border-gray-200 dark:border-gray-700 text-xs font-mono text-gray-800 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700' data-copy-text='${safeTag}' title='Copy'>${safeLabel}: ${safeTag}</button>`;
                }).join('')}
            </div>
            ${listId ? '' : `<div class='mt-2 text-xs text-gray-500 dark:text-gray-400'>Select an Email List to see available tags for that list.</div>`}
        `;

        containers.forEach((c) => {
            c.innerHTML = html;
        });
    }

    document.addEventListener('click', async function (e) {
        const btn = e.target && e.target.closest ? e.target.closest('[data-copy-text]') : null;
        if (!btn) return;
        const text = btn.getAttribute('data-copy-text') || '';
        if (!text) return;
        const originalTitle = btn.getAttribute('title') || '';
        try {
            btn.disabled = true;
            await copyText(text);
            btn.setAttribute('title', 'Copied!');
            setTimeout(function () {
                btn.disabled = false;
                btn.setAttribute('title', originalTitle || 'Copy');
            }, 1200);
        } catch (err) {
            btn.disabled = false;
        }
    });

    if (listSelect) {
        listSelect.addEventListener('change', renderTags);
    }
    renderTags();
};

document.addEventListener('DOMContentLoaded', initCampaignPersonalizationTags);
document.addEventListener('turbo:load', initCampaignPersonalizationTags);

const initServerPing = () => {
    const pingUrl = '{{ route('customer.campaigns.server-ping') }}';
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content
                 || document.querySelector('input[name="_token"]')?.value
                 || '';

    const loadingHtml = `<svg class="inline-block w-3 h-3 animate-spin mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>Checking…`;

    const setBadge = (badge, state, message) => {
        if (!badge) return;
        badge.classList.remove('hidden', 'bg-green-100', 'text-green-700', 'bg-red-100', 'text-red-700', 'bg-gray-100', 'text-gray-500', 'dark:bg-green-900/40', 'dark:text-green-300', 'dark:bg-red-900/40', 'dark:text-red-300');
        badge.classList.remove('dark:bg-gray-700', 'dark:text-gray-400');
        if (state === 'loading') {
            badge.classList.add('bg-gray-100', 'text-gray-500', 'dark:bg-gray-700', 'dark:text-gray-400');
            badge.innerHTML = loadingHtml;
        } else if (state === 'ok') {
            badge.classList.add('bg-green-100', 'text-green-700', 'dark:bg-green-900/40', 'dark:text-green-300');
            badge.innerHTML = `<svg class="inline-block w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>${message}`;
        } else {
            badge.classList.add('bg-red-100', 'text-red-700', 'dark:bg-red-900/40', 'dark:text-red-300');
            badge.innerHTML = `<svg class="inline-block w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>${message}`;
        }
        badge.classList.remove('hidden');
    };

    const ping = async (type, id, badge) => {
        if (!id) {
            if (badge) badge.classList.add('hidden');
            return;
        }
        setBadge(badge, 'loading', '');
        try {
            const res = await fetch(pingUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ type, id: Number(id) }),
            });
            const data = await res.json().catch(() => ({ ok: false, message: 'Invalid response.' }));
            setBadge(badge, data.ok ? 'ok' : 'error', data.message || (data.ok ? 'OK' : 'Failed'));
        } catch (e) {
            setBadge(badge, 'error', 'Connection error.');
        }
    };

    const deliverySelect = document.getElementById('delivery_server_id');
    const deliveryBadge  = document.getElementById('delivery-ping-badge');
    const replySelect    = document.getElementById('reply_server_id');
    const replyBadge     = document.getElementById('reply-ping-badge');
    const bounceSelect   = document.getElementById('bounce_server_id');
    const bounceBadge    = document.getElementById('bounce-ping-badge');

    if (deliverySelect) {
        deliverySelect.addEventListener('change', () => ping('delivery', deliverySelect.value, deliveryBadge));
        if (deliverySelect.value) ping('delivery', deliverySelect.value, deliveryBadge);
    }
    if (replySelect) {
        replySelect.addEventListener('change', () => ping('reply', replySelect.value, replyBadge));
        if (replySelect.value) ping('reply', replySelect.value, replyBadge);
    }
    if (bounceSelect) {
        bounceSelect.addEventListener('change', () => ping('bounce', bounceSelect.value, bounceBadge));
        if (bounceSelect.value) ping('bounce', bounceSelect.value, bounceBadge);
    }
};

document.addEventListener('DOMContentLoaded', initServerPing);
document.addEventListener('turbo:load', initServerPing);

const initDeliveryServerModeToggle = () => {
    const singleMode = document.getElementById('delivery_mode_single');
    const rotationMode = document.getElementById('delivery_mode_rotation');
    const singlePanel = document.getElementById('single-server-panel');
    const rotationPanel = document.getElementById('rotation-server-panel');
    const rotationEnabled = document.getElementById('inbox_rotation_enabled');
    const deliverySelect = document.getElementById('delivery_server_id');

    if (!singleMode || !rotationMode || !singlePanel || !rotationPanel || !rotationEnabled) {
        return;
    }

    const applyMode = () => {
        const isRotation = !!rotationMode.checked;
        singlePanel.classList.toggle('hidden', isRotation);
        rotationPanel.classList.toggle('hidden', !isRotation);
        rotationEnabled.value = isRotation ? '1' : '0';

        if (isRotation && deliverySelect) {
            deliverySelect.value = '';
        }
    };

    singleMode.addEventListener('change', applyMode);
    rotationMode.addEventListener('change', applyMode);
    applyMode();
};

document.addEventListener('DOMContentLoaded', initDeliveryServerModeToggle);
document.addEventListener('turbo:load', initDeliveryServerModeToggle);

const initCampaignSesFromEmail = () => {
    const deliverySelect = document.getElementById('delivery_server_id');
    const fromEmailInput = document.getElementById('from_email');
    const warning = document.getElementById('ses-from-email-warning');

    if (!deliverySelect || !fromEmailInput || !warning) {
        return;
    }

    if (deliverySelect.dataset.sesFromEmailBound === '1') {
        return;
    }
    deliverySelect.dataset.sesFromEmailBound = '1';

    const isSesServer = (optionEl) => {
        const type = optionEl ? String(optionEl.dataset.type || '') : '';
        return type === 'amazon-ses';
    };

    const getServerFromEmail = (optionEl) => {
        const val = optionEl ? String(optionEl.dataset.fromEmail || '') : '';
        return val.trim();
    };

    const updateState = (opts = {}) => {
        const selectedOption = deliverySelect.options[deliverySelect.selectedIndex] || null;
        const isSes = isSesServer(selectedOption);
        const serverFrom = getServerFromEmail(selectedOption);

        if (isSes && serverFrom && opts.applyServerFromEmail) {
            fromEmailInput.value = serverFrom;
        }

        if (!isSes) {
            warning.classList.add('hidden');
            return;
        }

        const currentFrom = String(fromEmailInput.value || '').trim().toLowerCase();
        const expectedFrom = String(serverFrom || '').trim().toLowerCase();

        if (!expectedFrom) {
            warning.classList.remove('hidden');
            return;
        }

        if (currentFrom && currentFrom !== expectedFrom) {
            warning.classList.remove('hidden');
            return;
        }

        warning.classList.add('hidden');
    };

    deliverySelect.addEventListener('change', function () {
        updateState({ applyServerFromEmail: true });
    });

    fromEmailInput.addEventListener('input', function () {
        updateState({ applyServerFromEmail: false });
    });

    updateState({ applyServerFromEmail: false });
};

document.addEventListener('DOMContentLoaded', initCampaignSesFromEmail);
document.addEventListener('turbo:load', initCampaignSesFromEmail);

// Initialize feature help
document.addEventListener('DOMContentLoaded', initFeatureHelp);
document.addEventListener('turbo:load', initFeatureHelp);
document.addEventListener('DOMContentLoaded', initSpamScoreChecker);
document.addEventListener('turbo:load', initSpamScoreChecker);

</script>

<script type="application/json" id="campaign-wizard-errors">@json(array_keys($errors->toArray()))</script>
<script>
const initCampaignWizard = () => {
    const form = document.getElementById('unlayer-form');
    if (!form || form.dataset.wizardBound === '1') {
        return;
    }
    form.dataset.wizardBound = '1';

    const storageKey = 'mailpurse.campaign.createWizardDraft';

    const stepEls = Array.from(form.querySelectorAll('[data-campaign-step]'));
    const accordionSteps = Array.from(form.querySelectorAll('[data-accordion-step]'));
    const accordionTriggers = Array.from(form.querySelectorAll('[data-accordion-trigger]'));
    const stepSaves = Array.from(form.querySelectorAll('[data-step-save]'));
    const stepCancels = Array.from(form.querySelectorAll('[data-step-cancel]'));
    const submitBtn = document.getElementById('btn-save');
    const saveDraftBtn = document.getElementById('btn-save-draft');
    const stepInput = document.getElementById('wizard_step');
    const statusInput = document.getElementById('campaign_status');
    const headerTitle = document.getElementById('campaign-header-title');
    const audienceInput = document.getElementById('audience_selection');
    const listInput = document.getElementById('list_id');
    const segmentsHiddenInputs = document.getElementById('segments-hidden-inputs');

    const fieldStep = {
        name: 1,
        list_id: 1,
        segments: 1,
        delivery_server_id: 2,
        reply_server_id: 2,
        sending_domain_id: 2,
        tracking_domain_id: 2,
        bounce_server_id: 2,
        from_name: 2,
        from_email: 2,
        subject: 3,
        template_id: 4,
        signature_template_id: 4,
        footer_template_id: 4,
        html_content: 4,
        plain_text_content: 4,
        template_data: 4,
        send_at: 4,
        track_opens: 4,
        track_clicks: 4,
        enable_spintax: 4,
        spam_scoring_enabled: 4,
        recurring_interval_days: 4,
    };

    const readDraftState = () => {
        try {
            return JSON.parse(window.sessionStorage.getItem(storageKey) || '{}') || {};
        } catch (e) {
            return {};
        }
    };

    const writeDraftState = (state) => {
        try {
            window.sessionStorage.setItem(storageKey, JSON.stringify(state));
        } catch (e) {
        }
    };

    const collectDraftFields = () => {
        const fields = {};

        Array.from(form.querySelectorAll('input[name], select[name], textarea[name]')).forEach((el) => {
            const name = el.name;
            if (!name || el.type === 'file' || name === '_token') {
                return;
            }

            if (el.type === 'checkbox') {
                fields[name] = Boolean(el.checked);
                return;
            }

            if (el.type === 'radio') {
                if (el.checked) {
                    fields[name] = el.value;
                }
                return;
            }

            if (name.endsWith('[]')) {
                if (!Array.isArray(fields[name])) {
                    fields[name] = [];
                }
                fields[name].push(el.value);
                return;
            }

            fields[name] = el.value;
        });

        return fields;
    };

    const persistDraftState = () => {
        writeDraftState({
            step: getCurrentStep(),
            fields: collectDraftFields(),
        });
    };

    const applyDraftFields = (fields) => {
        Object.entries(fields || {}).forEach(([name, value]) => {
            if (name.endsWith('[]')) {
                return;
            }

            const elements = Array.from(form.querySelectorAll(`[name="${CSS.escape(name)}"]`));
            if (!elements.length) {
                return;
            }

            elements.forEach((el) => {
                if (el.type === 'checkbox') {
                    el.checked = Boolean(value);
                } else if (el.type === 'radio') {
                    el.checked = String(el.value) === String(value);
                } else if (!String(el.value || '').trim()) {
                    el.value = value == null ? '' : value;
                }
            });
        });

        if (Array.isArray(fields['segments[]']) && segmentsHiddenInputs) {
            segmentsHiddenInputs.innerHTML = '';
            fields['segments[]'].forEach((segmentId) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'segments[]';
                input.value = segmentId;
                segmentsHiddenInputs.appendChild(input);
            });
        }
    };

    const readErrors = () => {
        const el = document.getElementById('campaign-wizard-errors');
        try {
            return el ? (JSON.parse((el.textContent || '').trim() || '[]') || []) : [];
        } catch (e) {
            return [];
        }
    };

    const clampStep = (n) => {
        const step = Number(n);
        if (!Number.isFinite(step)) return 1;
        return Math.min(4, Math.max(1, step));
    };

    let maxUnlockedStep = clampStep(stepInput ? stepInput.value : 1);

    const updateSummaries = (step) => {
        const nameVal = (document.getElementById('name') || {}).value || '';
        const s1 = form.querySelector('[data-step-summary="1"]');
        if (s1) {
            const audience = document.getElementById('audience_selection');
            const audienceLabel = audience && audience.value ? (audience.options[audience.selectedIndex].text || '') : '';
            if (nameVal || audienceLabel) {
                const parts = [];
                if (nameVal) parts.push(nameVal);
                if (audienceLabel) parts.push(audienceLabel);
                s1.textContent = parts.join(' • ');
            } else if (step > 1) {
                s1.textContent = 'Configured';
            } else {
                s1.textContent = 'Campaign name, list, and segments';
            }
        }

        const s2 = form.querySelector('[data-step-summary="2"]');
        if (s2) {
            const ds = document.getElementById('delivery_server_id');
            const dsLabel = ds && ds.value ? (ds.options[ds.selectedIndex].text || '') : '';
            const fn = (document.getElementById('from_name') || {}).value || '';
            const fe = (document.getElementById('from_email') || {}).value || '';
            if (fn || fe || dsLabel) {
                const parts = [];
                if (fn) parts.push(fn);
                if (fe) parts.push('<' + fe + '>');
                if (dsLabel && !fn && !fe) parts.push(dsLabel);
                s2.textContent = parts.join(' ');
            } else {
                s2.textContent = step > 2 ? 'Configured' : 'Choose sender identity and infrastructure';
            }
        }

        const s3 = form.querySelector('[data-step-summary="3"]');
        if (s3) {
            const subject = (document.getElementById('subject') || {}).value || '';
            if (subject) {
                s3.textContent = subject;
            } else {
                s3.textContent = step > 3 ? 'Configured' : 'Write a catchy subject line';
            }
        }

        const s4 = form.querySelector('[data-step-summary="4"]');
        if (s4) {
            const tm = document.getElementById('template_id');
            const hasBuilder = !!(document.getElementById('html_content') && String(document.getElementById('html_content').value || '').trim() !== '');
            const sa = (document.getElementById('send_at') || {}).value || '';
            const to = document.querySelector('input[name="track_opens"]');
            const tc = document.querySelector('input[name="track_clicks"]');
            if (tm && tm.value) {
                s4.textContent = 'Template: ' + (tm.options[tm.selectedIndex].text || '');
            } else if (hasBuilder) {
                s4.textContent = 'Custom builder content';
            } else if (sa || to || tc) {
                const parts = [];
                if (sa) parts.push('Scheduled');
                else parts.push('Send now');
                if (to && to.checked) parts.push('Track opens');
                if (tc && tc.checked) parts.push('Track clicks');
                s4.textContent = parts.join(' • ');
            } else {
                s4.textContent = step > 4 ? 'Configured' : 'Design the layout and write the copy for your email.';
            }
        }

        if (headerTitle) {
            headerTitle.textContent = nameVal || 'New Campaign';
        }
    };

    const setStepUi = (n) => {
        const step = clampStep(n);
        maxUnlockedStep = Math.max(maxUnlockedStep, step);

        stepEls.forEach((el) => {
            const elStep = clampStep(el.getAttribute('data-campaign-step'));
            if (elStep === step) {
                el.classList.remove('hidden');
                el.classList.add('contents');
            } else {
                el.classList.add('hidden');
                el.classList.remove('contents');
            }
        });

        accordionSteps.forEach((el) => {
            const elStep = clampStep(el.getAttribute('data-accordion-step'));
            el.classList.remove('is-active', 'is-complete', 'is-pending');
            if (elStep === step) {
                el.classList.add('is-active');
            } else if (elStep <= maxUnlockedStep) {
                el.classList.add('is-complete');
            } else {
                el.classList.add('is-pending');
            }
        });

        updateSummaries(step);

        if (stepInput) {
            stepInput.value = String(step);
        }

        if (step === 4) {
            try {
                if (typeof window.__mailpurseSetupUnlayerEditors === 'function') {
                    setTimeout(() => window.__mailpurseSetupUnlayerEditors(), 0);
                }
            } catch (e) {
            }
        }
    };

    const validateStep = (n) => {
        const step = clampStep(n);
        if (step === 1) {
            const nameInput = document.getElementById('name');
            if (nameInput && !nameInput.reportValidity()) {
                return false;
            }
        }
        if (step === 3) {
            const subjectInput = document.getElementById('subject');
            if (subjectInput && !subjectInput.reportValidity()) {
                return false;
            }
        }
        return true;
    };

    const exportUnlayerIfAvailable = () => {
        const editorEl = form.querySelector('[data-unlayer-editor]');
        if (!editorEl || !window.unlayer || typeof window.unlayer.exportHtml !== 'function') {
            return Promise.resolve();
        }

        return new Promise((resolve) => {
            try {
                window.unlayer.exportHtml((data) => {
                    const htmlInput = document.getElementById('html_content');
                    const plainInput = document.getElementById('plain_text_content');
                    const dataInput = document.getElementById('grapesjs_data');

                    if (htmlInput) {
                        htmlInput.value = (data && data.html) ? data.html : '';
                    }

                    if (dataInput) {
                        dataInput.value = JSON.stringify((data && data.design) ? data.design : null);
                    }

                    const plainText = String((data && data.html) ? data.html : '')
                        .replace(/<style[\s\S]*?<\/style>/gi, ' ')
                        .replace(/<script[\s\S]*?<\/script>/gi, ' ')
                        .replace(/<[^>]+>/g, ' ')
                        .replace(/\s+/g, ' ')
                        .trim();

                    if (plainInput) {
                        plainInput.value = plainText;
                    }

                    resolve();
                });
            } catch (e) {
                resolve();
            }
        });
    };

    const getCurrentStep = () => clampStep(stepInput ? stepInput.value : 1);

    const goTo = async (nextStep, options = {}) => {
        const { requireValidation = true } = options;
        const current = getCurrentStep();
        const target = clampStep(nextStep);

        if (target === current) {
            return;
        }

        if (target > current && requireValidation && target > maxUnlockedStep) {
            if (!validateStep(current)) {
                return;
            }
            if (current === 5) {
                await exportUnlayerIfAvailable();
            }
        }

        setStepUi(target);
        persistDraftState();
    };

    accordionTriggers.forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const step = clampStep(trigger.getAttribute('data-accordion-trigger'));
            if (step <= maxUnlockedStep) {
                goTo(step, { requireValidation: false });
            }
        });
    });

    stepSaves.forEach((btn) => {
        btn.addEventListener('click', () => {
            const current = getCurrentStep();
            if (current === 5) {
                if (submitBtn) {
                    submitBtn.click();
                }
                return;
            }
            goTo(current + 1);
        });
    });

    stepCancels.forEach((btn) => {
        btn.addEventListener('click', () => {
            const current = getCurrentStep();
            if (current > 1) {
                goTo(current - 1, { requireValidation: false });
            }
        });
    });

    if (submitBtn) {
        submitBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (statusInput) {
                statusInput.value = 'draft';
            }
            exportUnlayerIfAvailable().then(() => {
                form.submit();
            });
        });
    }

    if (saveDraftBtn) {
        saveDraftBtn.addEventListener('click', () => {
            if (statusInput) {
                statusInput.value = 'draft';
            }
            exportUnlayerIfAvailable().then(() => {
                form.submit();
            });
        });
    }

    const nameInput = document.getElementById('name');
    if (nameInput) {
        nameInput.addEventListener('input', () => {
            updateSummaries(getCurrentStep());
            persistDraftState();
        });
    }

    const syncAudienceSelection = () => {
        if (!audienceInput || !listInput || !segmentsHiddenInputs) {
            return;
        }

        const value = audienceInput.value || '';
        listInput.value = '';
        segmentsHiddenInputs.innerHTML = '';

        if (value.startsWith('list:')) {
            listInput.value = value.split(':')[1] || '';
            return;
        }

        if (value.startsWith('segment:')) {
            const parts = value.split(':');
            const listId = parts[1] || '';
            const segmentId = parts[2] || '';
            listInput.value = listId;

            if (segmentId) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'segments[]';
                input.value = segmentId;
                segmentsHiddenInputs.appendChild(input);
            }
        }
    };

    if (audienceInput) {
        audienceInput.addEventListener('change', () => {
            syncAudienceSelection();
            updateSummaries(getCurrentStep());
            persistDraftState();
        });
    }

    Array.from(form.querySelectorAll('input[name], select[name], textarea[name]')).forEach((el) => {
        const eventName = el.tagName === 'SELECT' || el.type === 'checkbox' || el.type === 'radio' ? 'change' : 'input';
        el.addEventListener(eventName, () => {
            persistDraftState();
        });
    });

    const visualTagInput = document.getElementById('campaign-tags-visual');
    const tagWrap = document.getElementById('campaign-tag-input-wrap');
    if (visualTagInput && tagWrap) {
        visualTagInput.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter' || !visualTagInput.value.trim()) {
                return;
            }

            event.preventDefault();
            const chip = document.createElement('span');
            chip.className = 'tag-pill';
            chip.setAttribute('data-visual-tag', '');
            chip.innerHTML = `${visualTagInput.value.trim()}<button type="button" aria-label="Remove tag">×</button>`;
            tagWrap.insertBefore(chip, visualTagInput);
            visualTagInput.value = '';
        });

        tagWrap.addEventListener('click', (event) => {
            const btn = event.target.closest('button');
            if (!btn) {
                return;
            }
            const chip = btn.closest('[data-visual-tag]');
            if (chip) {
                chip.remove();
            }
        });
    }

    const draftState = readDraftState();
    if (draftState && draftState.fields) {
        applyDraftFields(draftState.fields);
    }

    syncAudienceSelection();

    const errors = readErrors();
    let initial = clampStep(stepInput ? stepInput.value : 1);

    if ((!errors || !errors.length) && draftState && draftState.step) {
        initial = clampStep(draftState.step);
    }

    if (errors && errors.length) {
        const steps = errors
            .map((k) => fieldStep[String(k)] || null)
            .filter((v) => Number.isFinite(v));
        if (steps.length) {
            initial = Math.min(...steps);
        }
    }

    setStepUi(initial);
    persistDraftState();
};

document.addEventListener('DOMContentLoaded', initCampaignWizard);
document.addEventListener('turbo:load', initCampaignWizard);
</script>
@endpush

