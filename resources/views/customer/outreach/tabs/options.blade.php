@php
    $stopOnReply      = $campaign->getSetting('stop_on_reply', true);
    $stopOnAutoReply  = $campaign->getSetting('stop_on_auto_reply', false);
    $trackingDomain   = $campaign->getSetting('tracking_domain');
    $bccEmail         = $campaign->getSetting('bcc_email', '');
    $senderIds        = $campaign->getSetting('sender_account_ids', []);
    $accountRotation  = $campaign->getSetting('enable_account_rotation', true);
    $includeUnsub     = $campaign->getSetting('include_unsubscribe_link', true);
    $unsubText        = $campaign->getSetting('unsubscribe_text', 'If you no longer wish to receive these emails, click here to unsubscribe.');
@endphp

<form method="POST" action="{{ route('customer.outreach.campaigns.options.update', $campaign) }}" id="save-form-options"
    x-data="{ confirm_delete: false }" class="space-y-5">
    @csrf

    {{-- General Settings --}}
    <div class="bg-white dark:bg-admin-card border border-gray-100 dark:border-admin-border rounded-xl divide-y divide-gray-100 dark:divide-admin-border">
        <div class="px-6 py-4">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-admin-text-primary">{{ __('General Settings') }}</h2>
            <p class="text-xs text-gray-500 dark:text-admin-text-secondary mt-0.5">{{ __('Basic configuration for this campaign.') }}</p>
        </div>

        {{-- Campaign Name --}}
        <div class="px-6 py-5 flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="sm:w-64 flex-shrink-0">
                <label class="text-sm font-medium text-gray-700 dark:text-admin-text-primary">{{ __('Campaign Name') }}</label>
            </div>
            <input type="text" name="name" value="{{ $campaign->name }}"
                class="flex-1 px-3 py-2.5 text-sm border border-gray-200 dark:border-admin-border bg-white dark:bg-white/5 text-gray-900 dark:text-admin-text-primary rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1E5FEA]">
        </div>

        {{-- Stop on reply --}}
        <div class="px-6 py-5">
            <div class="flex items-center justify-between">
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-admin-text-primary">{{ __('Stop sending on reply') }}</label>
                    <p class="text-xs text-gray-400 dark:text-admin-text-secondary mt-0.5">{{ __('Automatically stop the sequence for a lead if they reply to any email.') }}</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                    <input type="checkbox" name="stop_on_reply" value="1" {{ $stopOnReply ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 dark:bg-white/10 rounded-full peer peer-checked:bg-[#1E5FEA] transition-colors after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
                </label>
            </div>
        </div>

        {{-- Stop on auto-reply --}}
        <div class="px-6 py-5">
            <div class="flex items-center justify-between">
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-admin-text-primary">{{ __('Stop sending on auto-reply') }}</label>
                    <p class="text-xs text-gray-400 dark:text-admin-text-secondary mt-0.5">{{ __('Pause the sequence if an out-of-office or auto-reply is detected.') }}</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                    <input type="checkbox" name="stop_on_auto_reply" value="1" {{ $stopOnAutoReply ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 dark:bg-white/10 rounded-full peer peer-checked:bg-[#1E5FEA] transition-colors after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
                </label>
            </div>
        </div>

        {{-- Custom Tracking Domain --}}
        <div class="px-6 py-5 flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="flex-1">
                <label class="text-sm font-medium text-gray-700 dark:text-admin-text-primary">{{ __('Custom Tracking Domain') }}</label>
                <p class="text-xs text-gray-400 dark:text-admin-text-secondary mt-0.5">{{ __('Use a custom domain for tracking opens and clicks to improve deliverability.') }}</p>
            </div>
            <div class="sm:w-64">
                <select name="tracking_domain" class="w-full appearance-none px-3 py-2.5 text-sm border border-gray-200 dark:border-admin-border bg-white dark:bg-white/5 text-gray-700 dark:text-admin-text-primary rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1E5FEA]">
                    <option value="">{{ __('None') }}</option>
                    @foreach($trackingDomains as $td)
                        <option value="{{ $td->domain }}" {{ $trackingDomain === $td->domain ? 'selected' : '' }}>{{ $td->domain }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- BCC Email --}}
        <div class="px-6 py-5 flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="flex-1">
                <label class="text-sm font-medium text-gray-700 dark:text-admin-text-primary">{{ __('BCC Email (CRM Integration)') }}</label>
                <p class="text-xs text-gray-400 dark:text-admin-text-secondary mt-0.5">{{ __('Automatically BCC all outgoing campaign emails to this address (e.g., Salesforce, HubSpot).') }}</p>
            </div>
            <input type="email" name="bcc_email" value="{{ $bccEmail }}" placeholder="{{ __('e.g. bcc@hubspot.com') }}"
                class="sm:w-64 px-3 py-2.5 text-sm border border-gray-200 dark:border-admin-border bg-white dark:bg-white/5 text-gray-900 dark:text-admin-text-primary rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1E5FEA] placeholder-gray-400">
        </div>
    </div>

    {{-- Sending Accounts --}}
    <div class="bg-white dark:bg-admin-card border border-gray-100 dark:border-admin-border rounded-xl divide-y divide-gray-100 dark:divide-admin-border">
        <div class="px-6 py-4">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-admin-text-primary">{{ __('Sending Accounts') }}</h2>
            <p class="text-xs text-gray-500 dark:text-admin-text-secondary mt-0.5">{{ __('Select which email accounts will be used to send this campaign.') }}</p>
        </div>

        <div class="px-6 py-5 space-y-4">
            <div>
                <label class="text-xs font-semibold text-gray-700 dark:text-admin-text-secondary uppercase tracking-wide mb-3 block">{{ __('Sender Accounts') }}</label>
                <p class="text-xs text-gray-400 dark:text-admin-text-secondary mb-3">{{ __('Select one or more accounts. Emails will be distributed among selected accounts.') }}</p>
                @if($deliveryServers->isEmpty())
                    <div class="px-4 py-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg text-xs text-amber-700 dark:text-amber-400">
                        {{ __('No active delivery servers found.') }}
                        <a href="{{ route('customer.delivery-servers.create') }}" class="underline font-medium ml-1">{{ __('Add one now') }}</a>
                    </div>
                @else
                    <div class="space-y-2">
                        @foreach($deliveryServers as $ds)
                            <label class="flex items-center justify-between p-3.5 border border-gray-200 dark:border-admin-border rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" name="sender_account_ids[]" value="{{ $ds->id }}"
                                        {{ in_array($ds->id, $senderIds) ? 'checked' : '' }}
                                        class="w-4 h-4 rounded border-gray-300 text-[#1E5FEA]">
                                    <div class="w-7 h-7 rounded-full bg-gray-100 dark:bg-white/10 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    </div>
                                    <span class="text-sm text-gray-700 dark:text-admin-text-primary">{{ $ds->from_email ?? $ds->name }}</span>
                                </div>
                                <span class="text-xs px-2 py-1 rounded-full border font-medium
                                    {{ $ds->status === 'active' ? 'text-green-700 bg-green-50 border-green-200 dark:bg-green-900/20 dark:text-green-400 dark:border-green-800' : 'text-amber-600 bg-amber-50 border-amber-200 dark:bg-amber-900/20 dark:text-amber-400' }}">
                                    {{ ucfirst($ds->status) }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-admin-border">
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-admin-text-primary">{{ __('Enable Account Rotation') }}</label>
                    <p class="text-xs text-gray-400 dark:text-admin-text-secondary mt-0.5">{{ __('Automatically rotate between selected sender accounts to maximize deliverability.') }}</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                    <input type="checkbox" name="enable_account_rotation" value="1" {{ $accountRotation ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 dark:bg-white/10 rounded-full peer peer-checked:bg-[#1E5FEA] transition-colors after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
                </label>
            </div>
        </div>
    </div>

    {{-- Unsubscribe Settings --}}
    <div class="bg-white dark:bg-admin-card border border-gray-100 dark:border-admin-border rounded-xl divide-y divide-gray-100 dark:divide-admin-border">
        <div class="px-6 py-4">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-admin-text-primary">{{ __('Unsubscribe Settings') }}</h2>
            <p class="text-xs text-gray-500 dark:text-admin-text-secondary mt-0.5">{{ __('Configure how prospects can opt-out of your campaign.') }}</p>
        </div>

        <div class="px-6 py-5 flex items-center justify-between">
            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-admin-text-primary">{{ __('Include unsubscribe link') }}</label>
                <p class="text-xs text-gray-400 dark:text-admin-text-secondary mt-0.5">{{ __('Add an unsubscribe link to the bottom of all emails in this campaign.') }}</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                <input type="checkbox" name="include_unsubscribe_link" value="1" {{ $includeUnsub ? 'checked' : '' }} class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 dark:bg-white/10 rounded-full peer peer-checked:bg-[#1E5FEA] transition-colors after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
            </label>
        </div>

        <div class="px-6 py-5 flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="flex-1">
                <label class="text-sm font-medium text-gray-700 dark:text-admin-text-primary">{{ __('Unsubscribe text') }}</label>
                <p class="text-xs text-gray-400 dark:text-admin-text-secondary mt-0.5">{{ __('The text that will be displayed for the unsubscribe link.') }}</p>
            </div>
            <input type="text" name="unsubscribe_text" value="{{ $unsubText }}"
                class="flex-1 sm:max-w-sm px-3 py-2.5 text-sm border border-gray-200 dark:border-admin-border bg-white dark:bg-white/5 text-gray-900 dark:text-admin-text-primary rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1E5FEA]">
        </div>
    </div>

    {{-- Danger Zone --}}
    <div class="bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800 rounded-xl divide-y divide-red-100 dark:divide-red-800">
        <div class="px-6 py-4">
            <h2 class="text-sm font-semibold text-red-700 dark:text-red-400">{{ __('Danger Zone') }}</h2>
            <p class="text-xs text-red-600 dark:text-red-500 mt-0.5">{{ __('Irreversible and destructive actions for this campaign.') }}</p>
        </div>
        <div class="px-6 py-5 flex items-center justify-between">
            <div>
                <label class="text-sm font-medium text-gray-800 dark:text-admin-text-primary">{{ __('Delete Campaign') }}</label>
                <p class="text-xs text-gray-500 dark:text-admin-text-secondary mt-0.5">{{ __('Permanently delete this campaign, all its sequences, and related statistics. This action cannot be undone.') }}</p>
            </div>
            <button type="button" @click="confirm_delete = true"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                {{ __('Delete Campaign') }}
            </button>
        </div>
    </div>

    {{-- Delete Confirm Modal --}}
    <div x-cloak x-show="confirm_delete"
        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
        @click.self="confirm_delete = false"
    >
        <div x-show="confirm_delete"
            x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            class="bg-white dark:bg-admin-card rounded-2xl shadow-xl w-full max-w-sm p-6 space-y-4"
        >
            <div class="w-12 h-12 mx-auto bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div class="text-center">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-admin-text-primary">{{ __('Delete Campaign?') }}</h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-admin-text-secondary">{{ __('This will permanently delete "') }}{{ $campaign->name }}{{ __('" including all leads and sequences. This cannot be undone.') }}</p>
            </div>
            <div class="flex gap-3">
                <button type="submit" form="delete-outreach-campaign-form" class="flex-1 w-full py-2.5 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">{{ __('Yes, Delete') }}</button>
                <button type="button" @click="confirm_delete = false" class="flex-1 py-2.5 text-sm font-medium text-gray-700 dark:text-admin-text-primary border border-gray-200 dark:border-admin-border bg-white dark:bg-white/5 hover:bg-gray-50 dark:hover:bg-white/10 rounded-lg transition-colors">{{ __('Cancel') }}</button>
            </div>
        </div>
    </div>
</form>

<form id="delete-outreach-campaign-form" method="POST" action="{{ route('customer.outreach.campaigns.destroy', $campaign) }}" class="hidden">
    @csrf
    @method('DELETE')
</form>

