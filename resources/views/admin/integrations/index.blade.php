@extends('layouts.admin')

@section('title', __('Integrations'))
@section('page-title', __('Integrations'))

@section('content')
<div class="space-y-6" x-data="{ tab: @js($tab), setTab(next) { this.tab = next; const url = new URL(window.location.href); url.searchParams.set('tab', next); window.history.replaceState({}, '', url); } }">
    <div class="border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
        <nav class="-mb-px flex min-w-max space-x-6 sm:space-x-8 px-2 sm:px-0" aria-label="Tabs">
            <a
                href="{{ route('admin.integrations.index', ['tab' => 'google']) }}"
                @click.prevent="setTab('google')"
                class="whitespace-nowrap shrink-0 py-3 sm:py-4 px-2 sm:px-1 font-medium text-sm"
                :class="tab === 'google' ? '!border-primary-500 text-primary-600 dark:text-primary-400 border-b-2' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
            >
                {{ __('Google') }}
            </a>
            <a
                href="{{ route('admin.integrations.index', ['tab' => 'wordpress']) }}"
                @click.prevent="setTab('wordpress')"
                class="whitespace-nowrap shrink-0 py-3 sm:py-4 px-2 sm:px-1 font-medium text-sm"
                :class="tab === 'wordpress' ? '!border-primary-500 text-primary-600 dark:text-primary-400 border-b-2' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
            >
                {{ __('Wordpress') }}
            </a>
        </nav>
    </div>

    <div x-show="tab === 'google'" class="space-y-4">
        <x-card>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Google') }}</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Google integrations are connected per customer account.') }}</p>

            @if($googleOAuthConfigured)
                <div class="mt-4 rounded-lg border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 px-4 py-3">
                    <p class="text-sm font-medium text-green-800 dark:text-green-300">{{ __('Google OAuth is configured') }}</p>
                    <p class="mt-1 text-sm text-green-700 dark:text-green-400">{{ __('Customers can connect Sheets/Drive from Customer → Integrations → Google.') }}</p>
                </div>
            @else
                <div class="mt-4 rounded-lg border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 px-4 py-3">
                    <p class="text-sm font-medium text-amber-800 dark:text-amber-300">{{ __('Google OAuth is not configured') }}</p>
                    <p class="mt-1 text-sm text-amber-700 dark:text-amber-400">{{ __('Set Google Client ID and Client Secret in Auth settings to enable customer connections.') }}</p>
                </div>
            @endif

            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Google Sheets') }}</h4>
                    <ul class="mt-2 text-sm text-gray-500 dark:text-gray-400 space-y-1">
                        <li>{{ __('Import and auto-sync subscribers, and export campaign reports.') }}</li>
                        <li>{{ __('Auto-sync subscribers from Sheets to a list') }}</li>
                        <li>{{ __('Import contacts with field mapping and tags') }}</li>
                        <li>{{ __('Export campaigns and metrics to Sheets') }}</li>
                    </ul>
                    <label class="mt-3 block text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('OAuth Redirect URL') }}</label>
                    <input type="text" readonly value="{{ $googleRedirectSheets }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-200 sm:text-xs" />
                </div>

                <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Google Drive') }}</h4>
                    <ul class="mt-2 text-sm text-gray-500 dark:text-gray-400 space-y-1">
                        <li>{{ __('Use Drive assets in templates and store exports and backups.') }}</li>
                        <li>{{ __('Pick images from Drive for your email templates') }}</li>
                        <li>{{ __('Save templates to Drive') }}</li>
                        <li>{{ __('Export backups to a Drive folder') }}</li>
                    </ul>
                    <label class="mt-3 block text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('OAuth Redirect URL') }}</label>
                    <input type="text" readonly value="{{ $googleRedirectDrive }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-200 sm:text-xs" />
                </div>
            </div>

            <div class="mt-4">
                <a href="{{ route('admin.settings.index', ['category' => 'auth']) }}" class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                    {{ __('Go to Auth settings') }}
                </a>
            </div>
        </x-card>
    </div>

    <div x-show="tab === 'wordpress'" class="space-y-4">
        <x-card>
            @php
                $defaultAppName = \App\Models\Setting::get('app_name', config('app.name', 'MailPurse'));
                $wordpressPluginName = $wordpressCopy['plugin_name'] ?? ($defaultAppName . ' Integration');
                $wordpressMenuLabel = $wordpressCopy['menu_label'] ?? $defaultAppName;
                $wordpressAppLabel = $wordpressCopy['app_label'] ?? $defaultAppName;
            @endphp
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Wordpress') }}</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Connect WordPress and WooCommerce events to :app automations.', ['app' => $wordpressAppLabel]) }}</p>
                </div>
                <div class="shrink-0">
                    <x-button href="{{ route('admin.integrations.wordpress.plugin') }}" variant="secondary" size="sm">
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
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('API Key (Customer token)') }}</h4>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('The plugin authenticates using a Customer API Key (Bearer token). Create it from the customer dashboard: Customer → API → Create API Key.') }}
                    </p>
                </div>
            </div>

            <div class="mt-4 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('How it works') }}</h4>
                <ul class="mt-2 text-sm text-gray-500 dark:text-gray-400 space-y-1">
                    <li>{{ __('WordPress uses your Base URL + Customer API Key to fetch lists and a signing secret.') }}</li>
                    <li>{{ __('When you click “Test Connection” in the plugin, it syncs a signing secret used to sign event requests.') }}</li>
                    <li>{{ __('Events are sent to :app and can trigger automations (events like wp_* and woo_*).', ['app' => $wordpressAppLabel]) }}</li>
                </ul>
            </div>

            <form method="POST" action="{{ route('admin.integrations.wordpress.store') }}" class="mt-4 rounded-xl border border-gray-200 dark:border-gray-700 p-4 space-y-4">
                @csrf

                <div class="flex items-center justify-between gap-4 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('White-label mode') }}</h4>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Hide the default branding in the generated WordPress plugin and replace it with your own brand.') }}</p>
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input type="hidden" name="wordpress_plugin[white_label_enabled]" value="0">
                        <input type="checkbox" name="wordpress_plugin[white_label_enabled]" value="1" {{ !empty($wordpressBranding['white_label_enabled']) ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700">
                        <span>{{ __('Enabled') }}</span>
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Integration API Key') }}</label>
                        <input type="text" name="settings[api_key]" value="{{ old('settings.api_key', $wordpressApiKey ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100" placeholder="{{ __('Optional admin-managed integration key') }}">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Leave unchanged to keep the existing saved key.') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Plugin Name') }}</label>
                        <input type="text" name="wordpress_plugin[plugin_name]" value="{{ old('wordpress_plugin.plugin_name', $wordpressBranding['plugin_name'] ?? ($defaultAppName . ' Integration')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Plugin Slug') }}</label>
                        <input type="text" name="wordpress_plugin[plugin_slug]" value="{{ old('wordpress_plugin.plugin_slug', $wordpressBranding['plugin_slug'] ?? 'mailpurse-integration') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Used for the plugin folder, main PHP file, and ZIP filename.') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('App Label') }}</label>
                        <input type="text" name="wordpress_plugin[app_label]" value="{{ old('wordpress_plugin.app_label', $wordpressBranding['app_label'] ?? $defaultAppName) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Plugin Author') }}</label>
                        <input type="text" name="wordpress_plugin[plugin_author]" value="{{ old('wordpress_plugin.plugin_author', $wordpressBranding['plugin_author'] ?? $defaultAppName) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('WordPress Menu Label') }}</label>
                        <input type="text" name="wordpress_plugin[plugin_menu_label]" value="{{ old('wordpress_plugin.plugin_menu_label', $wordpressBranding['plugin_menu_label'] ?? $defaultAppName) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Settings Page Title') }}</label>
                        <input type="text" name="wordpress_plugin[plugin_settings_title]" value="{{ old('wordpress_plugin.plugin_settings_title', $wordpressBranding['plugin_settings_title'] ?? ($defaultAppName . ' Integration')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Plugin Description') }}</label>
                        <textarea name="wordpress_plugin[plugin_description]" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">{{ old('wordpress_plugin.plugin_description', $wordpressBranding['plugin_description'] ?? ('Send WordPress and WooCommerce events to ' . $defaultAppName . ' Automations.')) }}</textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end">
                    <x-button type="submit" variant="primary">{{ __('Save WordPress Branding') }}</x-button>
                </div>
            </form>
        </x-card>
    </div>
</div>
@endsection
