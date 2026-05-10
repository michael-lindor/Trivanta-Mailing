@extends('layouts.customer')

@section('title', 'Settings – ' . ($list->display_name ?? $list->name))

@section('content')
@php
    $listDisplayName    = $list->display_name ?? $list->name;
    $confirmedCountDisp = (int) ($list->confirmed_subscribers_count ?? 0);
    $lastActivity       = $list->last_subscriber_at ?? $list->updated_at ?? null;
    $inputCls = 'block w-full rounded-lg border border-gray-200 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-500';
@endphp

<div x-data="{ confirmEmpty: false, confirmDelete: false }">

    {{-- ── Header ─────────────────────────────────────────────────────────── --}}
    @include('customer.lists.partials.header', ['list' => $list])

    {{-- ── Subnav ───────────────────────────────────────────────────────────── --}}
    @include('customer.lists.partials.subnav', ['list' => $list])

    {{-- ── Flash messages ───────────────────────────────────────────────────── --}}
    @if(session('success'))
        <div class="mt-4 flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-700/40 dark:bg-emerald-900/20 dark:text-emerald-400">
            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-700/40 dark:bg-red-900/20 dark:text-red-400">
            <ul class="list-disc pl-4 space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="mt-6 space-y-10">

        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- Section 1 · General Details                                     --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <div>
                <h2 class="text-base font-semibold text-gray-900 dark:text-gray-50">General Details</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage the basic information and default settings for this email list.</p>
            </div>
            <div class="lg:col-span-2">
                <form method="POST" action="{{ route('customer.lists.settings.update', $list) }}" class="rounded-xl border border-admin-border bg-white p-6 shadow-sm dark:bg-gray-800">
                    @csrf
                    @method('PUT')

                    <div class="space-y-5">
                        <div>
                            <label for="display_name" class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-200">List Name</label>
                            <input id="display_name" type="text" name="display_name"
                                   value="{{ old('display_name', $list->display_name ?? $list->name) }}"
                                   class="{{ $inputCls }}">
                            @error('display_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="from_name" class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-200">Default From Name</label>
                                <input id="from_name" type="text" name="from_name"
                                       value="{{ old('from_name', $list->from_name) }}"
                                       placeholder="e.g. Sarah Jenkins"
                                       class="{{ $inputCls }}">
                                @error('from_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="from_email" class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-200">Default From Email</label>
                                <input id="from_email" type="email" name="from_email"
                                       value="{{ old('from_email', $list->from_email) }}"
                                       placeholder="hello@example.com"
                                       class="{{ $inputCls }}">
                                @error('from_email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div>
                            <label for="description" class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-200">Internal Description</label>
                            <textarea id="description" name="description" rows="3"
                                      class="{{ $inputCls }} resize-none">{{ old('description', $list->description) }}</textarea>
                            <p class="mt-1.5 text-xs text-gray-400 dark:text-gray-500">Only visible to your team members.</p>
                            @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit"
                                    class="inline-flex items-center rounded-lg bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-700">
                                Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700"></div>

        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- Section 2 · Opt-in Configuration                               --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <div>
                <h2 class="text-base font-semibold text-gray-900 dark:text-gray-50">Opt-in Configuration</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Choose how new subscribers are added to your list when they sign up through forms.</p>
            </div>
            <div class="lg:col-span-2">
                <form method="POST" action="{{ route('customer.lists.settings.update', $list) }}">
                    @csrf
                    @method('PUT')
                    {{-- Pass all required existing values as hidden --}}
                    <input type="hidden" name="from_name" value="{{ $list->from_name }}">
                    <input type="hidden" name="from_email" value="{{ $list->from_email }}">

                    <div x-data="{ optin: '{{ old('double_opt_in', $list->double_opt_in) ? 'double' : 'single' }}' }" class="space-y-3">
                        <input type="hidden" name="double_opt_in" :value="optin === 'double' ? '1' : '0'">

                        {{-- Single Opt-in card --}}
                        <label class="flex cursor-pointer items-start gap-4 rounded-xl border-2 p-5 transition"
                               :class="optin === 'single' ? 'border-primary-500 bg-primary-50/40 dark:border-primary-500/70 dark:bg-primary-900/10' : 'border-gray-200 bg-white hover:border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:hover:border-gray-600'">
                            <div class="relative mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 transition"
                                 :class="optin === 'single' ? 'border-primary-500' : 'border-gray-300 dark:border-gray-600'">
                                <div class="h-2.5 w-2.5 rounded-full bg-primary-500 transition" x-show="optin === 'single'"></div>
                            </div>
                            <div @click="optin = 'single'; $nextTick(() => $el.closest('form').submit())">
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Single Opt-in</p>
                                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Subscribers are added immediately after submitting the signup form. Best for growing your list quickly.</p>
                            </div>
                        </label>

                        {{-- Double Opt-in card --}}
                        <label class="flex cursor-pointer items-start gap-4 rounded-xl border-2 p-5 transition"
                               :class="optin === 'double' ? 'border-primary-500 bg-primary-50/40 dark:border-primary-500/70 dark:bg-primary-900/10' : 'border-gray-200 bg-white hover:border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:hover:border-gray-600'">
                            <div class="relative mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 transition"
                                 :class="optin === 'double' ? 'border-primary-500' : 'border-gray-300 dark:border-gray-600'">
                                <div class="h-2.5 w-2.5 rounded-full bg-primary-500 transition" x-show="optin === 'double'"></div>
                            </div>
                            <div @click="optin = 'double'; $nextTick(() => $el.closest('form').submit())">
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Double Opt-in</p>
                                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Subscribers must confirm their email address by clicking a link in a confirmation email. Better for list hygiene and deliverability.</p>
                            </div>
                        </label>
                    </div>
                </form>
            </div>
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700"></div>

        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- Section 3 · Notifications                                       --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <div>
                <h2 class="text-base font-semibold text-gray-900 dark:text-gray-50">Notifications</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Configure the alerts you receive regarding subscriber activity on this list.</p>
            </div>
            <div class="lg:col-span-2">
                <div x-data="{ dailySummary: true, subscribeAlerts: false, unsubscribeAlerts: false }"
                     class="divide-y divide-gray-100 rounded-xl border border-admin-border bg-white shadow-sm dark:divide-gray-700/60 dark:bg-gray-800">
                    @foreach([
                        ['key' => 'dailySummary',      'label' => 'Daily Summary',       'desc' => 'Receive a daily email with a summary of new subscribers and unsubscribes.'],
                        ['key' => 'subscribeAlerts',   'label' => 'Subscribe Alerts',    'desc' => 'Get an instant notification every time a new user subscribes.'],
                        ['key' => 'unsubscribeAlerts', 'label' => 'Unsubscribe Alerts',  'desc' => 'Get an instant notification every time a user unsubscribes.'],
                    ] as $notif)
                    <div class="flex items-start justify-between gap-6 px-6 py-4">
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $notif['label'] }}</p>
                            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ $notif['desc'] }}</p>
                        </div>
                        <button type="button"
                                @click="{{ $notif['key'] }} = !{{ $notif['key'] }}"
                                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                                :class="{{ $notif['key'] }} ? 'bg-primary-500' : 'bg-gray-200 dark:bg-gray-600'">
                            <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                  :class="{{ $notif['key'] }} ? 'translate-x-5' : 'translate-x-0'"></span>
                        </button>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700"></div>

        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- Section 4 · Danger Zone                                         --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <div>
                <h2 class="text-base font-semibold text-red-600 dark:text-red-400">Danger Zone</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Destructive actions that cannot be easily reversed once executed.</p>
            </div>
            <div class="lg:col-span-2">
                <div class="divide-y divide-red-100 rounded-xl border-2 border-red-200 bg-white dark:divide-red-900/20 dark:border-red-800/50 dark:bg-gray-800">

                    {{-- Empty List --}}
                    <div class="flex items-center justify-between gap-4 px-6 py-5">
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Empty List</p>
                            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Remove all subscribers from this list, but keep the list configuration.</p>
                        </div>
                        <button type="button" @click="confirmEmpty = true"
                                class="shrink-0 rounded-lg border border-red-500 px-4 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-500 dark:text-red-400 dark:hover:bg-red-900/20">
                            Empty List
                        </button>
                    </div>

                    {{-- Delete List --}}
                    <div class="flex items-center justify-between gap-4 px-6 py-5">
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Delete List</p>
                            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Permanently delete this list and all its subscribers. This action cannot be undone.</p>
                        </div>
                        <button type="button" @click="confirmDelete = true"
                                class="shrink-0 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700">
                            Delete List
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- end .space-y-10 --}}

    {{-- ── Empty List Confirmation Modal ───────────────────────────────────── --}}
    <div x-show="confirmEmpty" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="absolute inset-0 bg-black/40" @click="confirmEmpty = false"></div>
        <div class="relative w-full max-w-md rounded-2xl border border-admin-border bg-white p-6 shadow-xl dark:bg-gray-800">
            <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30">
                <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
            </div>
            <h3 class="mb-1 text-base font-bold text-gray-900 dark:text-gray-50">Empty this list?</h3>
            <p class="mb-5 text-sm text-gray-500 dark:text-gray-400">All {{ number_format($confirmedCountDisp) }} subscribers will be permanently removed. The list itself will be kept.</p>
            <div class="flex gap-3">
                <button type="button" @click="confirmEmpty = false" class="flex-1 rounded-lg border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                    Cancel
                </button>
                <form method="POST" action="{{ route('customer.lists.empty', $list) }}" class="flex-1">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full rounded-lg bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-700">
                        Yes, Empty List
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Delete List Confirmation Modal ───────────────────────────────────── --}}
    <div x-show="confirmDelete" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="absolute inset-0 bg-black/40" @click="confirmDelete = false"></div>
        <div class="relative w-full max-w-md rounded-2xl border border-admin-border bg-white p-6 shadow-xl dark:bg-gray-800">
            <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
            </div>
            <h3 class="mb-1 text-base font-bold text-gray-900 dark:text-gray-50">Delete "{{ $listDisplayName }}"?</h3>
            <p class="mb-5 text-sm text-gray-500 dark:text-gray-400">This will permanently delete the list and all its subscribers. <span class="font-semibold text-red-600 dark:text-red-400">This cannot be undone.</span></p>
            <div class="flex gap-3">
                <button type="button" @click="confirmDelete = false" class="flex-1 rounded-lg border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                    Cancel
                </button>
                <form method="POST" action="{{ route('customer.lists.destroy', $list) }}" class="flex-1">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700">
                        Yes, Delete List
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

