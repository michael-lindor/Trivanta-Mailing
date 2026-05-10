@extends('layouts.customer')

@section('title', __('Integrations'))
@section('page-title', __('Integrations'))

@section('content')
<div class="space-y-6" x-data="{ tab: @js($tab), setTab(next) { this.tab = next; const url = new URL(window.location.href); url.searchParams.set('tab', next); window.history.replaceState({}, '', url); } }">
    <div class="border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
        <nav class="-mb-px flex min-w-max space-x-6 sm:space-x-8 px-2 sm:px-0" aria-label="Tabs">
            @if(($canAccessGoogle ?? false))
                <a
                    href="{{ route('customer.integrations.index', ['tab' => 'google']) }}"
                    @click.prevent="setTab('google')"
                    class="whitespace-nowrap shrink-0 py-3 sm:py-4 px-2 sm:px-1 font-medium text-sm"
                    :class="tab === 'google' ? '!border-primary-500 text-primary-600 dark:text-primary-400 border-b-2' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                >
                    {{ __('Google') }}
                </a>
            @else
                <button
                    type="button"
                    disabled
                    class="whitespace-nowrap shrink-0 py-3 sm:py-4 px-2 sm:px-1 font-medium text-sm border-transparent text-gray-400 dark:text-gray-500 cursor-not-allowed"
                >
                    <span class="inline-flex items-center gap-2">
                        {{ __('Google') }}
                        <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-800 px-2 py-0.5 text-xs font-medium text-gray-600 dark:text-gray-300">
                            {{ __('Locked') }}
                        </span>
                    </span>
                </button>
            @endif
            <a
                href="{{ route('customer.integrations.index', ['tab' => 'wordpress']) }}"
                @click.prevent="setTab('wordpress')"
                class="whitespace-nowrap shrink-0 py-3 sm:py-4 px-2 sm:px-1 font-medium text-sm"
                :class="tab === 'wordpress' ? '!border-primary-500 text-primary-600 dark:text-primary-400 border-b-2' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
            >
                {{ __('Wordpress') }}
            </a>
        </nav>
    </div>

    @if(($canAccessGoogle ?? false))
        <div x-show="tab === 'google'" class="space-y-4">
            <x-card>
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Google') }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Connect Google Sheets and Drive to use them across templates, lists, and campaigns.') }}</p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    @php
                        $sheetsIntegration = ($googleIntegrations ?? collect())->get('sheets');
                        $driveIntegration = ($googleIntegrations ?? collect())->get('drive');
                    @endphp

                    <div class="relative rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
                        @if($sheetsIntegration)
                            <div class="absolute top-3 ltr:right-3 rtl:left-3 rtl:right-auto z-10 h-6 w-6 rounded-full bg-green-500 text-white flex items-center justify-center">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        @endif

                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Google Sheets') }}</h4>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Import and auto-sync subscribers, and export campaign reports.') }}</p>
                            </div>
                            @if($sheetsIntegration)
                                <span class="inline-flex items-center rounded-full bg-green-50 dark:bg-green-900/30 px-2 py-0.5 text-xs font-medium text-green-700 dark:text-green-300">{{ __('Connected') }}</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-800 px-2 py-0.5 text-xs font-medium text-gray-600 dark:text-gray-300">{{ __('Not connected') }}</span>
                            @endif
                        </div>

                        <div class="mt-3">
                            <ul class="text-sm text-gray-500 dark:text-gray-400 space-y-1">
                                <li>{{ __('Auto-sync subscribers from Sheets to a list') }}</li>
                                <li>{{ __('Import contacts with field mapping and tags') }}</li>
                                <li>{{ __('Export campaigns, metrics, clicks, and activity to Sheets') }}</li>
                            </ul>
                        </div>

                        <div class="mt-4 flex items-center justify-between gap-3">
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                @if($sheetsIntegration && $sheetsIntegration->google_account_email)
                                    {{ $sheetsIntegration->google_account_email }}
                                @endif
                            </div>

                            @if($sheetsIntegration)
                                <form method="POST" action="{{ route('customer.integrations.google.disconnect', ['service' => 'sheets']) }}">
                                    @csrf
                                    <x-button type="submit" variant="secondary" size="sm">{{ __('Disconnect') }}</x-button>
                                </form>
                            @else
                                <x-button
                                    type="button"
                                    variant="primary"
                                    size="sm"
                                    onclick="window.location='{{ route('customer.integrations.google.connect', ['service' => 'sheets']) }}'"
                                >
                                    {{ __('Connect') }}
                                </x-button>
                            @endif
                        </div>
                    </div>

                    <div class="relative rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
                        @if($driveIntegration)
                            <div class="absolute top-3 ltr:right-3 rtl:left-3 rtl:right-auto z-10 h-6 w-6 rounded-full bg-green-500 text-white flex items-center justify-center">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        @endif

                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Google Drive') }}</h4>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Use Drive assets in templates and store exports and backups.') }}</p>
                            </div>
                            @if($driveIntegration)
                                <span class="inline-flex items-center rounded-full bg-green-50 dark:bg-green-900/30 px-2 py-0.5 text-xs font-medium text-green-700 dark:text-green-300">{{ __('Connected') }}</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-800 px-2 py-0.5 text-xs font-medium text-gray-600 dark:text-gray-300">{{ __('Not connected') }}</span>
                            @endif
                        </div>

                        <div class="mt-3">
                            <ul class="text-sm text-gray-500 dark:text-gray-400 space-y-1">
                                <li>{{ __('Pick images from Drive for your email templates') }}</li>
                                <li>{{ __('Save templates to Drive') }}</li>
                                <li>{{ __('Export backups to a Drive folder') }}</li>
                            </ul>
                        </div>

                        <div class="mt-4 flex items-center justify-between gap-3">
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                @if($driveIntegration && $driveIntegration->google_account_email)
                                    {{ $driveIntegration->google_account_email }}
                                @endif
                            </div>

                            @if($driveIntegration)
                                <form method="POST" action="{{ route('customer.integrations.google.disconnect', ['service' => 'drive']) }}">
                                    @csrf
                                    <x-button type="submit" variant="secondary" size="sm">{{ __('Disconnect') }}</x-button>
                                </form>
                            @else
                                <x-button
                                    type="button"
                                    variant="primary"
                                    size="sm"
                                    onclick="window.location='{{ route('customer.integrations.google.connect', ['service' => 'drive']) }}'"
                                >
                                    {{ __('Connect') }}
                                </x-button>
                            @endif
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
    @endif

    <div x-show="tab === 'wordpress'" class="space-y-4">
        <x-card>
            <div class="flex items-start justify-between gap-4">
                <div>
                    @php
                        $defaultAppName = \App\Models\Setting::get('app_name', config('app.name', 'MailPurse'));
                        $wordpressPluginName = $wordpressCopy['plugin_name'] ?? ($defaultAppName . ' Integration');
                        $wordpressMenuLabel = $wordpressCopy['menu_label'] ?? $defaultAppName;
                        $wordpressAppLabel = $wordpressCopy['app_label'] ?? $defaultAppName;
                    @endphp
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Wordpress') }}</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Connect WordPress and WooCommerce events to :app automations.', ['app' => $wordpressAppLabel]) }}</p>
                </div>
                <div class="shrink-0">
                    <x-button href="{{ route('customer.integrations.wordpress.plugin') }}" variant="secondary" size="sm">
                        {{ __('Download Plugin') }}
                    </x-button>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('How to install') }}</h4>
                    <ol class="mt-2 text-sm text-gray-500 dark:text-gray-400 space-y-1 list-decimal list-inside">
                        <li>{{ __('Download the plugin zip from above.') }}</li>
                        <li>{{ __('In WordPress: Plugins → Add New → Upload Plugin → choose the zip → Install → Activate.') }}</li>
                        <li>{{ __('In WordPress: Settings → :label.', ['label' => $wordpressMenuLabel]) }}</li>
                    </ol>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ __('Current plugin package: :name', ['name' => $wordpressPluginName]) }}</p>
                </div>

                <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('API Key') }}</h4>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Create a Customer API Key, then paste it into the plugin as the API Key.') }}
                        <a href="{{ route('customer.api.index') }}" class="text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">{{ __('Go to API Keys') }}</a>
                    </p>
                </div>
            </div>

            <div class="mt-4 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('How it works') }}</h4>
                <ul class="mt-2 text-sm text-gray-500 dark:text-gray-400 space-y-1">
                    <li>{{ __('The plugin uses Base URL + API Key (Bearer token) to communicate with :app.', ['app' => $wordpressAppLabel]) }}</li>
                    <li>{{ __('When you click “Test Connection”, it fetches a signing secret and stores it in WordPress for request signing.') }}</li>
                    <li>{{ __('Events like wp_* and woo_* are sent to :app and can trigger automations.', ['app' => $wordpressAppLabel]) }}</li>
                </ul>
            </div>
        </x-card>
    </div>
</div>
@endsection
