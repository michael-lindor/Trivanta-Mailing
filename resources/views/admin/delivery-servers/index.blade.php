@extends('layouts.admin')

@section('title', __('Delivery Servers'))
@section('page-title', __('Delivery Servers'))

@section('content')
<div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex flex-col gap-4 mb-6 lg:flex-row lg:items-center lg:justify-between">
        <div class="w-full lg:flex-1 lg:max-w-lg">
            <form method="GET" action="{{ route('admin.delivery-servers.index') }}" class="flex flex-col gap-2 lg:flex-row">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="{{ __('Search servers...') }}"
                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                >
                <select
                    name="type"
                    class="w-full lg:w-auto rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                >
                    <option value="">{{ __('All Types') }}</option>
                    <option value="smtp" {{ request('type') === 'smtp' ? 'selected' : '' }}>{{ __('SMTP') }}</option>
                    <option value="sendmail" {{ request('type') === 'sendmail' ? 'selected' : '' }}>{{ __('Sendmail') }}</option>
                    <option value="zeptomail" {{ request('type') === 'zeptomail' ? 'selected' : '' }}>{{ __('ZeptoMail') }}</option>
                    <option value="amazon-ses" {{ request('type') === 'amazon-ses' ? 'selected' : '' }}>{{ __('Amazon SES') }}</option>
                    <option value="mailgun" {{ request('type') === 'mailgun' ? 'selected' : '' }}>{{ __('Mailgun') }}</option>
                    <option value="sendgrid" {{ request('type') === 'sendgrid' ? 'selected' : '' }}>{{ __('SendGrid') }}</option>
                    <option value="postmark" {{ request('type') === 'postmark' ? 'selected' : '' }}>{{ __('Postmark') }}</option>
                    <option value="sparkpost" {{ request('type') === 'sparkpost' ? 'selected' : '' }}>{{ __('SparkPost') }}</option>
                </select>
                <select
                    name="status"
                    class="w-full lg:w-auto rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                >
                    <option value="">{{ __('All Statuses') }}</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                </select>
                <x-button type="submit" variant="primary" class="w-full lg:w-auto">{{ __('Filter') }}</x-button>
            </form>
        </div>
        <x-button type="button" variant="primary" class="w-full lg:w-auto" @click="$dispatch('open-modal', 'admin-delivery-server-modal')">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            {{ __('Add Server') }}
        </x-button>
    </div>

    <x-modal name="admin-delivery-server-modal" maxWidth="4xl">
        <div class="p-0">
            <div class="px-8 py-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ __('Add Delivery Server') }}</h2>
                    </div>
                </div>
            </div>

            <form id="admin-delivery-server-form" method="POST" action="{{ route('admin.delivery-servers.store') }}" class="space-y-6">
                @csrf

                <div class="px-8 pt-8 space-y-8">
                    <div>
                        <div class="flex items-center gap-3 mb-5">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">1. {{ __('Standard SMTP') }}</h3>
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-700 text-sm font-semibold dark:bg-gray-700 dark:text-gray-200">3</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <label class="admin-delivery-provider-option group relative flex items-center gap-4 rounded-2xl border border-gray-200 bg-white px-4 py-4 cursor-pointer transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-500 hover:shadow-md dark:border-gray-700 dark:bg-gray-800" data-type="gmail" data-flow="smtp" data-provider-name="Gmail">
                                <input type="radio" name="provider_type" value="gmail" class="sr-only">
                                <span class="admin-selected-check pointer-events-none absolute right-4 top-4 hidden h-5 w-5 rounded-full bg-blue-600 text-white items-center justify-center">
                                    <x-lucide name="check" class="h-3.5 w-3.5" />
                                </span>
                                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-red-50 text-red-600 ring-1 ring-red-100 dark:bg-red-900/20 dark:text-red-300 dark:ring-red-900/40">
                                    <x-lucide name="mail" class="h-5 w-5" />
                                </div>
                                <div>
                                    <div class="text-lg font-medium text-gray-900 dark:text-gray-100">Gmail</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('App password SMTP') }}</div>
                                </div>
                            </label>
                            <label class="admin-delivery-provider-option group relative flex items-center gap-4 rounded-2xl border border-gray-200 bg-white px-4 py-4 cursor-pointer transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-500 hover:shadow-md dark:border-gray-700 dark:bg-gray-800" data-type="outlook" data-flow="smtp" data-provider-name="Outlook">
                                <input type="radio" name="provider_type" value="outlook" class="sr-only">
                                <span class="admin-selected-check pointer-events-none absolute right-4 top-4 hidden h-5 w-5 rounded-full bg-blue-600 text-white items-center justify-center">
                                    <x-lucide name="check" class="h-3.5 w-3.5" />
                                </span>
                                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600 ring-1 ring-blue-100 dark:bg-blue-900/20 dark:text-blue-300 dark:ring-blue-900/40">
                                    <x-lucide name="mail" class="h-5 w-5" />
                                </div>
                                <div>
                                    <div class="text-lg font-medium text-gray-900 dark:text-gray-100">Outlook</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Microsoft SMTP') }}</div>
                                </div>
                            </label>
                            <label class="admin-delivery-provider-option group relative flex items-center gap-4 rounded-2xl border border-gray-200 bg-white px-4 py-4 cursor-pointer transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-500 hover:shadow-md dark:border-gray-700 dark:bg-gray-800" data-type="smtp" data-flow="smtp" data-provider-name="Other SMTP">
                                <input type="radio" name="provider_type" value="smtp" class="sr-only">
                                <span class="admin-selected-check pointer-events-none absolute right-4 top-4 hidden h-5 w-5 rounded-full bg-blue-600 text-white items-center justify-center">
                                    <x-lucide name="check" class="h-3.5 w-3.5" />
                                </span>
                                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-50 text-slate-600 ring-1 ring-slate-100 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700">
                                    <x-lucide name="server" class="h-5 w-5" />
                                </div>
                                <div>
                                    <div class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Other SMTP') }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Custom mail server') }}</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center gap-3 mb-5">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">2. {{ __('API Integrations') }}</h3>
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-700 text-sm font-semibold dark:bg-gray-700 dark:text-gray-200">6</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <label class="admin-delivery-provider-option group relative flex items-center gap-4 rounded-2xl border border-gray-200 bg-white px-4 py-4 cursor-pointer transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-500 hover:shadow-md dark:border-gray-700 dark:bg-gray-800" data-type="mailgun" data-flow="api" data-provider-name="Mailgun">
                                <input type="radio" name="provider_type" value="mailgun" class="sr-only">
                                <span class="admin-selected-check pointer-events-none absolute right-4 top-4 hidden h-5 w-5 rounded-full bg-blue-600 text-white items-center justify-center">
                                    <x-lucide name="check" class="h-3.5 w-3.5" />
                                </span>
                                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-sky-50 text-sky-600 ring-1 ring-sky-100 dark:bg-sky-900/20 dark:text-sky-300 dark:ring-sky-900/40">
                                    <x-lucide name="send" class="h-5 w-5" />
                                </div>
                                <div>
                                    <div class="text-lg font-medium text-gray-900 dark:text-gray-100">Mailgun</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Domain + API key') }}</div>
                                </div>
                            </label>
                            <label class="admin-delivery-provider-option group relative flex items-center gap-4 rounded-2xl border border-gray-200 bg-white px-4 py-4 cursor-pointer transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-500 hover:shadow-md dark:border-gray-700 dark:bg-gray-800" data-type="sendgrid" data-flow="api" data-provider-name="SendGrid">
                                <input type="radio" name="provider_type" value="sendgrid" class="sr-only">
                                <span class="admin-selected-check pointer-events-none absolute right-4 top-4 hidden h-5 w-5 rounded-full bg-blue-600 text-white items-center justify-center">
                                    <x-lucide name="check" class="h-3.5 w-3.5" />
                                </span>
                                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600 ring-1 ring-cyan-100 dark:bg-cyan-900/20 dark:text-cyan-300 dark:ring-cyan-900/40">
                                    <x-lucide name="send" class="h-5 w-5" />
                                </div>
                                <div>
                                    <div class="text-lg font-medium text-gray-900 dark:text-gray-100">SendGrid</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Transactional API') }}</div>
                                </div>
                            </label>
                            <label class="admin-delivery-provider-option group relative flex items-center gap-4 rounded-2xl border border-gray-200 bg-white px-4 py-4 cursor-pointer transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-500 hover:shadow-md dark:border-gray-700 dark:bg-gray-800" data-type="postmark" data-flow="api" data-provider-name="Postmark">
                                <input type="radio" name="provider_type" value="postmark" class="sr-only">
                                <span class="admin-selected-check pointer-events-none absolute right-4 top-4 hidden h-5 w-5 rounded-full bg-blue-600 text-white items-center justify-center">
                                    <x-lucide name="check" class="h-3.5 w-3.5" />
                                </span>
                                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600 ring-1 ring-amber-100 dark:bg-amber-900/20 dark:text-amber-300 dark:ring-amber-900/40">
                                    <x-lucide name="mail-check" class="h-5 w-5" />
                                </div>
                                <div>
                                    <div class="text-lg font-medium text-gray-900 dark:text-gray-100">Postmark</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Server token') }}</div>
                                </div>
                            </label>
                            <label class="admin-delivery-provider-option group relative flex items-center gap-4 rounded-2xl border border-gray-200 bg-white px-4 py-4 cursor-pointer transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-500 hover:shadow-md dark:border-gray-700 dark:bg-gray-800" data-type="sparkpost" data-flow="api" data-provider-name="SparkPost">
                                <input type="radio" name="provider_type" value="sparkpost" class="sr-only">
                                <span class="admin-selected-check pointer-events-none absolute right-4 top-4 hidden h-5 w-5 rounded-full bg-blue-600 text-white items-center justify-center">
                                    <x-lucide name="check" class="h-3.5 w-3.5" />
                                </span>
                                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-violet-50 text-violet-600 ring-1 ring-violet-100 dark:bg-violet-900/20 dark:text-violet-300 dark:ring-violet-900/40">
                                    <x-lucide name="zap" class="h-5 w-5" />
                                </div>
                                <div>
                                    <div class="text-lg font-medium text-gray-900 dark:text-gray-100">SparkPost</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Events + sending API') }}</div>
                                </div>
                            </label>
                            <label class="admin-delivery-provider-option group relative flex items-center gap-4 rounded-2xl border border-gray-200 bg-white px-4 py-4 cursor-pointer transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-500 hover:shadow-md dark:border-gray-700 dark:bg-gray-800" data-type="amazon-ses" data-flow="api" data-provider-name="Amazon SES">
                                <input type="radio" name="provider_type" value="amazon-ses" class="sr-only">
                                <span class="admin-selected-check pointer-events-none absolute right-4 top-4 hidden h-5 w-5 rounded-full bg-blue-600 text-white items-center justify-center">
                                    <x-lucide name="check" class="h-3.5 w-3.5" />
                                </span>
                                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600 ring-1 ring-blue-100 dark:bg-blue-900/20 dark:text-blue-300 dark:ring-blue-900/40">
                                    <x-lucide name="cloud" class="h-5 w-5" />
                                </div>
                                <div>
                                    <div class="text-lg font-medium text-gray-900 dark:text-gray-100">Amazon SES</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('AWS credentials') }}</div>
                                </div>
                            </label>
                            <label class="admin-delivery-provider-option group relative flex items-center gap-4 rounded-2xl border border-gray-200 bg-white px-4 py-4 cursor-pointer transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-500 hover:shadow-md dark:border-gray-700 dark:bg-gray-800" data-type="zeptomail-api" data-flow="api" data-provider-name="ZeptoMail API">
                                <input type="radio" name="provider_type" value="zeptomail-api" class="sr-only">
                                <span class="admin-selected-check pointer-events-none absolute right-4 top-4 hidden h-5 w-5 rounded-full bg-blue-600 text-white items-center justify-center">
                                    <x-lucide name="check" class="h-3.5 w-3.5" />
                                </span>
                                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-fuchsia-50 text-fuchsia-600 ring-1 ring-fuchsia-100 dark:bg-fuchsia-900/20 dark:text-fuchsia-300 dark:ring-fuchsia-900/40">
                                    <x-lucide name="mail-plus" class="h-5 w-5" />
                                </div>
                                <div>
                                    <div class="text-lg font-medium text-gray-900 dark:text-gray-100">ZeptoMail API</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Send mail token') }}</div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <div id="admin-config-section" class="hidden px-8 pb-8 border-t border-gray-200 pt-8 dark:border-gray-700">
                    <div class="flex items-start gap-4 mb-8">
                        <div id="admin-provider-icon" class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm ring-1 ring-blue-200/60 dark:ring-blue-400/20">
                            <x-lucide name="send" class="h-6 w-6" />
                        </div>
                        <div>
                            <h3 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ __('Configure') }} <span id="admin-selected-provider-name"></span> {{ __('Server') }}</h3>
                            <p class="mt-1 text-base text-gray-400" id="admin-selected-provider-description"></p>
                        </div>
                    </div>

                    <input type="hidden" name="type" id="admin-selected-type">
                    <input type="hidden" name="flow" id="admin-selected-flow">

                    <div id="admin-gmail-help" class="hidden mb-6 rounded-2xl border border-blue-200 bg-blue-50 px-5 py-4 text-sm text-blue-900 dark:border-blue-900/40 dark:bg-blue-900/10 dark:text-blue-100">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 flex h-8 w-8 items-center justify-center rounded-lg bg-white text-blue-600 shadow-sm dark:bg-blue-950/60 dark:text-blue-300">
                                <x-lucide name="info" class="h-4 w-4" />
                            </div>
                            <div class="space-y-2">
                                <div class="font-semibold">{{ __('Gmail setup instructions') }}</div>
                                <ol class="list-decimal space-y-1 pl-5">
                                    <li>{{ __('Sign in to your Google account and enable 2-Step Verification.') }}</li>
                                    <li>{{ __('Open Google Account settings and go to Security.') }}</li>
                                    <li>{{ __('Under App passwords, create a new app password for Mail.') }}</li>
                                    <li>{{ __('Use your full Gmail address as Username.') }}</li>
                                    <li>{{ __('Paste the generated app password into the Password field here.') }}</li>
                                </ol>
                                <a href="https://support.google.com/accounts/answer/185833" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 font-medium text-blue-700 underline dark:text-blue-300">
                                    <span>{{ __('Open Google App Password instructions') }}</span>
                                    <x-lucide name="external-link" class="h-4 w-4" />
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2">
                            <label for="admin-server-name" class="block text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('Server Name') }} <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="admin-server-name" class="mt-2 block w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm" required>
                            <p class="mt-2 text-sm text-gray-400">{{ __('A recognizable name for this delivery server in your system.') }}</p>
                        </div>

                        <div id="admin-mailgun-domain-wrap" class="hidden">
                            <label for="admin-mailgun-domain" class="block text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('Sending Domain') }} <span class="text-red-500">*</span></label>
                            <input type="text" name="settings[domain]" id="admin-mailgun-domain" placeholder="e.g. mg.yourdomain.com" class="mt-2 block w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        </div>

                        <div id="admin-mailgun-secret-wrap" class="hidden">
                            <label for="admin-mailgun-secret" class="block text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('API Key') }} <span class="text-red-500">*</span></label>
                            <input type="password" name="settings[secret]" id="admin-mailgun-secret" class="mt-2 block w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        </div>

                        <div id="admin-sendgrid-wrap" class="hidden md:col-span-2">
                            <label for="admin-sendgrid-api-key" class="block text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('API Key') }} <span class="text-red-500">*</span></label>
                            <input type="password" name="settings[api_key]" id="admin-sendgrid-api-key" class="mt-2 block w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        </div>

                        <div id="admin-postmark-wrap" class="hidden md:col-span-2">
                            <label for="admin-postmark-token" class="block text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('API Token') }} <span class="text-red-500">*</span></label>
                            <input type="password" name="settings[token]" id="admin-postmark-token" class="mt-2 block w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        </div>

                        <div id="admin-sparkpost-wrap" class="hidden md:col-span-2">
                            <label for="admin-sparkpost-secret" class="block text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('API Key') }} <span class="text-red-500">*</span></label>
                            <input type="password" name="settings[secret]" id="admin-sparkpost-secret" class="mt-2 block w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        </div>

                        <div id="admin-smtp-hostname-wrap" class="hidden">
                            <label for="admin-smtp-hostname" class="block text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('Hostname') }} <span class="text-red-500">*</span></label>
                            <input type="text" name="hostname" id="admin-smtp-hostname" class="mt-2 block w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        </div>

                        <div id="admin-smtp-port-wrap" class="hidden">
                            <label for="admin-smtp-port" class="block text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('Port') }} <span class="text-red-500">*</span></label>
                            <input type="number" name="port" id="admin-smtp-port" min="1" max="65535" value="587" class="mt-2 block w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        </div>

                        <div id="admin-smtp-username-wrap" class="hidden">
                            <label for="admin-smtp-username" class="block text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('Username') }} <span class="text-red-500">*</span></label>
                            <input type="text" name="username" id="admin-smtp-username" class="mt-2 block w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        </div>

                        <div id="admin-smtp-password-wrap" class="hidden">
                            <label for="admin-smtp-password" class="block text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('Password') }} <span class="text-red-500">*</span></label>
                            <input type="password" name="password" id="admin-smtp-password" class="mt-2 block w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        </div>

                        <div id="admin-smtp-encryption-wrap" class="hidden">
                            <label for="admin-smtp-encryption" class="block text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('Encryption') }}</label>
                            <select name="encryption" id="admin-smtp-encryption" class="mt-2 block w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                <option value="tls">TLS</option>
                                <option value="ssl">SSL</option>
                                <option value="none">None</option>
                            </select>
                        </div>

                        <div id="admin-ses-key-wrap" class="hidden">
                            <label for="admin-ses-key" class="block text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('Access Key ID') }} <span class="text-red-500">*</span></label>
                            <input type="text" name="settings[key]" id="admin-ses-key" class="mt-2 block w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        </div>

                        <div id="admin-ses-secret-wrap" class="hidden">
                            <label for="admin-ses-secret" class="block text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('Secret Key') }} <span class="text-red-500">*</span></label>
                            <input type="password" name="settings[secret]" id="admin-ses-secret" class="mt-2 block w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        </div>

                        <div id="admin-ses-region-wrap" class="hidden">
                            <label for="admin-ses-region" class="block text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('Region') }} <span class="text-red-500">*</span></label>
                            <select name="settings[region]" id="admin-ses-region" class="mt-2 block w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                <option value="us-east-1">US (Default)</option>
                                <option value="us-west-2">US West</option>
                                <option value="eu-west-1">EU West</option>
                                <option value="ap-southeast-1">Asia Pacific</option>
                            </select>
                        </div>

                        <div id="admin-from-email-wrap" class="hidden">
                            <label for="admin-from-email" class="block text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('From Email') }} <span class="text-red-500">*</span></label>
                            <input type="email" name="from_email" id="admin-from-email" placeholder="hello@yourdomain.com" class="mt-2 block w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        </div>

                        <div id="admin-zepto-token-wrap" class="hidden md:col-span-2">
                            <label for="admin-zepto-token" class="block text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('Send Mail Token') }} <span class="text-red-500">*</span></label>
                            <input type="password" name="settings[send_mail_token]" id="admin-zepto-token" class="mt-2 block w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        </div>

                        <div>
                            <label for="admin-status" class="block text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('Status') }}</label>
                            <select name="status" id="admin-status" class="mt-2 block w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                <option value="pending">{{ __('Pending') }}</option>
                                <option value="active">{{ __('Active') }}</option>
                                <option value="inactive">{{ __('Inactive') }}</option>
                            </select>
                        </div>

                        <div>
                            <label for="admin-from-name" class="block text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('From Name') }}</label>
                            <input type="text" name="from_name" id="admin-from-name" class="mt-2 block w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        </div>
                    </div>
                </div>

                <div class="px-8 py-4 flex items-center justify-end gap-3 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" class="px-6 py-3 rounded-xl border border-gray-300 text-gray-800 dark:text-gray-200 dark:border-gray-600" @click="$dispatch('close-modal', 'admin-delivery-server-modal')">{{ __('Cancel') }}</button>
                    <button type="submit" id="admin-save-server-btn" class="px-6 py-3 rounded-xl bg-blue-600 text-white font-medium disabled:opacity-50 disabled:cursor-not-allowed" disabled>{{ __('Save Server') }}</button>
                </div>
            </form>
        </div>
    </x-modal>

    <x-modal name="pro-feature-extended-mailbox-providers-admin" maxWidth="md">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Extended License Required</h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                Gmail and Outlook mailbox providers are available only with the Extended license.
            </p>
            <div class="mt-6 flex justify-end">
                <x-button type="button" variant="secondary" @click="$dispatch('close-modal', 'pro-feature-extended-mailbox-providers-admin')">Close</x-button>
            </div>
        </div>
    </x-modal>

    <!-- Servers Table -->
    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Name') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Type') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Hostname') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Quota') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @if(isset($systemSmtpServer) && $systemSmtpServer)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-2 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $systemSmtpServer->name }}</div>
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">{{ __('System') }}</span>
                                </div>
                                @if(!empty($systemSmtpServer->from_email))
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $systemSmtpServer->from_email }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-2 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                    {{ strtoupper(str_replace('-', ' ', $systemSmtpServer->type)) }}
                                </span>
                            </td>
                            <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ !empty($systemSmtpServer->hostname) ? $systemSmtpServer->hostname . ':' . ($systemSmtpServer->port ?? 587) : __('N/A') }}
                            </td>
                            <td class="px-6 py-2 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    {{ __('Active') }}
                                </span>
                                <span class="ml-2 px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">{{ __('Locked') }}</span>
                            </td>
                            <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ __('Unlimited') }}
                            </td>
                            <td class="px-6 py-2 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    @if(!empty($systemSmtpServer->id))
                                        <x-button href="{{ route('admin.delivery-servers.edit', $systemSmtpServer->id) }}" variant="table" size="action" :pill="true" class="p-2" title="{{ __('Edit') }}" aria-label="{{ __('Edit') }}"><x-lucide name="pencil" class="h-4 w-4" /><span class="sr-only">{{ __('Edit') }}</span></x-button>
                                    @endif
                                    <x-button href="{{ route('admin.delivery-servers.test') }}" variant="table" size="action" :pill="true">{{ __('Test') }}</x-button>
                                </div>
                            </td>
                        </tr>
                    @endif

                    @forelse($deliveryServers as $server)
                        @if(isset($systemSmtpServer) && $systemSmtpServer && !empty($systemSmtpServer->id) && (int) $server->id === (int) $systemSmtpServer->id)
                            @continue
                        @endif
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-2 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $server->name }}</div>
                                    @if($server->is_primary)
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">{{ __('Primary') }}</span>
                                    @endif
                                </div>
                                @if($server->from_email)
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $server->from_email }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-2 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                    {{ strtoupper(str_replace('-', ' ', $server->type)) }}
                                </span>
                            </td>
                            <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $server->hostname ? $server->hostname . ':' . $server->port : __('N/A') }}
                            </td>
                            <td class="px-6 py-2 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $server->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : ($server->status === 'inactive' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200') }}">
                                    {{ __(ucfirst($server->status)) }}
                                </span>
                                @if($server->locked)
                                    <span class="ml-2 px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">{{ __('Locked') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                @if($server->monthly_quota > 0)
                                    {{ __(':count/month', ['count' => number_format($server->monthly_quota)]) }}
                                @else
                                    {{ __('Unlimited') }}
                                @endif
                            </td>
                            <td class="px-6 py-2 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <x-button href="{{ route('admin.delivery-servers.show', $server) }}" variant="table" size="action" :pill="true" class="p-2" title="{{ __('View') }}" aria-label="{{ __('View') }}"><x-lucide name="eye" class="h-4 w-4" /><span class="sr-only">{{ __('View') }}</span></x-button>
                                    @admincan('admin.delivery_servers.make_primary')
                                        @if(!$server->is_primary)
                                            <form method="POST" action="{{ route('admin.delivery-servers.make-primary', $server) }}" class="inline">
                                                @csrf
                                                <x-button type="submit" variant="table-info" size="action" :pill="true">{{ __('Make Primary') }}</x-button>
                                            </form>
                                        @endif
                                    @endadmincan
                                    <x-button href="{{ route('admin.delivery-servers.edit', $server) }}" variant="table" size="action" :pill="true" class="p-2" title="{{ __('Edit') }}" aria-label="{{ __('Edit') }}"><x-lucide name="pencil" class="h-4 w-4" /><span class="sr-only">{{ __('Edit') }}</span></x-button>
                                    <form method="POST" action="{{ route('admin.delivery-servers.destroy', $server) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure?') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <x-button type="submit" variant="table-danger" size="action" :pill="true" class="p-2" title="{{ __('Delete') }}" aria-label="{{ __('Delete') }}"><x-lucide name="trash-2" class="h-4 w-4" /><span class="sr-only">{{ __('Delete') }}</span></x-button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                {{ __('No delivery servers found.') }}
                                <a href="{{ route('admin.delivery-servers.create') }}" class="text-primary-600">{{ __('Add your first server') }}</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($deliveryServers->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $deliveryServers->links() }}
            </div>
        @endif
    </x-card>
</div>

@push('scripts')
<script>
const initAdminDeliveryServerIndex = () => {
    const hasExtendedLicense = @json((bool) ($hasExtendedLicense ?? false));
    const form = document.getElementById('admin-delivery-server-form');
    if (!form) return;

    const optionSelector = '.admin-delivery-provider-option';
    const options = document.querySelectorAll(optionSelector);
    const configSection = document.getElementById('admin-config-section');
    const selectedType = document.getElementById('admin-selected-type');
    const selectedFlow = document.getElementById('admin-selected-flow');
    const selectedName = document.getElementById('admin-selected-provider-name');
    const selectedDescription = document.getElementById('admin-selected-provider-description');
    const saveButton = document.getElementById('admin-save-server-btn');
    const providerIcon = document.getElementById('admin-provider-icon');
    const gmailHelp = document.getElementById('admin-gmail-help');

    const fieldIds = [
        'admin-mailgun-domain-wrap',
        'admin-mailgun-secret-wrap',
        'admin-sendgrid-wrap',
        'admin-postmark-wrap',
        'admin-sparkpost-wrap',
        'admin-smtp-hostname-wrap',
        'admin-smtp-port-wrap',
        'admin-smtp-username-wrap',
        'admin-smtp-password-wrap',
        'admin-smtp-encryption-wrap',
        'admin-ses-key-wrap',
        'admin-ses-secret-wrap',
        'admin-ses-region-wrap',
        'admin-from-email-wrap',
        'admin-zepto-token-wrap'
    ];

    const descriptions = {
        gmail: 'Enter your SMTP credentials to connect your Gmail account.',
        outlook: 'Enter your SMTP credentials to connect your Outlook account.',
        smtp: 'Enter your SMTP server credentials.',
        mailgun: 'Enter your API credentials to connect your Mailgun account.',
        sendgrid: 'Enter your API credentials to connect your SendGrid account.',
        postmark: 'Enter your API credentials to connect your Postmark account.',
        sparkpost: 'Enter your API credentials to connect your SparkPost account.',
        'amazon-ses': 'Enter your AWS SES credentials to connect your Amazon SES account.',
        'zeptomail-api': 'Enter your API credentials to connect your ZeptoMail account.'
    };

    const iconMap = {
        gmail: {
            classes: 'bg-red-50 text-red-600 ring-1 ring-red-100 dark:bg-red-900/20 dark:text-red-300 dark:ring-red-900/40',
            svg: '<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>'
        },
        outlook: {
            classes: 'bg-blue-50 text-blue-600 ring-1 ring-blue-100 dark:bg-blue-900/20 dark:text-blue-300 dark:ring-blue-900/40',
            svg: '<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>'
        },
        smtp: {
            classes: 'bg-slate-50 text-slate-600 ring-1 ring-slate-100 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700',
            svg: '<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 7h14M5 17h14" /></svg>'
        },
        mailgun: {
            classes: 'bg-sky-50 text-sky-600 ring-1 ring-sky-100 dark:bg-sky-900/20 dark:text-sky-300 dark:ring-sky-900/40',
            svg: '<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M22 2L11 13" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M22 2L15 22l-4-9-9-4 20-7z" /></svg>'
        },
        sendgrid: {
            classes: 'bg-cyan-50 text-cyan-600 ring-1 ring-cyan-100 dark:bg-cyan-900/20 dark:text-cyan-300 dark:ring-cyan-900/40',
            svg: '<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M22 2L11 13" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M22 2L15 22l-4-9-9-4 20-7z" /></svg>'
        },
        postmark: {
            classes: 'bg-amber-50 text-amber-600 ring-1 ring-amber-100 dark:bg-amber-900/20 dark:text-amber-300 dark:ring-amber-900/40',
            svg: '<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>'
        },
        sparkpost: {
            classes: 'bg-violet-50 text-violet-600 ring-1 ring-violet-100 dark:bg-violet-900/20 dark:text-violet-300 dark:ring-violet-900/40',
            svg: '<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>'
        },
        'amazon-ses': {
            classes: 'bg-blue-50 text-blue-600 ring-1 ring-blue-100 dark:bg-blue-900/20 dark:text-blue-300 dark:ring-blue-900/40',
            svg: '<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 014-4 5 5 0 019.7-1.3A4.5 4.5 0 1118 18H7a4 4 0 01-4-3z" /></svg>'
        },
        'zeptomail-api': {
            classes: 'bg-fuchsia-50 text-fuchsia-600 ring-1 ring-fuchsia-100 dark:bg-fuchsia-900/20 dark:text-fuchsia-300 dark:ring-fuchsia-900/40',
            svg: '<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 5v6m3-3h-6" /></svg>'
        }
    };

    const defaults = {
        gmail: { hostname: 'smtp.gmail.com', port: '587', encryption: 'tls' },
        outlook: { hostname: 'smtp.office365.com', port: '587', encryption: 'tls' },
        smtp: { hostname: '', port: '587', encryption: 'tls' }
    };

    function hideAllFieldGroups() {
        fieldIds.forEach((id) => {
            const element = document.getElementById(id);
            if (element) element.classList.add('hidden');
        });
    }

    function markSelected(selectedOption) {
        options.forEach((option) => {
            option.classList.remove('border-blue-600', 'ring-2', 'ring-blue-100', 'shadow-sm');
            option.classList.add('border-gray-200', 'dark:border-gray-700');
            option.querySelector('.admin-selected-check')?.classList.add('hidden');
            option.querySelector('.admin-selected-check')?.classList.remove('flex');
        });

        selectedOption.classList.remove('border-gray-200', 'dark:border-gray-700');
        selectedOption.classList.add('border-blue-600', 'ring-2', 'ring-blue-100', 'shadow-sm');
        selectedOption.querySelector('.admin-selected-check')?.classList.remove('hidden');
        selectedOption.querySelector('.admin-selected-check')?.classList.add('flex');
    }

    function showFieldsFor(type, flow) {
        hideAllFieldGroups();
        if (gmailHelp) {
            gmailHelp.classList.toggle('hidden', type !== 'gmail');
        }

        if (flow === 'smtp') {
            ['admin-smtp-hostname-wrap', 'admin-smtp-port-wrap', 'admin-smtp-username-wrap', 'admin-smtp-password-wrap', 'admin-smtp-encryption-wrap', 'admin-from-email-wrap'].forEach((id) => {
                document.getElementById(id)?.classList.remove('hidden');
            });

            const config = defaults[type] || defaults.smtp;
            document.getElementById('admin-smtp-hostname').value = config.hostname;
            document.getElementById('admin-smtp-port').value = config.port;
            document.getElementById('admin-smtp-encryption').value = config.encryption;
            return;
        }

        if (type === 'mailgun') {
            document.getElementById('admin-mailgun-domain-wrap')?.classList.remove('hidden');
            document.getElementById('admin-mailgun-secret-wrap')?.classList.remove('hidden');
            document.getElementById('admin-from-email-wrap')?.classList.remove('hidden');
        }

        if (type === 'sendgrid') {
            document.getElementById('admin-sendgrid-wrap')?.classList.remove('hidden');
            document.getElementById('admin-from-email-wrap')?.classList.remove('hidden');
        }

        if (type === 'postmark') {
            document.getElementById('admin-postmark-wrap')?.classList.remove('hidden');
            document.getElementById('admin-from-email-wrap')?.classList.remove('hidden');
        }

        if (type === 'sparkpost') {
            document.getElementById('admin-sparkpost-wrap')?.classList.remove('hidden');
            document.getElementById('admin-from-email-wrap')?.classList.remove('hidden');
        }

        if (type === 'amazon-ses') {
            ['admin-ses-key-wrap', 'admin-ses-secret-wrap', 'admin-ses-region-wrap', 'admin-from-email-wrap'].forEach((id) => {
                document.getElementById(id)?.classList.remove('hidden');
            });
        }

        if (type === 'zeptomail-api') {
            document.getElementById('admin-zepto-token-wrap')?.classList.remove('hidden');
            document.getElementById('admin-from-email-wrap')?.classList.remove('hidden');
        }
    }

    function scrollConfigSectionIntoView() {
        requestAnimationFrame(() => {
            const modalViewport = configSection.closest('.relative.mx-auto')?.parentElement?.parentElement;

            if (modalViewport instanceof HTMLElement) {
                const viewportRect = modalViewport.getBoundingClientRect();
                const sectionRect = configSection.getBoundingClientRect();
                const nextTop = sectionRect.top - viewportRect.top + modalViewport.scrollTop - 24;

                modalViewport.scrollTo({
                    top: Math.max(nextTop, 0),
                    behavior: 'smooth',
                });

                return;
            }

            configSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }

    options.forEach((option) => {
        if (option.dataset.deliveryProviderBound === '1') {
            return;
        }

        option.dataset.deliveryProviderBound = '1';

        option.addEventListener('click', function () {
            const radio = this.querySelector('input[type="radio"]');
            const type = this.dataset.type;
            const flow = this.dataset.flow;
            const providerName = this.dataset.providerName;

            if ((type === 'gmail' || type === 'outlook') && !hasExtendedLicense) {
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'pro-feature-extended-mailbox-providers-admin' }));
                return;
            }

            if (radio) radio.checked = true;
            markSelected(this);

            selectedType.value = type;
            selectedFlow.value = flow;
            selectedName.textContent = providerName;
            selectedDescription.textContent = descriptions[type] || '';
            providerIcon.className = 'flex h-12 w-12 items-center justify-center rounded-xl shadow-sm';
            if (iconMap[type]) {
                providerIcon.className += ' ' + iconMap[type].classes;
                providerIcon.innerHTML = iconMap[type].svg;
            }

            showFieldsFor(type, flow);
            configSection.classList.remove('hidden');
            scrollConfigSectionIntoView();
            saveButton.disabled = false;
        });
    });

    hideAllFieldGroups();
};

document.addEventListener('DOMContentLoaded', initAdminDeliveryServerIndex);
document.addEventListener('turbo:load', initAdminDeliveryServerIndex);

if (document.readyState !== 'loading') {
    initAdminDeliveryServerIndex();
}
</script>
@endpush
@endsection

