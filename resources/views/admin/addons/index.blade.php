@extends('layouts.admin')

@section('title', __('Addons'))
@section('page-title', __('Addons'))

@section('content')
<div class="space-y-8" x-data="addonsPage()" @dragover.prevent="dragOver = true" @dragleave.prevent="dragOver = false" @drop.prevent="handleDrop($event)">

    {{-- Page Header --}}
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-admin-text-primary tracking-tight">{{ __('Addons') }}</h1>
            <p class="mt-0.5 text-sm text-gray-500 dark:text-admin-text-secondary">{{ __('Extend :app with powerful addon packages.', ['app' => \App\Models\Setting::get('app_name', config('app.name', 'MailPurse'))]) }}</p>
        </div>
        <button type="button" @click="showUploadModal = true"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-[#1E5FEA] hover:bg-blue-700 rounded-lg shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
            </svg>
            {{ __('Upload .zip') }}
        </button>
    </div>

    {{-- Flash Messages --}}
    <div x-show="flashMessage" x-cloak x-transition
        :class="flashType === 'error' ? 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-800 dark:text-red-300' : 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800 text-green-800 dark:text-green-300'"
        class="flex items-center gap-3 px-4 py-3 border rounded-lg text-sm">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <template x-if="flashType === 'error'"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></template>
            <template x-if="flashType !== 'error'"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></template>
        </svg>
        <span x-text="flashMessage"></span>
    </div>

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

    {{-- Drag-to-upload overlay --}}
    <div :class="dragOver ? 'opacity-100' : 'opacity-0 pointer-events-none'"
        class="transition-opacity fixed inset-0 z-40 flex items-center justify-center bg-[#1E5FEA]/10 border-4 border-dashed border-[#1E5FEA] rounded-2xl m-4">
        <div class="text-center">
            <svg class="w-12 h-12 text-[#1E5FEA] mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
            </svg>
            <p class="text-lg font-semibold text-[#1E5FEA]">{{ __('Drop addon .zip to install') }}</p>
        </div>
    </div>

    {{-- ── TABS ── --}}
    <div x-data="{ activeTab: 'available' }">

        <div class="flex items-center gap-1 border-b border-gray-200 dark:border-admin-border mb-6">
            <button type="button"
                @click="activeTab = 'available'"
                :class="activeTab === 'available'
                    ? 'border-b-2 border-[#1E5FEA] text-[#1E5FEA]'
                    : 'border-b-2 border-transparent text-gray-500 dark:text-admin-text-secondary hover:border-gray-300 dark:hover:border-admin-border hover:text-gray-700 dark:hover:text-admin-text-primary'"
                class="px-4 py-2.5 text-sm font-medium -mb-px transition-colors">
                {{ __('Available') }}
                <span class="ml-1.5 px-1.5 py-0.5 text-xs rounded-full bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-admin-text-secondary">{{ count($catalog) }}</span>
            </button>
            <button type="button"
                @click="activeTab = 'installed'"
                :class="activeTab === 'installed'
                    ? 'border-b-2 border-[#1E5FEA] text-[#1E5FEA]'
                    : 'border-b-2 border-transparent text-gray-500 dark:text-admin-text-secondary hover:border-gray-300 dark:hover:border-admin-border hover:text-gray-700 dark:hover:text-admin-text-primary'"
                class="px-4 py-2.5 text-sm font-medium -mb-px transition-colors">
                {{ __('Installed') }}
                <span class="ml-1.5 px-1.5 py-0.5 text-xs rounded-full bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-admin-text-secondary">{{ $addons->count() }}</span>
            </button>
        </div>

        {{-- ── AVAILABLE ADDONS TAB ── --}}
        <div x-show="activeTab === 'available'">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($catalog as $item)
                <div class="bg-white dark:bg-admin-card border border-gray-100 dark:border-admin-border rounded-xl p-5 flex flex-col gap-3 hover:shadow-sm transition-shadow"
                    x-data="{ installing: false, installed: {{ $item['status'] !== 'available' ? 'true' : 'false' }} }">

                    {{-- Icon + badge row --}}
                    <div class="flex items-start justify-between gap-2">
                        <div class="w-10 h-10 flex-shrink-0 rounded-xl flex items-center justify-center
                            @if($item['icon'] === 'outreach') bg-blue-50 dark:bg-blue-900/20
                            @elseif($item['icon'] === 'scraper') bg-emerald-50 dark:bg-emerald-900/20
                            @elseif($item['icon'] === 'reports') bg-purple-50 dark:bg-purple-900/20
                            @elseif($item['icon'] === 'ai') bg-orange-50 dark:bg-orange-900/20
                            @else bg-red-50 dark:bg-red-900/20 @endif">
                            @if($item['icon'] === 'outreach')
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            @elseif($item['icon'] === 'scraper')
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8" stroke-width="1.75"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="m21 21-4.35-4.35M11 8v6M8 11h6"/></svg>
                            @elseif($item['icon'] === 'reports')
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            @elseif($item['icon'] === 'ai')
                                <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-2"/></svg>
                            @else
                                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            @endif
                        </div>
                        @if($item['status'] === 'active')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/20 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>{{ __('Active') }}
                            </span>
                        @elseif($item['status'] === 'installed')
                            <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium text-gray-500 dark:text-admin-text-secondary bg-gray-100 dark:bg-white/10 rounded-full">{{ __('Installed') }}</span>
                        @endif
                    </div>

                    {{-- Info --}}
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-admin-text-primary leading-tight">{{ $item['name'] }}</p>
                        <p class="text-xs text-gray-400 dark:text-admin-text-secondary mt-0.5">v{{ $item['version'] }}</p>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-admin-text-secondary leading-relaxed">{{ $item['description'] }}</p>

                    {{-- Action button --}}
                    <div class="mt-auto pt-1 flex items-center gap-2">
                        @if($item['status'] === 'active')
                            @if($item['slug'] === 'super-scrape')
                                <a href="{{ route('admin.super-scrape.settings') }}"
                                   class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold text-[#1E5FEA] border border-[#1E5FEA]/30 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/40 rounded-lg transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    {{ __('Settings') }}
                                </a>
                            @endif
                            <form method="POST" action="{{ route('admin.addons.deactivate', $item['record']) }}" class="{{ $item['slug'] === 'super-scrape' ? '' : 'flex-1' }}">
                                @csrf
                                <button type="submit" class="{{ $item['slug'] === 'super-scrape' ? 'w-full' : 'w-full' }} px-3 py-2 text-xs font-medium text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 hover:bg-amber-100 rounded-lg transition-colors">
                                    {{ __('Deactivate') }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.addons.uninstall', $item['record']) }}"
                                onsubmit="return confirm('{{ __('Uninstall :name?', ['name' => $item['name']]) }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        @elseif($item['status'] === 'installed')
                            <button type="button"
                                @click="$dispatch('open-activate', { id: {{ $item['record']->id }}, name: '{{ addslashes($item['name']) }}' })"
                                class="flex-1 px-3 py-2 text-xs font-semibold text-white bg-[#1E5FEA] hover:bg-blue-700 rounded-lg transition-colors">
                                {{ __('Activate') }}
                            </button>
                            <form method="POST" action="{{ route('admin.addons.uninstall', $item['record']) }}"
                                onsubmit="return confirm('{{ __('Uninstall :name?', ['name' => $item['name']]) }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        @else
                            {{-- Available — install from server --}}
                            @if(!empty($item['purchase_url']) && $item['purchase_url'] !== '#')
                                <a href="{{ $item['purchase_url'] }}" target="_blank" rel="noopener noreferrer"
                                    class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold text-[#1E5FEA] border border-[#1E5FEA]/40 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/40 rounded-lg transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    {{ __('Purchase') }}
                                </a>
                            @endif
                            <button type="button"
                                :disabled="installing"
                                @click="$dispatch('open-install', { slug: '{{ $item['slug'] }}', name: '{{ addslashes($item['name']) }}' })"
                                class="{{ empty($item['purchase_url']) || $item['purchase_url'] === '#' ? 'flex-1' : '' }} flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold text-white bg-[#1E5FEA] hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed rounded-lg transition-colors">
                                <template x-if="!installing">
                                    <span>{{ __('Install') }}</span>
                                </template>
                                <template x-if="installing">
                                    <span class="flex items-center gap-1.5">
                                        <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                        </svg>
                                        {{ __('Installing...') }}
                                    </span>
                                </template>
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        </div>{{-- end available tab --}}

        {{-- ── INSTALLED ADDONS TAB ── --}}
        <div x-show="activeTab === 'installed'">
        @if($addons->isNotEmpty())
        <div class="bg-white dark:bg-admin-card border border-gray-100 dark:border-admin-border rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-admin-border bg-gray-50/50 dark:bg-white/2">
                        <th class="text-left text-xs font-medium text-gray-500 dark:text-admin-text-secondary px-5 py-3">{{ __('Addon') }}</th>
                        <th class="text-left text-xs font-medium text-gray-500 dark:text-admin-text-secondary px-4 py-3">{{ __('Version') }}</th>
                        <th class="text-left text-xs font-medium text-gray-500 dark:text-admin-text-secondary px-4 py-3">{{ __('Status') }}</th>
                        <th class="text-left text-xs font-medium text-gray-500 dark:text-admin-text-secondary px-4 py-3">{{ __('Installed') }}</th>
                        <th class="text-right text-xs font-medium text-gray-500 dark:text-admin-text-secondary px-5 py-3">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-admin-border">
                    @foreach($addons as $addon)
                        <tr class="hover:bg-gray-50/40 dark:hover:bg-white/2 transition-colors">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 flex-shrink-0 rounded-lg bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-admin-text-primary">{{ $addon->name }}</p>
                                        @if($addon->description)
                                            <p class="text-xs text-gray-400 dark:text-admin-text-secondary mt-0.5 max-w-xs truncate">{{ $addon->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="space-y-1.5">
                                    <span class="text-xs font-mono bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-admin-text-secondary px-2 py-1 rounded">v{{ $addon->version }}</span>
                                    @if(data_get($addon, 'update_info.update_available'))
                                        <p class="text-xs text-amber-600 dark:text-amber-400">
                                            {{ __('Update available : v:version', ['version' => data_get($addon, 'update_info.latest_version')]) }}
                                        </p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                @if($addon->status === 'active')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-full">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>{{ __('Active') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-gray-600 dark:text-admin-text-secondary bg-gray-100 dark:bg-white/10 border border-gray-200 dark:border-admin-border rounded-full">{{ __('Installed') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-xs text-gray-500 dark:text-admin-text-secondary">
                                {{ $addon->installed_at ? $addon->installed_at->format('M j, Y') : '—' }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                    @if(data_get($addon, 'update_info.update_available'))
                                        <form method="POST" action="{{ route('admin.addons.install-update', $addon) }}"
                                            onsubmit="return confirm('{{ __('Install update for :name? This will replace the current addon files.', ['name' => $addon->name]) }}')">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-white bg-[#1E5FEA] hover:bg-blue-700 rounded-lg transition-colors">
                                                {{ __('Install Update') }}
                                            </button>
                                        </form>
                                    @endif
                                    @if($addon->status === 'active')
                                        <form method="POST" action="{{ route('admin.addons.deactivate', $addon) }}">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 hover:bg-amber-100 rounded-lg transition-colors">{{ __('Deactivate') }}</button>
                                        </form>
                                    @else
                                        <button type="button"
                                            @click="openActivateModal({{ $addon->id }}, '{{ addslashes($addon->name) }}')"
                                            class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-white bg-[#1E5FEA] hover:bg-blue-700 rounded-lg transition-colors">{{ __('Activate') }}</button>
                                    @endif
                                    <form method="POST" action="{{ route('admin.addons.uninstall', $addon) }}"
                                        onsubmit="return confirm('{{ __('Uninstall :name?', ['name' => $addon->name]) }}')">
                                        @csrf @method('DELETE')
                                        <button type="submit" title="{{ __('Uninstall') }}" class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-red-600 dark:hover:text-red-400 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-white/10 flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <p class="text-sm font-medium text-gray-900 dark:text-admin-text-primary">{{ __('No addons installed') }}</p>
            <p class="text-xs text-gray-500 dark:text-admin-text-secondary mt-1">{{ __('Go to the Available tab to install addons.') }}</p>
            <button type="button" @click="activeTab = 'available'"
                class="mt-4 px-4 py-2 text-sm font-semibold text-white bg-[#1E5FEA] hover:bg-blue-700 rounded-lg transition-colors">
                {{ __('Browse Addons') }}
            </button>
        </div>
        @endif
        </div>{{-- end installed tab --}}

    </div>{{-- end tabs --}}

    {{-- ──────────── MODALS ──────────── --}}

    {{-- Upload / Install Modal --}}
    <div x-cloak x-show="showUploadModal"
        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
        @click.self="showUploadModal = false"
    >
        <div x-show="showUploadModal"
            x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            class="bg-white dark:bg-admin-card rounded-2xl shadow-xl w-full max-w-md p-6 space-y-5"
        >
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-admin-text-primary">{{ __('Install Addon') }}</h2>
                    <p class="text-xs text-gray-500 dark:text-admin-text-secondary mt-0.5">{{ __('Upload a .zip package to install.') }}</p>
                </div>
                <button type="button" @click="showUploadModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.addons.upload') }}" enctype="multipart/form-data" class="space-y-4" id="upload-addon-form">
                @csrf

                {{-- Drop zone --}}
                <label for="addon_zip_input"
                    class="flex flex-col items-center justify-center w-full h-40 rounded-xl border-2 border-dashed cursor-pointer transition-colors"
                    :class="uploadFile ? 'border-[#1E5FEA] bg-blue-50 dark:bg-blue-900/10' : 'border-gray-200 dark:border-admin-border hover:border-[#1E5FEA] hover:bg-gray-50 dark:hover:bg-white/5'"
                    @dragover.prevent
                    @drop.prevent="setUploadFile($event.dataTransfer.files[0])"
                >
                    <template x-if="!uploadFile">
                        <div class="text-center px-4">
                            <svg class="w-10 h-10 text-gray-300 dark:text-admin-text-secondary mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <p class="text-sm font-medium text-gray-600 dark:text-admin-text-secondary">{{ __('Click to browse') }} <span class="text-[#1E5FEA]">{{ __('or drag & drop') }}</span></p>
                            <p class="text-xs text-gray-400 dark:text-admin-text-secondary mt-1">{{ __('.zip files only — max 100 MB') }}</p>
                        </div>
                    </template>
                    <template x-if="uploadFile">
                        <div class="text-center px-4">
                            <svg class="w-8 h-8 text-[#1E5FEA] mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm font-semibold text-[#1E5FEA]" x-text="uploadFile.name"></p>
                            <p class="text-xs text-gray-400 dark:text-admin-text-secondary mt-0.5" x-text="formatBytes(uploadFile.size)"></p>
                            <button type="button" @click.prevent="uploadFile = null; document.getElementById('addon_zip_input').value = ''"
                                class="mt-2 text-xs text-red-500 hover:text-red-700 underline">{{ __('Remove') }}</button>
                        </div>
                    </template>
                    <input type="file" id="addon_zip_input" name="addon_zip" accept=".zip" class="sr-only"
                        @change="setUploadFile($event.target.files[0])">
                </label>

                @error('addon_zip')
                    <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror

                <div class="flex gap-3">
                    <button type="submit" :disabled="!uploadFile"
                        :class="uploadFile ? 'bg-[#1E5FEA] hover:bg-blue-700 cursor-pointer' : 'bg-gray-200 dark:bg-white/10 cursor-not-allowed text-gray-400'"
                        class="flex-1 py-2.5 text-sm font-semibold text-white rounded-lg transition-colors">
                        {{ __('Install Addon') }}
                    </button>
                    <button type="button" @click="showUploadModal = false"
                        class="flex-1 py-2.5 text-sm font-medium text-gray-700 dark:text-admin-text-primary border border-gray-200 dark:border-admin-border bg-white dark:bg-white/5 hover:bg-gray-50 dark:hover:bg-white/10 rounded-lg transition-colors">
                        {{ __('Cancel') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Install Modal (purchase code + download from server) --}}
    <div x-cloak x-show="showInstallModal"
        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
        @click.self="showInstallModal = false"
    >
        <div x-show="showInstallModal"
            x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            class="bg-white dark:bg-admin-card rounded-2xl shadow-xl w-full max-w-md p-6 space-y-5"
        >
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-admin-text-primary">{{ __('Install Addon') }}</h2>
                    <p class="text-xs text-gray-500 dark:text-admin-text-secondary mt-0.5" x-text="installAddonName"></p>
                </div>
                <button type="button" @click="showInstallModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="space-y-4">
                <div x-show="installError" x-cloak x-transition
                    class="flex items-start gap-2.5 px-3.5 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-sm text-red-700 dark:text-red-300">
                    <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span x-text="installError" class="break-words"></span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-admin-text-primary mb-1.5">{{ __('Purchase Code') }}</label>
                    <input type="text" x-model="installLicenseKey" required
                        placeholder="{{ __('xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx') }}"
                        class="w-full px-3 py-2.5 text-sm border border-gray-200 dark:border-admin-border bg-white dark:bg-white/5 text-gray-900 dark:text-admin-text-primary rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1E5FEA] placeholder-gray-400">
                    <p class="mt-1.5 text-xs text-gray-400 dark:text-admin-text-secondary">{{ __('Enter your Envato/ThemeForest purchase code to download and install.') }}</p>
                </div>
                <div class="flex gap-3">
                    <button type="button"
                        :disabled="installing || !installLicenseKey.trim()"
                        :class="(installing || !installLicenseKey.trim()) ? 'opacity-60 cursor-not-allowed' : 'hover:bg-blue-700 cursor-pointer'"
                        @click="confirmInstall()"
                        class="flex-1 flex items-center justify-center gap-2 py-2.5 text-sm font-semibold text-white bg-[#1E5FEA] rounded-lg transition-colors">
                        <template x-if="!installing">
                            <span>{{ __('Install & Activate') }}</span>
                        </template>
                        <template x-if="installing">
                            <span class="flex items-center gap-2">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                {{ __('Installing...') }}
                            </span>
                        </template>
                    </button>
                    <button type="button" @click="showInstallModal = false" :disabled="installing"
                        class="flex-1 py-2.5 text-sm font-medium text-gray-700 dark:text-admin-text-primary border border-gray-200 dark:border-admin-border bg-white dark:bg-white/5 hover:bg-gray-50 dark:hover:bg-white/10 rounded-lg transition-colors">
                        {{ __('Cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Activate Modal --}}
    <div x-cloak x-show="showActivateModal"
        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
        @click.self="showActivateModal = false"
    >
        <div x-show="showActivateModal"
            x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            class="bg-white dark:bg-admin-card rounded-2xl shadow-xl w-full max-w-md p-6 space-y-5"
        >
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-admin-text-primary">{{ __('Activate Addon') }}</h2>
                    <p class="text-xs text-gray-500 dark:text-admin-text-secondary mt-0.5" x-text="activeAddonName"></p>
                </div>
                <button type="button" @click="showActivateModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form :action="'/admin/addons/' + activeAddonId + '/activate'" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-admin-text-primary mb-1.5">{{ __('Purchase Code') }}</label>
                    <input type="text" name="license_key" required
                        placeholder="{{ __('xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx') }}"
                        class="w-full px-3 py-2.5 text-sm border border-gray-200 dark:border-admin-border bg-white dark:bg-white/5 text-gray-900 dark:text-admin-text-primary rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1E5FEA] placeholder-gray-400">
                    <p class="mt-1.5 text-xs text-gray-400 dark:text-admin-text-secondary">{{ __('Enter your purchase code to activate this addon.') }}</p>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 py-2.5 text-sm font-semibold text-white bg-[#1E5FEA] hover:bg-blue-700 rounded-lg transition-colors">{{ __('Activate') }}</button>
                    <button type="button" @click="showActivateModal = false"
                        class="flex-1 py-2.5 text-sm font-medium text-gray-700 dark:text-admin-text-primary border border-gray-200 dark:border-admin-border bg-white dark:bg-white/5 hover:bg-gray-50 dark:hover:bg-white/10 rounded-lg transition-colors">{{ __('Cancel') }}</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function addonsPage() {
    return {
        showUploadModal: false,
        showActivateModal: false,
        showInstallModal: false,
        activeAddonId: null,
        activeAddonName: '',
        installAddonSlug: '',
        installAddonName: '',
        installLicenseKey: '',
        installError: '',
        uploadFile: null,
        dragOver: false,
        installing: false,
        flashMessage: '',
        flashType: 'success',

        init() {
            this.$el.addEventListener('open-activate', (e) => {
                this.openActivateModal(e.detail.id, e.detail.name);
            });
            this.$el.addEventListener('open-install', (e) => {
                this.installAddonSlug = e.detail.slug;
                this.installAddonName = e.detail.name;
                this.installLicenseKey = '';
                this.installError = '';
                this.showInstallModal = true;
            });
        },

        openActivateModal(id, name) {
            this.activeAddonId = id;
            this.activeAddonName = name;
            this.showActivateModal = true;
        },

        async confirmInstall() {
            if (!this.installLicenseKey.trim()) return;

            this.installing = true;

            try {
                const res = await fetch('{{ route('admin.addons.remote-install') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        slug: this.installAddonSlug,
                        license_key: this.installLicenseKey.trim(),
                    }),
                });

                const data = await res.json();

                if (data.success) {
                    this.showInstallModal = false;
                    this.showFlash(data.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    this.installError = data.error || '{{ __('Installation failed.') }}';
                }
            } catch (e) {
                this.installError = '{{ __('Network error. Please try again.') }}';
            } finally {
                this.installing = false;
            }
        },

        showFlash(msg, type = 'success') {
            this.flashMessage = msg;
            this.flashType = type;
            setTimeout(() => { this.flashMessage = ''; }, 6000);
        },

        setUploadFile(file) {
            if (file && file.name.endsWith('.zip')) {
                this.uploadFile = file;
                const dt = new DataTransfer();
                dt.items.add(file);
                document.getElementById('addon_zip_input').files = dt.files;
            }
        },

        handleDrop(e) {
            this.dragOver = false;
            const file = e.dataTransfer.files[0];
            if (file && file.name.endsWith('.zip')) {
                this.setUploadFile(file);
                this.showUploadModal = true;
            }
        },

        formatBytes(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / 1048576).toFixed(1) + ' MB';
        },
    };
}
</script>
@endpush
