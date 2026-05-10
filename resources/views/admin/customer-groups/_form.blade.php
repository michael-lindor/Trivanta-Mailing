@php
    $outreachAddonActive = \App\Models\Addon::isInstalled('cold-email-outreach') && \App\Models\Addon::isActive('cold-email-outreach');
    $superScrapeAddonActive = \App\Models\Addon::isActive('super-scrape');
@endphp

<div class="border-b border-gray-200 dark:border-gray-700">
    <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Tabs">
        <button type="button" @click="activeTab = 'messages'" :class="activeTab === 'messages' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-primary-500 dark:!border-primary-400 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-transparent text-gray-500 hover:text-gray-700 hover:!border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
            {{ __('Messages') }}
        </button>
        <button type="button" @click="activeTab = 'email_lists'" :class="activeTab === 'email_lists' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-primary-500 dark:!border-primary-400 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-transparent text-gray-500 hover:text-gray-700 hover:!border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
            {{ __('Email Lists') }}
        </button>
        <button type="button" @click="activeTab = 'campaigns'" :class="activeTab === 'campaigns' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-primary-500 dark:!border-primary-400 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-transparent text-gray-500 hover:text-gray-700 hover:!border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
            {{ __('Campaigns') }}
        </button>
        <button type="button" @click="activeTab = 'autoresponders'" :class="activeTab === 'autoresponders' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-primary-500 dark:!border-primary-400 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-transparent text-gray-500 hover:text-gray-700 hover:!border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
            {{ __('Auto Responders') }}
        </button>
        <button type="button" @click="activeTab = 'automations'" :class="activeTab === 'automations' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-primary-500 dark:!border-primary-400 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-transparent text-gray-500 hover:text-gray-700 hover:!border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
            {{ __('Automations') }}
        </button>
        @if($outreachAddonActive)
            <button type="button" @click="activeTab = 'outreach'" :class="activeTab === 'outreach' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-primary-500 dark:!border-primary-400 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-transparent text-gray-500 hover:text-gray-700 hover:!border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
                {{ __('Outreach') }}
            </button>
        @endif
        @if($superScrapeAddonActive)
            <button type="button" @click="activeTab = 'super_scrape'" :class="activeTab === 'super_scrape' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-primary-500 dark:!border-primary-400 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-transparent text-gray-500 hover:text-gray-700 hover:!border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
                {{ __('Lead Scraper') }}
            </button>
        @endif
        <button type="button" @click="activeTab = 'servers'" :class="activeTab === 'servers' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-primary-500 dark:!border-primary-400 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-transparent text-gray-500 hover:text-gray-700 hover:!border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
            {{ __('Servers') }}
        </button>
        <button type="button" @click="activeTab = 'tracking_domains'" :class="activeTab === 'tracking_domains' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-primary-500 dark:!border-primary-400 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-transparent text-gray-500 hover:text-gray-700 hover:!border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
            {{ __('Tracking Domain') }}
        </button>
        <button type="button" @click="activeTab = 'sending_domains'" :class="activeTab === 'sending_domains' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-primary-500 dark:!border-primary-400 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-transparent text-gray-500 hover:text-gray-700 hover:!border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
            {{ __('Sending Domain') }}
        </button>
        <button type="button" @click="activeTab = 'sending_quota'" :class="activeTab === 'sending_quota' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-primary-500 dark:!border-primary-400 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-transparent text-gray-500 hover:text-gray-700 hover:!border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
            {{ __('Email Sending Quota') }}
        </button>
        <button type="button" @click="activeTab = 'email_validation'" :class="activeTab === 'email_validation' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-primary-500 dark:!border-primary-400 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-transparent text-gray-500 hover:text-gray-700 hover:!border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
            {{ __('Email Validation') }}
        </button>
        <button type="button" @click="activeTab = 'integrations'" :class="activeTab === 'integrations' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-primary-500 dark:!border-primary-400 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-transparent text-gray-500 hover:text-gray-700 hover:!border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
            {{ __('Integrations') }}
        </button>
        <button type="button" @click="activeTab = 'ai'" :class="activeTab === 'ai' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-primary-500 dark:!border-primary-400 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-transparent text-gray-500 hover:text-gray-700 hover:!border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
            {{ __('AI') }}
        </button>
    </nav>
</div>

<div x-show="activeTab === 'messages'">
    <x-card title="{{ __('Messages') }}">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Default access denied message') }}</label>
                <input type="text" name="messages[access][default]" value="{{ old('messages.access.default', data_get($settings ?? $defaultSettings ?? [], 'messages.access.default', '')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm" placeholder="{{ __('You have no access here.') }}">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Access denied message (Tracking Domains)') }}</label>
                <input type="text" name="messages[access][domains][tracking_domains][can_manage]" value="{{ old('messages.access.domains.tracking_domains.can_manage', data_get($settings ?? $defaultSettings ?? [], 'messages.access.domains.tracking_domains.can_manage', '')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm" placeholder="{{ __('You have no access to tracking domains.') }}">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Access denied message (Sending Domains)') }}</label>
                <input type="text" name="messages[access][domains][sending_domains][can_manage]" value="{{ old('messages.access.domains.sending_domains.can_manage', data_get($settings ?? $defaultSettings ?? [], 'messages.access.domains.sending_domains.can_manage', '')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm" placeholder="{{ __('You have no access to sending domains.') }}">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Access denied message (Delivery Servers)') }}</label>
                <input type="text" name="messages[access][servers][permissions][can_add_delivery_servers]" value="{{ old('messages.access.servers.permissions.can_add_delivery_servers', data_get($settings ?? $defaultSettings ?? [], 'messages.access.servers.permissions.can_add_delivery_servers', '')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm" placeholder="{{ __('You have no access to delivery servers.') }}">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Limit reached message (Tracking Domains)') }}</label>
                <input type="text" name="messages[limits][domains][tracking_domains][max_tracking_domains]" value="{{ old('messages.limits.domains.tracking_domains.max_tracking_domains', data_get($settings ?? $defaultSettings ?? [], 'messages.limits.domains.tracking_domains.max_tracking_domains', '')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm" placeholder="{{ __('Your tracking domain limits expired.') }}">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Limit reached message (Sending Domains)') }}</label>
                <input type="text" name="messages[limits][domains][sending_domains][max_sending_domains]" value="{{ old('messages.limits.domains.sending_domains.max_sending_domains', data_get($settings ?? $defaultSettings ?? [], 'messages.limits.domains.sending_domains.max_sending_domains', '')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm" placeholder="{{ __('Your sending domain limits expired.') }}">
            </div>
        </div>
    </x-card>
</div>

<div x-show="activeTab === 'automations'">
    <x-card title="{{ __('Automations') }}">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Access to automations') }}</span>
                    <div class="flex items-center">
                        <input type="hidden" name="automations[enabled]" value="0">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="automations[enabled]" value="1" {{ old('automations.enabled', data_get($settings ?? $defaultSettings ?? [], 'automations.enabled', false)) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </x-card>
</div>

@if($outreachAddonActive)
<div x-show="activeTab === 'outreach'">
    <x-card title="{{ __('Outreach') }}">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Access to outreach') }}</span>
                    <div class="flex items-center">
                        <input type="hidden" name="outreach[access]" value="0">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="outreach[access]" value="1" {{ old('outreach.access', data_get($settings ?? $defaultSettings ?? [], 'outreach.access', false)) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                </div>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ __('Show the Outreach section for customers in this group and allow them to use the cold outreach addon.') }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Number of outreach campaigns') }}</label>
                <input type="number" min="0" name="outreach[max_campaigns]" value="{{ old('outreach.max_campaigns', data_get($settings ?? $defaultSettings ?? [], 'outreach.max_campaigns', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Set 0 for unlimited.') }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Number of sequences per outreach') }}</label>
                <input type="number" min="0" name="outreach[max_sequences_per_campaign]" value="{{ old('outreach.max_sequences_per_campaign', data_get($settings ?? $defaultSettings ?? [], 'outreach.max_sequences_per_campaign', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Set 0 for unlimited.') }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Number of leads per outreach') }}</label>
                <input type="number" min="0" name="outreach[max_leads_per_campaign]" value="{{ old('outreach.max_leads_per_campaign', data_get($settings ?? $defaultSettings ?? [], 'outreach.max_leads_per_campaign', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Set 0 for unlimited.') }}</p>
            </div>
        </div>
    </x-card>
</div>
@endif

@if($superScrapeAddonActive)
<div x-show="activeTab === 'super_scrape'">
    <x-card title="{{ __('Lead Scraper (SuperScrape)') }}">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Access to Lead Scraper') }}</span>
                    <div class="flex items-center">
                        <input type="hidden" name="super_scrape[access]" value="0">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="super_scrape[access]" value="1" {{ old('super_scrape.access', data_get($settings ?? $defaultSettings ?? [], 'super_scrape.access', true)) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                </div>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ __('Allow customers in this group to access the Lead Scraper and run scraping jobs.') }}</p>
            </div>

            <div class="sm:col-span-2">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <div class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Google') }}</div>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Allow access to Google scraping.') }}</p>
                            </div>
                            <div class="flex items-center">
                                <input type="hidden" name="super_scrape[google_access]" value="0">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="super_scrape[google_access]" value="1" {{ old('super_scrape.google_access', data_get($settings ?? $defaultSettings ?? [], 'super_scrape.google_access', true)) ? 'checked' : '' }} class="sr-only peer">
                                    <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg border border-dashed border-gray-200 dark:border-gray-700 p-4 opacity-70">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <div class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Instagram') }} <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('(Coming soon)') }}</span></div>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Will stay inactive until released.') }}</p>
                            </div>
                            <div class="flex items-center">
                                <input type="hidden" name="super_scrape[instagram_access]" value="0">
                                <label class="inline-flex items-center cursor-not-allowed">
                                    <input type="checkbox" disabled class="sr-only peer">
                                    <div class="relative w-11 h-6 bg-gray-200 rounded-full dark:bg-gray-700 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5"></div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg border border-dashed border-gray-200 dark:border-gray-700 p-4 opacity-70">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <div class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('LinkedIn') }} <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('(Coming soon)') }}</span></div>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Will stay inactive until released.') }}</p>
                            </div>
                            <div class="flex items-center">
                                <input type="hidden" name="super_scrape[linkedin_access]" value="0">
                                <label class="inline-flex items-center cursor-not-allowed">
                                    <input type="checkbox" disabled class="sr-only peer">
                                    <div class="relative w-11 h-6 bg-gray-200 rounded-full dark:bg-gray-700 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5"></div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg border border-dashed border-gray-200 dark:border-gray-700 p-4 opacity-70">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <div class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('TikTok') }} <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('(Coming soon)') }}</span></div>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Will stay inactive until released.') }}</p>
                            </div>
                            <div class="flex items-center">
                                <input type="hidden" name="super_scrape[tiktok_access]" value="0">
                                <label class="inline-flex items-center cursor-not-allowed">
                                    <input type="checkbox" disabled class="sr-only peer">
                                    <div class="relative w-11 h-6 bg-gray-200 rounded-full dark:bg-gray-700 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5"></div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg border border-dashed border-gray-200 dark:border-gray-700 p-4 opacity-70">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <div class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Facebook') }} <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('(Coming soon)') }}</span></div>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Will stay inactive until released.') }}</p>
                            </div>
                            <div class="flex items-center">
                                <input type="hidden" name="super_scrape[facebook_access]" value="0">
                                <label class="inline-flex items-center cursor-not-allowed">
                                    <input type="checkbox" disabled class="sr-only peer">
                                    <div class="relative w-11 h-6 bg-gray-200 rounded-full dark:bg-gray-700 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5"></div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg border border-dashed border-gray-200 dark:border-gray-700 p-4 opacity-70">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <div class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('X') }} <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('(Coming soon)') }}</span></div>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Will stay inactive until released.') }}</p>
                            </div>
                            <div class="flex items-center">
                                <input type="hidden" name="super_scrape[x_access]" value="0">
                                <label class="inline-flex items-center cursor-not-allowed">
                                    <input type="checkbox" disabled class="sr-only peer">
                                    <div class="relative w-11 h-6 bg-gray-200 rounded-full dark:bg-gray-700 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Monthly Scraper Credits Override') }}</label>
                <input type="number" min="0" name="super_scrape[monthly_credits]" value="{{ old('super_scrape.monthly_credits', data_get($settings ?? $defaultSettings ?? [], 'super_scrape.monthly_credits', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Set 0 to use the global default from SuperScrape settings.') }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Max Scraping Jobs') }}</label>
                <input type="number" min="0" name="super_scrape[max_jobs]" value="{{ old('super_scrape.max_jobs', data_get($settings ?? $defaultSettings ?? [], 'super_scrape.max_jobs', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Maximum total scraping jobs allowed. Set 0 for unlimited.') }}</p>
            </div>
        </div>
    </x-card>
</div>
@endif

<div x-show="activeTab === 'integrations'">
    <x-card title="{{ __('Integrations') }}">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Access to Google Integrations') }}</span>
                    <div class="flex items-center">
                        <input type="hidden" name="integrations[permissions][can_access_google]" value="0">
                        <label class="inline-flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                name="integrations[permissions][can_access_google]"
                                value="1"
                                {{ old('integrations.permissions.can_access_google', data_get($settings ?? $defaultSettings ?? [], 'integrations.permissions.can_access_google', false)) ? 'checked' : '' }}
                                class="sr-only peer"
                            >
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                </div>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ __('Enables the Integrations → Google page for customers in this group (Sheets/Drive connect & sync).') }}</p>
            </div>
        </div>
    </x-card>
</div>

<div x-show="activeTab === 'email_validation'">
    <x-card title="{{ __('Email Validation') }}">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Access') }}</span>
                    <div class="flex items-center">
                        <input type="hidden" name="email_validation[access]" value="0">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="email_validation[access]" value="1" {{ old('email_validation.access', data_get($settings ?? $defaultSettings ?? [], 'email_validation.access', false)) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="sm:col-span-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Must add own tool') }}</span>
                    <div class="flex items-center">
                        <input type="hidden" name="email_validation[must_add]" value="0">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="email_validation[must_add]" value="1" {{ old('email_validation.must_add', data_get($settings ?? $defaultSettings ?? [], 'email_validation.must_add', false)) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Max Number of tools to add') }}</label>
                <input type="number" min="0" name="email_validation[max_tools]" value="{{ old('email_validation.max_tools', data_get($settings ?? $defaultSettings ?? [], 'email_validation.max_tools', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Monthly validation limit') }}</label>
                <input type="number" min="0" name="email_validation[monthly_limit]" value="{{ old('email_validation.monthly_limit', data_get($settings ?? $defaultSettings ?? [], 'email_validation.monthly_limit', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
            </div>
        </div>
    </x-card>
</div>

<div x-show="activeTab === 'ai'">
    <x-card title="{{ __('AI') }}">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Customer must use their API keys') }}</span>
                    <div class="flex items-center">
                        <input type="hidden" name="ai[must_use_own_keys]" value="0">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="ai[must_use_own_keys]" value="1" {{ old('ai.must_use_own_keys', data_get($settings ?? $defaultSettings ?? [], 'ai.must_use_own_keys', false)) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Token limit (when using admin keys)') }}</label>
                <input type="number" min="0" name="ai[token_limit]" value="{{ old('ai.token_limit', data_get($settings ?? $defaultSettings ?? [], 'ai.token_limit', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Image generation credits (when using admin keys)') }}</label>
                <input type="number" min="0" name="ai[image_credits]" value="{{ old('ai.image_credits', data_get($settings ?? $defaultSettings ?? [], 'ai.image_credits', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
            </div>
        </div>
    </x-card>
</div>

<div x-show="activeTab === 'email_lists'">
    <x-card title="{{ __('Email Lists') }}">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Max Number of Lists') }}</label>
                <input type="number" min="0" name="lists[limits][max_lists]" value="{{ old('lists.limits.max_lists', data_get($settings ?? $defaultSettings ?? [], 'lists.limits.max_lists', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Max Subscribers Per List') }}</label>
                <input type="number" min="0" name="lists[limits][max_subscribers_per_list]" value="{{ old('lists.limits.max_subscribers_per_list', data_get($settings ?? $defaultSettings ?? [], 'lists.limits.max_subscribers_per_list', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Max Forms Per List') }}</label>
                <input type="number" min="0" name="lists[limits][max_forms_per_list]" value="{{ old('lists.limits.max_forms_per_list', data_get($settings ?? $defaultSettings ?? [], 'lists.limits.max_forms_per_list', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Max Subscribers (All Lists)') }}</label>
                <input type="number" min="0" name="lists[limits][max_subscribers]" value="{{ old('lists.limits.max_subscribers', data_get($settings ?? $defaultSettings ?? [], 'lists.limits.max_subscribers', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
            </div>
        </div>
    </x-card>
</div>

<div x-show="activeTab === 'campaigns'">
    <x-card title="{{ __('Campaigns') }}">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Max Campaigns to Create') }}</label>
                <input type="number" min="0" name="campaigns[limits][max_campaigns]" value="{{ old('campaigns.limits.max_campaigns', data_get($settings ?? $defaultSettings ?? [], 'campaigns.limits.max_campaigns', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Max Campaigns to Run') }}</label>
                <input type="number" min="0" name="campaigns[limits][max_active_campaigns]" value="{{ old('campaigns.limits.max_active_campaigns', data_get($settings ?? $defaultSettings ?? [], 'campaigns.limits.max_active_campaigns', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
            </div>
            <div class="sm:col-span-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Access to A/B testing') }}</span>
                    <div class="flex items-center">
                        <input type="hidden" name="campaigns[features][ab_testing]" value="0">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="campaigns[features][ab_testing]" value="1" {{ old('campaigns.features.ab_testing', data_get($settings ?? $defaultSettings ?? [], 'campaigns.features.ab_testing', false)) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="sm:col-span-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Can create campaigns') }}</span>
                    <div class="flex items-center">
                        <input type="hidden" name="campaigns[permissions][can_create_campaigns]" value="0">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="campaigns[permissions][can_create_campaigns]" value="1" {{ old('campaigns.permissions.can_create_campaigns', data_get($settings ?? $defaultSettings ?? [], 'campaigns.permissions.can_create_campaigns', false)) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="sm:col-span-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Can edit campaigns') }}</span>
                    <div class="flex items-center">
                        <input type="hidden" name="campaigns[permissions][can_edit_campaigns]" value="0">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="campaigns[permissions][can_edit_campaigns]" value="1" {{ old('campaigns.permissions.can_edit_campaigns', data_get($settings ?? $defaultSettings ?? [], 'campaigns.permissions.can_edit_campaigns', false)) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="sm:col-span-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Can start/pause/resume campaigns') }}</span>
                    <div class="flex items-center">
                        <input type="hidden" name="campaigns[permissions][can_start_campaigns]" value="0">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="campaigns[permissions][can_start_campaigns]" value="1" {{ old('campaigns.permissions.can_start_campaigns', data_get($settings ?? $defaultSettings ?? [], 'campaigns.permissions.can_start_campaigns', false)) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="sm:col-span-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Can delete campaigns') }}</span>
                    <div class="flex items-center">
                        <input type="hidden" name="campaigns[permissions][can_delete_campaigns]" value="0">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="campaigns[permissions][can_delete_campaigns]" value="1" {{ old('campaigns.permissions.can_delete_campaigns', data_get($settings ?? $defaultSettings ?? [], 'campaigns.permissions.can_delete_campaigns', false)) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </x-card>
</div>

<div x-show="activeTab === 'autoresponders'">
    <x-card title="{{ __('Auto Responders') }}">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Access to auto responders') }}</span>
                    <div class="flex items-center">
                        <input type="hidden" name="autoresponders[enabled]" value="0">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="autoresponders[enabled]" value="1" {{ old('autoresponders.enabled', data_get($settings ?? $defaultSettings ?? [], 'autoresponders.enabled', false)) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Number of Auto Responders to Create') }}</label>
                <input type="number" min="0" name="autoresponders[max_autoresponders]" value="{{ old('autoresponders.max_autoresponders', data_get($settings ?? $defaultSettings ?? [], 'autoresponders.max_autoresponders', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
            </div>
        </div>
    </x-card>
</div>

<div x-show="activeTab === 'servers'">
    <x-card title="{{ __('Servers') }}">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div>
                <div class="sm:col-span-2 mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Allocated Delivery Servers') }}</label>
                    @php
                        $allocatedDeliveryServerOptions = collect($deliveryServers ?? [])->map(function ($server) {
                            $ownerLabel = $server->customer ? ('Customer: ' . $server->customer->email) : 'System';

                            return [
                                'id' => (string) $server->id,
                                'label' => $server->name . ' (' . $ownerLabel . ')',
                            ];
                        })->values()->all();
                    @endphp
                    <x-tag-multiselect
                        name="allocated_delivery_server_ids[]"
                        :options="$allocatedDeliveryServerOptions"
                        :selected="old('allocated_delivery_server_ids', $allocatedDeliveryServerIds ?? [])"
                        placeholder="{{ __('Select delivery servers') }}"
                        search-placeholder="{{ __('Search servers...') }}"
                    />
                    @error('allocated_delivery_server_ids')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    @error('allocated_delivery_server_ids.*')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('If set, this group can only use these delivery servers (unless a customer has their own allocation).') }}</p>
                </div>
                <div class="sm:col-span-2 mb-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Must add delivery server') }}</span>
                        <div class="flex items-center">
                            <input type="hidden" name="servers[permissions][must_add_delivery_server]" value="0">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="servers[permissions][must_add_delivery_server]" value="1" {{ old('servers.permissions.must_add_delivery_server', data_get($settings ?? $defaultSettings ?? [], 'servers.permissions.must_add_delivery_server', false)) ? 'checked' : '' }} class="sr-only peer">
                                <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="sm:col-span-2 mb-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Can add delivery servers') }}</span>
                        <div class="flex items-center">
                            <input type="hidden" name="servers[permissions][can_add_delivery_servers]" value="0">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="servers[permissions][can_add_delivery_servers]" value="1" {{ old('servers.permissions.can_add_delivery_servers', data_get($settings ?? $defaultSettings ?? [], 'servers.permissions.can_add_delivery_servers', false)) ? 'checked' : '' }} class="sr-only peer">
                                <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="sm:col-span-2 mb-4">
                    @php($showExtendedFeaturePopup = !($hasExtendedLicense ?? false))
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Can use Gmail/Outlook mailbox providers (Extended)') }}</span>
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                @click="if (@json($showExtendedFeaturePopup)) { $dispatch('open-modal', 'pro-feature-extended-mailbox-providers-admin') }"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-amber-300 bg-amber-50 text-amber-600 transition hover:bg-amber-100 dark:border-amber-700/70 dark:bg-amber-900/25 dark:text-amber-300 dark:hover:bg-amber-900/40"
                                title="{{ __('Pro feature') }}"
                                aria-label="{{ __('Pro feature') }}"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M5 18h14l1.5-9-4.5 2.5L12 4 7.5 11.5 3 9 5 18zm0 2a1 1 0 100 2h14a1 1 0 100-2H5z" />
                                </svg>
                            </button>
                            <input type="hidden" name="servers[permissions][can_use_extended_mailbox_providers]" value="0">
                            <label class="inline-flex items-center cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="servers[permissions][can_use_extended_mailbox_providers]"
                                    value="1"
                                    {{ old('servers.permissions.can_use_extended_mailbox_providers', data_get($settings ?? $defaultSettings ?? [], 'servers.permissions.can_use_extended_mailbox_providers', false)) ? 'checked' : '' }}
                                    class="sr-only peer"
                                    @change="if (@json($showExtendedFeaturePopup) && $event.target.checked) { $event.target.checked = false; $dispatch('open-modal', 'pro-feature-extended-mailbox-providers-admin') }"
                                >
                                <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                            </label>
                        </div>
                    </div>
                </div>
                <x-modal name="pro-feature-extended-mailbox-providers-admin" maxWidth="md">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Pro Feature') }}</h2>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                            {{ __('Gmail and Outlook mailbox providers are available as an Extended/Pro feature.') }}
                        </p>
                        <div class="mt-6 flex justify-end">
                            <x-button type="button" variant="secondary" @click="$dispatch('close-modal', 'pro-feature-extended-mailbox-providers-admin')">{{ __('Close') }}</x-button>
                        </div>
                    </div>
                </x-modal>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Max Delivery Servers') }}</label>
                <input type="number" min="0" name="servers[limits][max_delivery_servers]" value="{{ old('servers.limits.max_delivery_servers', data_get($settings ?? $defaultSettings ?? [], 'servers.limits.max_delivery_servers', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
            </div>
            <div>
                <div class="sm:col-span-2 mb-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Must add bounce server') }}</span>
                        <div class="flex items-center">
                            <input type="hidden" name="servers[permissions][must_add_bounce_server]" value="0">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="servers[permissions][must_add_bounce_server]" value="1" {{ old('servers.permissions.must_add_bounce_server', data_get($settings ?? $defaultSettings ?? [], 'servers.permissions.must_add_bounce_server', false)) ? 'checked' : '' }} class="sr-only peer">
                                <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="sm:col-span-2 mb-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Access bounce servers') }}</span>
                        <div class="flex items-center">
                            <input type="hidden" name="servers[permissions][can_access_bounce_servers]" value="0">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="servers[permissions][can_access_bounce_servers]" value="1" {{ old('servers.permissions.can_access_bounce_servers', data_get($settings ?? $defaultSettings ?? [], 'servers.permissions.can_access_bounce_servers', false)) ? 'checked' : '' }} class="sr-only peer">
                                <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="sm:col-span-2 mb-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Can add bounce servers') }}</span>
                        <div class="flex items-center">
                            <input type="hidden" name="servers[permissions][can_add_bounce_servers]" value="0">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="servers[permissions][can_add_bounce_servers]" value="1" {{ old('servers.permissions.can_add_bounce_servers', data_get($settings ?? $defaultSettings ?? [], 'servers.permissions.can_add_bounce_servers', false)) ? 'checked' : '' }} class="sr-only peer">
                                <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="sm:col-span-2 mb-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Can edit bounce servers') }}</span>
                        <div class="flex items-center">
                            <input type="hidden" name="servers[permissions][can_edit_bounce_servers]" value="0">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="servers[permissions][can_edit_bounce_servers]" value="1" {{ old('servers.permissions.can_edit_bounce_servers', data_get($settings ?? $defaultSettings ?? [], 'servers.permissions.can_edit_bounce_servers', false)) ? 'checked' : '' }} class="sr-only peer">
                                <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="sm:col-span-2 mb-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Can delete bounce servers') }}</span>
                        <div class="flex items-center">
                            <input type="hidden" name="servers[permissions][can_delete_bounce_servers]" value="0">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="servers[permissions][can_delete_bounce_servers]" value="1" {{ old('servers.permissions.can_delete_bounce_servers', data_get($settings ?? $defaultSettings ?? [], 'servers.permissions.can_delete_bounce_servers', false)) ? 'checked' : '' }} class="sr-only peer">
                                <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                            </label>
                        </div>
                    </div>
                </div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Max Bounce Servers') }}</label>
                <input type="number" min="0" name="servers[limits][max_bounce_servers]" value="{{ old('servers.limits.max_bounce_servers', data_get($settings ?? $defaultSettings ?? [], 'servers.limits.max_bounce_servers', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
            </div>

            <div>
                <div class="sm:col-span-2 mb-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Must add reply server') }}</span>
                        <div class="flex items-center">
                            <input type="hidden" name="servers[permissions][must_add_reply_server]" value="0">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="servers[permissions][must_add_reply_server]" value="1" {{ old('servers.permissions.must_add_reply_server', data_get($settings ?? $defaultSettings ?? [], 'servers.permissions.must_add_reply_server', false)) ? 'checked' : '' }} class="sr-only peer">
                                <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="sm:col-span-2 mb-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Access reply servers') }}</span>
                        <div class="flex items-center">
                            <input type="hidden" name="servers[permissions][can_access_reply_servers]" value="0">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="servers[permissions][can_access_reply_servers]" value="1" {{ old('servers.permissions.can_access_reply_servers', data_get($settings ?? $defaultSettings ?? [], 'servers.permissions.can_access_reply_servers', false)) ? 'checked' : '' }} class="sr-only peer">
                                <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="sm:col-span-2 mb-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Can add reply servers') }}</span>
                        <div class="flex items-center">
                            <input type="hidden" name="servers[permissions][can_add_reply_servers]" value="0">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="servers[permissions][can_add_reply_servers]" value="1" {{ old('servers.permissions.can_add_reply_servers', data_get($settings ?? $defaultSettings ?? [], 'servers.permissions.can_add_reply_servers', false)) ? 'checked' : '' }} class="sr-only peer">
                                <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="sm:col-span-2 mb-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Can edit reply servers') }}</span>
                        <div class="flex items-center">
                            <input type="hidden" name="servers[permissions][can_edit_reply_servers]" value="0">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="servers[permissions][can_edit_reply_servers]" value="1" {{ old('servers.permissions.can_edit_reply_servers', data_get($settings ?? $defaultSettings ?? [], 'servers.permissions.can_edit_reply_servers', false)) ? 'checked' : '' }} class="sr-only peer">
                                <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="sm:col-span-2 mb-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Can delete reply servers') }}</span>
                        <div class="flex items-center">
                            <input type="hidden" name="servers[permissions][can_delete_reply_servers]" value="0">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="servers[permissions][can_delete_reply_servers]" value="1" {{ old('servers.permissions.can_delete_reply_servers', data_get($settings ?? $defaultSettings ?? [], 'servers.permissions.can_delete_reply_servers', false)) ? 'checked' : '' }} class="sr-only peer">
                                <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Max Reply Servers') }}</label>
                <input type="number" min="0" name="servers[limits][max_reply_servers]" value="{{ old('servers.limits.max_reply_servers', data_get($settings ?? $defaultSettings ?? [], 'servers.limits.max_reply_servers', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
            </div>
        </div>
    </x-card>
</div>

<div x-show="activeTab === 'tracking_domains'">
    <x-card title="{{ __('Tracking Domain') }}">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Must add tracking domain') }}</span>
                    <div class="flex items-center">
                        <input type="hidden" name="domains[tracking_domains][must_add]" value="0">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="domains[tracking_domains][must_add]" value="1" {{ old('domains.tracking_domains.must_add', data_get($settings ?? $defaultSettings ?? [], 'domains.tracking_domains.must_add', false)) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                </div>
            </div>
            <div class="sm:col-span-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Access') }}</span>
                    <div class="flex items-center">
                        <input type="hidden" name="domains[tracking_domains][can_manage]" value="0">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="domains[tracking_domains][can_manage]" value="1" {{ old('domains.tracking_domains.can_manage', data_get($settings ?? $defaultSettings ?? [], 'domains.tracking_domains.can_manage', false)) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Limit (Max tracking domains)') }}</label>
                <input type="number" min="0" name="domains[tracking_domains][max_tracking_domains]" value="{{ old('domains.tracking_domains.max_tracking_domains', data_get($settings ?? $defaultSettings ?? [], 'domains.tracking_domains.max_tracking_domains', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
            </div>
        </div>
    </x-card>
</div>

<div x-show="activeTab === 'sending_domains'">
    <x-card title="{{ __('Sending Domain') }}">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Must add sending domain') }}</span>
                    <div class="flex items-center">
                        <input type="hidden" name="domains[sending_domains][must_add]" value="0">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="domains[sending_domains][must_add]" value="1" {{ old('domains.sending_domains.must_add', data_get($settings ?? $defaultSettings ?? [], 'domains.sending_domains.must_add', false)) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                </div>
            </div>
            <div class="sm:col-span-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Access') }}</span>
                    <div class="flex items-center">
                        <input type="hidden" name="domains[sending_domains][can_manage]" value="0">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="domains[sending_domains][can_manage]" value="1" {{ old('domains.sending_domains.can_manage', data_get($settings ?? $defaultSettings ?? [], 'domains.sending_domains.can_manage', false)) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Limit (Max sending domains)') }}</label>
                <input type="number" min="0" name="domains[sending_domains][max_sending_domains]" value="{{ old('domains.sending_domains.max_sending_domains', data_get($settings ?? $defaultSettings ?? [], 'domains.sending_domains.max_sending_domains', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
            </div>
        </div>
    </x-card>
</div>

<div x-show="activeTab === 'sending_quota'">
    <x-card title="{{ __('Email Sending Quota') }}">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Daily') }}</label>
                <input type="number" min="0" name="sending_quota[daily_quota]" value="{{ old('sending_quota.daily_quota', data_get($settings ?? $defaultSettings ?? [], 'sending_quota.daily_quota', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Weekly') }}</label>
                <input type="number" min="0" name="sending_quota[weekly_quota]" value="{{ old('sending_quota.weekly_quota', data_get($settings ?? $defaultSettings ?? [], 'sending_quota.weekly_quota', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Monthly') }}</label>
                <input type="number" min="0" name="sending_quota[monthly_quota]" value="{{ old('sending_quota.monthly_quota', data_get($settings ?? $defaultSettings ?? [], 'sending_quota.monthly_quota', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
            </div>
        </div>
    </x-card>
</div>
