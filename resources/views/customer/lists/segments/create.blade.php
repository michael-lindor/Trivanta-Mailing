@extends('layouts.customer')

@section('title', 'Create Segment')

@section('content')
@php
    $selectedListId = (int) ($defaultListIds[0] ?? 0);
    $selectedList   = $selectedListId ? $lists->firstWhere('id', $selectedListId) : $lists->first();
    $listName       = $selectedList ? ($selectedList->display_name ?? $selectedList->name) : 'Lists';

    $activityFields = ['last_opened_at','last_clicked_at','campaign_received','campaign_bounced','campaign_not_opened','campaign_opened','campaign_clicked','subscribed_at','confirmed_at'];

    $oldRaw = old('conditions', []);
    if (is_array($oldRaw) && count($oldRaw) > 0) {
        $initConds = array_values(array_map(function ($c, $i) use ($activityFields) {
            $f = trim((string) ($c['field'] ?? ''));
            $isAct = in_array($f, $activityFields);
            return [
                '_id' => $i + 1,
                'category'          => $isAct ? 'activity' : 'property',
                'activityNegated'   => false,
                'activityType'      => $isAct ? $f : 'last_opened_at',
                'activityFrequency' => 'at_least_once',
                'activityDays'      => $isAct ? max(1, (int) ($c['value'] ?? 30)) : 30,
                'activityUnit'      => 'days',
                'propField'         => !$isAct ? $f : 'email',
                'propOperator'      => (string) ($c['operator'] ?? 'is'),
                'propValue'         => !$isAct ? (string) ($c['value'] ?? '') : '',
            ];
        }, $oldRaw, array_keys($oldRaw)));
    } else {
        $initConds = [[
            '_id' => 1, 'category' => 'activity',
            'activityNegated' => false, 'activityType' => 'last_opened_at',
            'activityFrequency' => 'at_least_once', 'activityDays' => 30, 'activityUnit' => 'days',
            'propField' => 'email', 'propOperator' => 'is', 'propValue' => '',
        ]];
    }
    $oldCombine = old('combine_operator', 'all');
@endphp

<div x-data="segmentBuilder(@js($initConds), @js(array_map('intval', $defaultListIds)), @js($oldCombine))">

    {{-- ── Header ──────────────────────────────────────────────────────────── --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <nav class="mb-1.5 flex items-center gap-1 text-sm text-gray-400 dark:text-gray-500">
                <a href="{{ route('customer.lists.index') }}" class="transition hover:text-gray-600 dark:hover:text-gray-300">Audience</a>
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                @if($selectedList)
                    <a href="{{ route('customer.lists.segments.index', $selectedList) }}" class="transition hover:text-gray-600 dark:hover:text-gray-300">{{ $listName }}</a>
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                @endif
                <span class="text-gray-600 dark:text-gray-300">Create Segment</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-50">Create Segment</h1>
        </div>
        <div class="flex shrink-0 items-center gap-3">
            <a href="{{ $selectedList ? route('customer.lists.segments.index', $selectedList) : route('customer.lists.index', ['tab' => 'segments']) }}"
               class="text-sm font-medium text-gray-600 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">
                Cancel
            </a>
            <button type="submit" form="segment-form"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/></svg>
                Save Segment
            </button>
        </div>
    </div>

    <form method="POST" action="{{ route('customer.segments.store') }}" id="segment-form">
        @csrf

        {{-- Pre-selected list IDs --}}
        @foreach($defaultListIds as $lid)
            <input type="hidden" name="list_ids[]" value="{{ $lid }}">
        @endforeach

        {{-- combine_operator --}}
        <input type="hidden" name="combine_operator" :value="combineOperator">

        {{-- ── Two-column layout ──────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">

            {{-- ── Left (2/3) ───────────────────────────────────────────── --}}
            <div class="space-y-5 lg:col-span-2">

                {{-- Basic info card --}}
                <div class="rounded-xl border border-admin-border bg-white p-6 shadow-sm dark:bg-gray-800">
                    <div class="space-y-5">
                        <div>
                            <label for="seg-name" class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-200">Segment Name</label>
                            <input id="seg-name" type="text" name="name" value="{{ old('name') }}" required
                                   placeholder="e.g. VIP Customers – Q4"
                                   class="block w-full rounded-lg border border-gray-200 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-500">
                            @error('name')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-200">
                                Tags <span class="font-normal text-gray-400">(Optional)</span>
                            </label>
                            <div class="flex min-h-[42px] cursor-text flex-wrap items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-400 dark:border-gray-600 dark:bg-gray-700">
                                <span>Select tags...</span>
                                <svg class="ml-auto h-4 w-4 text-gray-300 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Conditions card --}}
                <div class="rounded-xl border border-admin-border bg-white p-6 shadow-sm dark:bg-gray-800">
                    <h2 class="mb-1 text-base font-semibold text-gray-900 dark:text-gray-50">Conditions</h2>

                    {{-- "Contacts must match ALL of the following rules" --}}
                    <div class="mb-5 flex flex-wrap items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                        Contacts must match
                        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                            <button type="button" @click="open = !open"
                                    class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-white px-2.5 py-1 text-sm font-bold text-gray-800 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                <span x-text="combineOperator === 'all' ? 'ALL' : 'ANY'"></span>
                                <svg class="h-3 w-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                            </button>
                            <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                 class="absolute left-0 z-20 mt-1 w-28 origin-top-left rounded-xl border border-admin-border bg-white shadow-lg dark:bg-gray-800">
                                <div class="p-1">
                                    <button type="button" @click="combineOperator = 'all'; open = false"
                                            class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm transition hover:bg-gray-50 dark:hover:bg-gray-700"
                                            :class="combineOperator === 'all' ? 'font-semibold text-primary-600 dark:text-primary-400' : 'text-gray-700 dark:text-gray-200'">
                                        All
                                        <svg x-show="combineOperator === 'all'" class="h-3.5 w-3.5 text-primary-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
                                    </button>
                                    <button type="button" @click="combineOperator = 'any'; open = false"
                                            class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm transition hover:bg-gray-50 dark:hover:bg-gray-700"
                                            :class="combineOperator === 'any' ? 'font-semibold text-primary-600 dark:text-primary-400' : 'text-gray-700 dark:text-gray-200'">
                                        Any
                                        <svg x-show="combineOperator === 'any'" class="h-3.5 w-3.5 text-primary-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        of the following rules:
                    </div>

                    @error('conditions')<p class="mb-3 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror

                    {{-- Condition blocks --}}
                    <template x-for="(cond, idx) in conditions" :key="cond._id">
                        <div>
                            {{-- Hidden form inputs for this condition --}}
                            <input type="hidden" :name="`conditions[${idx}][field]`" :value="resolveField(cond)">
                            <input type="hidden" :name="`conditions[${idx}][operator]`" :value="resolveOperator(cond)">
                            <input type="hidden" :name="`conditions[${idx}][value]`" :value="resolveValue(cond)">

                            {{-- AND / OR separator --}}
                            <div x-show="idx > 0" class="relative flex items-center justify-center py-3">
                                <div class="absolute inset-0 flex items-center px-1">
                                    <div class="w-full border-t border-dashed border-gray-200 dark:border-gray-600"></div>
                                </div>
                                <div class="relative flex overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-600 dark:bg-gray-800">
                                    <button type="button" @click="combineOperator = 'all'"
                                            class="px-4 py-1 text-xs font-semibold transition"
                                            :class="combineOperator === 'all' ? 'bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900' : 'text-gray-500 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-700'">
                                        AND
                                    </button>
                                    <button type="button" @click="combineOperator = 'any'"
                                            class="border-l border-gray-200 px-4 py-1 text-xs font-semibold transition dark:border-gray-600"
                                            :class="combineOperator === 'any' ? 'bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900' : 'text-gray-500 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-700'">
                                        OR
                                    </button>
                                </div>
                            </div>

                            {{-- Condition card --}}
                            <div class="rounded-xl border border-blue-100 bg-blue-50/40 p-4 dark:border-gray-600 dark:bg-gray-700/30">

                                {{-- Category selector row --}}
                                <div class="mb-4 flex items-start justify-between gap-3">
                                    <div class="relative flex-1" x-data="{ open: false }" @click.outside="open = false">
                                        <button type="button" @click="open = !open"
                                                class="flex w-full items-center justify-between gap-2 rounded-lg border px-3 py-2 text-sm font-medium shadow-sm transition"
                                                :class="open ? 'border-primary-500 bg-white ring-1 ring-primary-500 dark:bg-gray-800' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200'">
                                            <span x-text="({ activity: 'What someone has done (or not done)', property: 'Properties about someone', subscription: 'Subscription details', in_list: 'If someone is in or not in a list', in_segment: 'If someone is in or not in a segment', proximity: 'Proximity to a location' })[cond.category] || cond.category"></span>
                                            <svg class="h-4 w-4 shrink-0 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                                        </button>

                                        <div x-show="open" x-cloak
                                             x-transition:enter="transition ease-out duration-100"
                                             x-transition:enter-start="opacity-0 scale-95"
                                             x-transition:enter-end="opacity-100 scale-100"
                                             class="absolute left-0 right-0 z-30 mt-1 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-800">

                                            @php
                                            $catOptions = [
                                                ['value'=>'activity',   'label'=>'What someone has done (or not done)', 'desc'=>'Track behavior like email opens, clicks, or purchases.',      'icon'=>'M15.042 21.672 13.684 16.6m0 0-2.51 2.225.569-9.47 5.227 7.917-3.286-.672ZM12 2.25V4.5m5.834.166-1.591 1.591M20.25 10.5H18M7.757 14.743l-1.59 1.59M6 10.5H3.75m4.007-4.243-1.59-1.59'],
                                                ['value'=>'property',   'label'=>'Properties about someone',              'desc'=>'Filter by attributes like location, age, or custom fields.',  'icon'=>'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z'],
                                                ['value'=>'in_list',    'label'=>'If someone is in or not in a list',     'desc'=>'Include or exclude members of specific lists.',              'icon'=>'M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z'],
                                                ['value'=>'in_segment', 'label'=>'If someone is in or not in a segment', 'desc'=>'Combine with existing dynamic segments.',                  'icon'=>'M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6ZM13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z'],
                                                ['value'=>'proximity',  'label'=>'Proximity to a location',               'desc'=>'Target based on zip code or exact radius.',                 'icon'=>'M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z'],
                                            ];
                                            @endphp

                                            @foreach($catOptions as $i => $opt)
                                            @if($i > 0)<div class="border-t border-gray-100 dark:border-gray-700/60"></div>@endif
                                            <button type="button" @click="cond.category = '{{ $opt['value'] }}'; open = false"
                                                    class="flex w-full items-start gap-3 px-4 py-3.5 text-left transition"
                                                    :class="cond.category === '{{ $opt['value'] }}' ? 'bg-blue-50 dark:bg-blue-900/20' : 'hover:bg-gray-50 dark:hover:bg-gray-700/50'">
                                                <svg class="mt-0.5 h-5 w-5 shrink-0 transition"
                                                     :class="cond.category === '{{ $opt['value'] }}' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400'"
                                                     fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $opt['icon'] }}"/>
                                                </svg>
                                                <div>
                                                    <p class="text-sm font-semibold leading-snug transition"
                                                       :class="cond.category === '{{ $opt['value'] }}' ? 'text-blue-700 dark:text-blue-300' : 'text-gray-900 dark:text-gray-100'">{{ $opt['label'] }}</p>
                                                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $opt['desc'] }}</p>
                                                </div>
                                            </button>
                                            @endforeach

                                        </div>
                                    </div>

                                    <button type="button" @click="removeCondition(idx)"
                                            :disabled="conditions.length <= 1"
                                            class="mt-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-red-50 hover:text-red-500 disabled:cursor-not-allowed disabled:opacity-30 dark:hover:bg-red-900/20">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                    </button>
                                </div>

                                {{-- ── Activity condition (What someone has done) ── --}}
                                <div x-show="cond.category === 'activity'" class="space-y-3">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="rounded-md border border-blue-200 bg-white px-3 py-1.5 text-sm font-medium text-blue-600 dark:border-blue-700/60 dark:bg-gray-800 dark:text-blue-400">
                                            Person has
                                        </span>
                                        <select x-model="cond.activityType"
                                                class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm text-gray-700 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                            <optgroup label="EMAIL ACTIVITY">
                                                <option value="last_opened_at">Opened Email</option>
                                                <option value="last_clicked_at">Clicked Email</option>
                                                <option value="campaign_received">Received Email</option>
                                                <option value="campaign_bounced">Bounced Email</option>
                                                <option value="campaign_not_opened">Not Opened Email</option>
                                            </optgroup>
                                            <optgroup label="SUBSCRIPTION ACTIVITY">
                                                <option value="subscribed_at">Subscribed</option>
                                                <option value="confirmed_at">Confirmed</option>
                                            </optgroup>
                                        </select>
                                        <select x-model="cond.activityFrequency"
                                                class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm text-gray-700 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                            <option value="at_least_once">at least once</option>
                                            <option value="at_least_2">at least 2 times</option>
                                            <option value="at_least_5">at least 5 times</option>
                                        </select>
                                        <input type="text" placeholder="Any campaign..."
                                               class="min-w-[120px] flex-1 rounded-lg border border-blue-200 bg-white px-3 py-1.5 text-sm text-gray-700 placeholder-gray-300 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-blue-700/60 dark:bg-gray-700 dark:text-gray-200">
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">In the last</span>
                                        <input type="number" x-model.number="cond.activityDays" min="1" max="730"
                                               class="w-20 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm text-gray-700 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                        <select x-model="cond.activityUnit"
                                                class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm text-gray-700 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                            <option value="days">days</option>
                                            <option value="weeks">weeks</option>
                                            <option value="months">months</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- ── Properties condition ── --}}
                                <div x-show="cond.category === 'property'" class="flex flex-wrap items-center gap-2">
                                    <select x-model="cond.propField"
                                            class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm text-gray-700 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                        <optgroup label="PROFILE">
                                            <option value="email">Email</option>
                                            <option value="first_name">First Name</option>
                                            <option value="last_name">Last Name</option>
                                            <option value="tags">Tags</option>
                                        </optgroup>
                                        <optgroup label="ENGAGEMENT">
                                            <option value="open_count">Open Count</option>
                                            <option value="click_count">Click Count</option>
                                            <option value="inactive_days">Inactive Days</option>
                                        </optgroup>
                                    </select>
                                    <select x-model="cond.propOperator"
                                            class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm text-gray-700 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                        <option value="is">is</option>
                                        <option value="is_not">is not</option>
                                        <option value="contains">contains</option>
                                        <option value="not_contains">does not contain</option>
                                        <option value="greater_than">is greater than</option>
                                        <option value="less_than">is less than</option>
                                    </select>
                                    <input type="text" x-model="cond.propValue" placeholder="Value..."
                                           class="min-w-[140px] flex-1 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm text-gray-700 placeholder-gray-400 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                </div>

                                {{-- ── Subscription condition ── --}}
                                <div x-show="cond.category === 'subscription'" class="flex flex-wrap items-center gap-2">
                                    <select x-model="cond.propField"
                                            class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm text-gray-700 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                        <option value="status">Status</option>
                                        <option value="subscribed_at">Subscribed Date</option>
                                        <option value="source">Source</option>
                                        <option value="confirmed_at">Confirmed Date</option>
                                        <option value="unsubscribed_at">Unsubscribed Date</option>
                                    </select>
                                    <select x-model="cond.propOperator"
                                            class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm text-gray-700 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                        <option value="is">is</option>
                                        <option value="is_not">is not</option>
                                        <option value="before">is before</option>
                                        <option value="after">is after</option>
                                        <option value="in_last_days">in last (days)</option>
                                    </select>
                                    <input type="text" x-model="cond.propValue" placeholder="e.g. confirmed"
                                           class="min-w-[140px] flex-1 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm text-gray-700 placeholder-gray-400 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                </div>

                                {{-- ── In / Not-in a List ── --}}
                                <div x-show="cond.category === 'in_list'" class="flex flex-wrap items-center gap-2">
                                    <select x-model="cond.inListOperator"
                                            class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm text-gray-700 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                        <option value="is_in">is a member of</option>
                                        <option value="not_in">is not a member of</option>
                                    </select>
                                    <select x-model="cond.inListId"
                                            class="flex-1 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm text-gray-700 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                        <option value="">— Select a list —</option>
                                        @foreach($lists as $lst)
                                        <option value="{{ $lst->id }}">{{ $lst->display_name ?? $lst->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- ── In / Not-in a Segment ── --}}
                                <div x-show="cond.category === 'in_segment'" class="flex flex-wrap items-center gap-2">
                                    <select x-model="cond.inSegmentOperator"
                                            class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm text-gray-700 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                        <option value="is_in">is a member of</option>
                                        <option value="not_in">is not a member of</option>
                                    </select>
                                    <input type="text" x-model="cond.inSegmentId" placeholder="Segment name or ID…"
                                           class="min-w-[160px] flex-1 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm text-gray-700 placeholder-gray-400 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                </div>

                                {{-- ── Proximity ── --}}
                                <div x-show="cond.category === 'proximity'" class="flex flex-wrap items-center gap-2">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Within</span>
                                    <input type="number" x-model.number="cond.proximityRadius" min="1" max="500"
                                           class="w-20 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm text-gray-700 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                    <select x-model="cond.proximityUnit"
                                            class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm text-gray-700 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                        <option value="miles">miles</option>
                                        <option value="km">km</option>
                                    </select>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">of zip code</span>
                                    <input type="text" x-model="cond.proximityZip" placeholder="e.g. 10001"
                                           class="w-28 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm text-gray-700 placeholder-gray-400 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- Add condition --}}
                    <div class="mt-5 text-center">
                        <button type="button" @click="addCondition"
                                class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            Add Condition
                        </button>
                    </div>
                </div>
            </div>

            {{-- ── Right sidebar ─────────────────────────────────────────── --}}
            <div class="lg:col-span-1">
                <div class="sticky top-6 rounded-xl border border-admin-border bg-white p-6 shadow-sm dark:bg-gray-800">
                    <h2 class="mb-5 text-base font-semibold text-gray-900 dark:text-gray-50">Segment Summary</h2>

                    <div class="mb-4 py-4 text-center">
                        <p class="text-3xl font-bold tracking-widest text-gray-300 dark:text-gray-600">– –</p>
                        <p class="mt-1.5 text-sm text-gray-500 dark:text-gray-400">Estimated Subscribers</p>
                    </div>

                    <div class="mb-4 rounded-lg bg-blue-50 px-4 py-2.5 text-center text-xs leading-relaxed text-blue-500 dark:bg-blue-900/20 dark:text-blue-400">
                        Save segment to calculate exact size
                    </div>

                    <button type="button"
                            class="w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                        Calculate Size
                    </button>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function segmentBuilder(initConds, listIds, initCombine) {
    const blank = () => ({
        _id: Date.now() + Math.random(),
        category: 'activity',
        activityNegated: false,
        activityType: 'last_opened_at',
        activityFrequency: 'at_least_once',
        activityDays: 30,
        activityUnit: 'days',
        propField: 'email',
        propOperator: 'is',
        propValue: '',
        inListOperator: 'is_in',
        inListId: '',
        inSegmentOperator: 'is_in',
        inSegmentId: '',
        proximityRadius: 25,
        proximityUnit: 'miles',
        proximityZip: '',
    });

    return {
        combineOperator: initCombine || 'all',
        listIds: listIds || [],
        conditions: (Array.isArray(initConds) && initConds.length)
            ? initConds.map(c => Object.assign(blank(), c))
            : [Object.assign(blank(), { _id: 1, category: 'activity' })],

        addCondition() {
            this.conditions.push(blank());
        },

        removeCondition(index) {
            if (this.conditions.length <= 1) return;
            this.conditions.splice(index, 1);
        },

        resolveField(cond) {
            if (cond.category === 'activity')   return cond.activityType || '';
            if (cond.category === 'in_list')    return 'list_membership';
            if (cond.category === 'in_segment') return 'segment_membership';
            if (cond.category === 'proximity')  return 'zip_code';
            return cond.propField || '';
        },

        resolveOperator(cond) {
            if (cond.category === 'activity')   return 'in_last_days';
            if (cond.category === 'in_list')    return cond.inListOperator || 'is_in';
            if (cond.category === 'in_segment') return cond.inSegmentOperator || 'is_in';
            if (cond.category === 'proximity')  return 'within';
            return cond.propOperator || 'is';
        },

        resolveValue(cond) {
            if (cond.category === 'activity')   return String(cond.activityDays || 30);
            if (cond.category === 'in_list')    return String(cond.inListId || '');
            if (cond.category === 'in_segment') return String(cond.inSegmentId || '');
            if (cond.category === 'proximity')  return (cond.proximityZip || '') + ':' + (cond.proximityRadius || 25) + ':' + (cond.proximityUnit || 'miles');
            return cond.propValue || '';
        },
    };
}
</script>
@endpush
