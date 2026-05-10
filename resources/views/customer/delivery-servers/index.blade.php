@extends('layouts.customer')

@section('title', 'Delivery Servers')
@section('page-title', 'Delivery Servers')

@section('page-actions')
    <div class="flex w-full flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-end">
        <form method="GET" action="{{ route('customer.delivery-servers.index') }}" class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:flex-wrap sm:items-center sm:justify-end">
            <select name="type" class="block w-full sm:w-auto rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                <option value="">All Types</option>
                <option value="smtp" {{ ($filters['type'] ?? '') === 'smtp' ? 'selected' : '' }}>SMTP</option>
                <option value="gmail" {{ ($filters['type'] ?? '') === 'gmail' ? 'selected' : '' }}>Gmail</option>
                <option value="outlook" {{ ($filters['type'] ?? '') === 'outlook' ? 'selected' : '' }}>Outlook</option>
                <option value="sendmail" {{ ($filters['type'] ?? '') === 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                <option value="zeptomail" {{ ($filters['type'] ?? '') === 'zeptomail' ? 'selected' : '' }}>ZeptoMail</option>
                <option value="amazon-ses" {{ ($filters['type'] ?? '') === 'amazon-ses' ? 'selected' : '' }}>Amazon SES</option>
                <option value="mailgun" {{ ($filters['type'] ?? '') === 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                <option value="sendgrid" {{ ($filters['type'] ?? '') === 'sendgrid' ? 'selected' : '' }}>SendGrid</option>
                <option value="postmark" {{ ($filters['type'] ?? '') === 'postmark' ? 'selected' : '' }}>Postmark</option>
                <option value="sparkpost" {{ ($filters['type'] ?? '') === 'sparkpost' ? 'selected' : '' }}>SparkPost</option>
            </select>
            <select name="status" class="block w-full sm:w-auto rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                <option value="">All Statuses</option>
                <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
            </select>
            <x-button type="submit" variant="primary" class="w-full sm:w-auto">Search</x-button>
        </form>

        @customercan('servers.permissions.can_create_delivery_servers')
            <x-button type="button" variant="primary" class="w-full sm:w-auto" @click="$dispatch('open-delivery-server-drawer')">Add Server</x-button>
        @endcustomercan
    </div>
@endsection

@section('content')
<div
    x-data="{
        drawerOpen: false,
        selectedProvider: '',
        providerDefaults: {
            'gmail': { name: 'Gmail', type: 'gmail', flow: 'smtp', hostname: 'smtp.gmail.com', port: 587, encryption: 'tls' },
            'outlook': { name: 'Outlook', type: 'outlook', flow: 'smtp', hostname: 'smtp.office365.com', port: 587, encryption: 'tls' },
            'smtp': { name: 'Other SMTP', type: 'smtp', flow: 'smtp', hostname: '', port: 587, encryption: 'tls' },
            'mailgun': { name: 'Mailgun', type: 'mailgun', flow: 'api' },
            'sendgrid': { name: 'SendGrid', type: 'sendgrid', flow: 'api' },
            'sparkpost': { name: 'SparkPost', type: 'sparkpost', flow: 'api' },
            'amazon-ses': { name: 'Amazon SES', type: 'amazon-ses', flow: 'api' },
            'postmark': { name: 'Postmark', type: 'postmark', flow: 'api' },
            'zeptomail-api': { name: 'ZeptoMail API', type: 'zeptomail-api', flow: 'api' }
        },
        canUseExtendedMailboxProviders: @json((bool) ($canUseExtendedMailboxProviders ?? false)),

        openDrawer() {
            this.drawerOpen = true;
            this.selectedProvider = '';
            this.resetForm();
        },

        closeDrawer() {
            this.drawerOpen = false;
            this.selectedProvider = '';
            this.resetForm();
        },

        resetForm() {
            const form = document.getElementById('delivery-server-form');
            if (form) form.reset();
            this.hideAllFields();
        },

        hideAllFields() {
            const configSection = document.getElementById('config-section');
            const smtpFields = document.getElementById('smtp-fields');
            const apiFields = document.getElementById('api-fields');
            const mailgunFields = document.getElementById('mailgun-fields');
            const sendgridFields = document.getElementById('sendgrid-fields');
            const sparkpostFields = document.getElementById('sparkpost-fields');
            const amazonSesFields = document.getElementById('amazon-ses-fields');
            const postmarkFields = document.getElementById('postmark-fields');
            const zeptomailApiFields = document.getElementById('zeptomail-api-fields');
            const gmailHelp = document.getElementById('gmail-help');

            if (configSection) configSection.classList.add('hidden');
            if (smtpFields) smtpFields.classList.add('is-hidden');
            if (apiFields) apiFields.classList.add('is-hidden');
            if (mailgunFields) mailgunFields.classList.add('is-hidden');
            if (sendgridFields) sendgridFields.classList.add('is-hidden');
            if (sparkpostFields) sparkpostFields.classList.add('is-hidden');
            if (amazonSesFields) amazonSesFields.classList.add('is-hidden');
            if (postmarkFields) postmarkFields.classList.add('is-hidden');
            if (zeptomailApiFields) zeptomailApiFields.classList.add('is-hidden');
            if (gmailHelp) gmailHelp.classList.add('hidden');
        },

        onProviderChange() {
            const providerType = this.selectedProvider;
            const config = this.providerDefaults[providerType];
            if (!config) return;

            if ((providerType === 'gmail' || providerType === 'outlook') && !this.canUseExtendedMailboxProviders) {
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'pro-feature-extended-mailbox-providers' }));
                return;
            }

            const selectedType = document.getElementById('selected-type');
            const selectedFlow = document.getElementById('selected-flow');
            const selectedProviderName = document.getElementById('selected-provider-name');
            const saveServerBtn = document.getElementById('save-server-btn');
            const gmailHelp = document.getElementById('gmail-help');

            if (selectedType) selectedType.value = config.type;
            if (selectedFlow) selectedFlow.value = config.flow;
            if (selectedProviderName) selectedProviderName.textContent = config.name;
            if (gmailHelp) gmailHelp.classList.toggle('hidden', providerType !== 'gmail');

            const configSection = document.getElementById('config-section');
            const smtpFields = document.getElementById('smtp-fields');
            const apiFields = document.getElementById('api-fields');
            const mailgunFields = document.getElementById('mailgun-fields');
            const sendgridFields = document.getElementById('sendgrid-fields');
            const sparkpostFields = document.getElementById('sparkpost-fields');
            const amazonSesFields = document.getElementById('amazon-ses-fields');
            const postmarkFields = document.getElementById('postmark-fields');
            const zeptomailApiFields = document.getElementById('zeptomail-api-fields');

            if (configSection) configSection.classList.remove('hidden');
            if (saveServerBtn) saveServerBtn.disabled = false;

            if (smtpFields) smtpFields.classList.add('is-hidden');
            if (apiFields) apiFields.classList.add('is-hidden');
            if (mailgunFields) mailgunFields.classList.add('is-hidden');
            if (sendgridFields) sendgridFields.classList.add('is-hidden');
            if (sparkpostFields) sparkpostFields.classList.add('is-hidden');
            if (amazonSesFields) amazonSesFields.classList.add('is-hidden');
            if (postmarkFields) postmarkFields.classList.add('is-hidden');
            if (zeptomailApiFields) zeptomailApiFields.classList.add('is-hidden');

            if (config.flow === 'smtp') {
                if (smtpFields) smtpFields.classList.remove('is-hidden');
                if (config.hostname) {
                    const hostnameInput = document.getElementById('smtp-hostname');
                    if (hostnameInput) hostnameInput.value = config.hostname;
                }
                if (config.port) {
                    const portInput = document.getElementById('smtp-port');
                    if (portInput) portInput.value = config.port;
                }
                if (config.encryption) {
                    const encryptionInput = document.getElementById('smtp-encryption');
                    if (encryptionInput) encryptionInput.value = config.encryption;
                }
            } else {
                if (apiFields) apiFields.classList.remove('is-hidden');

                switch (providerType) {
                    case 'mailgun':
                        if (mailgunFields) mailgunFields.classList.remove('is-hidden');
                        break;
                    case 'sendgrid':
                        if (sendgridFields) sendgridFields.classList.remove('is-hidden');
                        break;
                    case 'sparkpost':
                        if (sparkpostFields) sparkpostFields.classList.remove('is-hidden');
                        break;
                    case 'amazon-ses':
                        if (amazonSesFields) amazonSesFields.classList.remove('is-hidden');
                        break;
                    case 'postmark':
                        if (postmarkFields) postmarkFields.classList.remove('is-hidden');
                        break;
                    case 'zeptomail-api':
                        if (zeptomailApiFields) zeptomailApiFields.classList.remove('is-hidden');
                        break;
                }
            }
        },

        onFormSubmit(e) {
            if (!this.selectedProvider) {
                e.preventDefault();
                alert('Please select a delivery server provider.');
                return;
            }
        }
    }"
    @open-delivery-server-drawer.window="openDrawer()"
>
    <style>
        .provider-grid-group {
            display: contents;
        }

        .provider-grid-group.is-hidden {
            display: none;
        }
    </style>

    <!-- Sidebar Drawer for Adding Delivery Server -->
    <template x-teleport="body">
        <div
            x-cloak
            x-show="drawerOpen"
            class="fixed inset-0 z-[100]"
            @keydown.window.escape="closeDrawer()"
        >
            <div
                x-show="drawerOpen"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="absolute inset-0 bg-slate-900/30 backdrop-blur-sm"
                @click="closeDrawer()"
            ></div>

            <div
                x-show="drawerOpen"
                x-transition:enter="transform transition ease-out duration-300"
                x-transition:enter-start="translate-x-full opacity-0"
                x-transition:enter-end="translate-x-0 opacity-100"
                x-transition:leave="transform transition ease-in duration-250"
                x-transition:leave-start="translate-x-0 opacity-100"
                x-transition:leave-end="translate-x-full opacity-0"
                class="absolute inset-y-0 right-0 w-full max-w-lg bg-white dark:bg-gray-900 border-l border-gray-200 dark:border-gray-800 shadow-2xl will-change-transform"
            >
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                    <div class="text-base font-semibold text-gray-900 dark:text-gray-100">Add Delivery Server</div>
                    <button type="button" class="p-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800" @click="closeDrawer()" aria-label="Close">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="h-[calc(100%-64px)] overflow-y-auto p-5">
                    <form id="delivery-server-form" method="POST" action="{{ route('customer.delivery-servers.store') }}" class="space-y-6" @submit="onFormSubmit($event)">
                        @csrf

                    <!-- Provider Dropdown Selection -->
                    <div>
                        <label for="provider-select" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Provider</label>
                        <select
                            id="provider-select"
                            x-model="selectedProvider"
                            @change="onProviderChange()"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                        >
                            <option value="">Choose a provider...</option>
                            <optgroup label="Standard SMTP">
                                @if($canUseExtendedMailboxProviders ?? false)
                                    <option value="gmail">Gmail (SMTP with App Password)</option>
                                    <option value="outlook">Outlook (SMTP with App Password)</option>
                                @endif
                                <option value="smtp">Other SMTP (Custom SMTP server)</option>
                            </optgroup>
                            <optgroup label="API Integrations">
                                <option value="mailgun">Mailgun (API Key & Domain)</option>
                                <option value="sendgrid">SendGrid (API Key)</option>
                                <option value="sparkpost">SparkPost (API Key)</option>
                                <option value="amazon-ses">Amazon SES (Access Key & Secret)</option>
                                <option value="postmark">Postmark (API Token)</option>
                                <option value="zeptomail-api">ZeptoMail API (Send Mail Token)</option>
                            </optgroup>
                        </select>
                    </div>

                <!-- Configuration Form Section -->
                <div id="config-section" class="hidden">
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Configure <span id="selected-provider-name"></span> Server</h3>
                        
                        <input type="hidden" name="type" id="selected-type">
                        <input type="hidden" name="flow" id="selected-flow">

                        <div id="gmail-help" class="hidden mb-4 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900 dark:border-blue-900/40 dark:bg-blue-900/10 dark:text-blue-100">
                            <div class="font-semibold mb-2">Gmail setup instructions</div>
                            <ol class="list-decimal space-y-1 pl-5">
                                <li>Sign in to your Google account and enable 2-Step Verification.</li>
                                <li>Open Google Account settings and go to Security.</li>
                                <li>Under App passwords, create a new app password for Mail.</li>
                                <li>Use your full Gmail address as Username.</li>
                                <li>Paste the generated app password into the Password field here.</li>
                            </ol>
                            <a href="https://support.google.com/accounts/answer/185833" target="_blank" rel="noopener noreferrer" class="mt-2 inline-block font-medium underline text-blue-700 dark:text-blue-300">Open Google App Password instructions</a>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <!-- Common Fields -->
                            <div>
                                <label for="server-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Server Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" id="server-name" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="server-status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                                <select name="status" id="server-status" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                    <option value="pending">Pending</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>

                            <!-- SMTP Fields (Hidden by default) -->
                            <div id="smtp-fields" class="provider-grid-group is-hidden">
                                <div>
                                    <label for="smtp-hostname" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Hostname <span class="text-red-500">*</span></label>
                                    <input type="text" name="hostname" id="smtp-hostname" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                </div>
                                <div>
                                    <label for="smtp-port" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Port <span class="text-red-500">*</span></label>
                                    <input type="number" name="port" id="smtp-port" value="587" min="1" max="65535" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                </div>
                                <div>
                                    <label for="smtp-username" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Username <span class="text-red-500">*</span></label>
                                    <input type="text" name="username" id="smtp-username" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                </div>
                                <div>
                                    <label for="smtp-password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password <span class="text-red-500">*</span></label>
                                    <input type="password" name="password" id="smtp-password" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                </div>
                                <div>
                                    <label for="smtp-encryption" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Encryption</label>
                                    <select name="encryption" id="smtp-encryption" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                        <option value="tls">TLS</option>
                                        <option value="ssl">SSL</option>
                                        <option value="none">None</option>
                                    </select>
                                </div>
                            </div>

                            <!-- API Provider Fields (Hidden by default) -->
                            <div id="api-fields" class="provider-grid-group is-hidden">
                                <!-- Mailgun Fields -->
                                <div id="mailgun-fields" class="provider-grid-group is-hidden">
                                    <div>
                                        <label for="mailgun-domain" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sending Domain <span class="text-red-500">*</span></label>
                                        <input type="text" name="settings[domain]" id="mailgun-domain" placeholder="mg.yourdomain.com" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                    </div>
                                    <div>
                                        <label for="mailgun-api-key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">API Key <span class="text-red-500">*</span></label>
                                        <input type="password" name="settings[secret]" id="mailgun-api-key" placeholder="key-xxxxxxxxxxxxxxxxxxxx" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                    </div>
                                </div>

                                <!-- SendGrid Fields -->
                                <div id="sendgrid-fields" class="provider-grid-group is-hidden">
                                    <div>
                                        <label for="sendgrid-api-key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">API Key <span class="text-red-500">*</span></label>
                                        <input type="password" name="settings[api_key]" id="sendgrid-api-key" placeholder="SG.xxxxx" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                    </div>
                                </div>

                                <!-- SparkPost Fields -->
                                <div id="sparkpost-fields" class="provider-grid-group is-hidden">
                                    <div>
                                        <label for="sparkpost-api-key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">API Key <span class="text-red-500">*</span></label>
                                        <input type="password" name="settings[secret]" id="sparkpost-api-key" placeholder="xxxxxxxxxxxxxxxxxxxx" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                    </div>
                                </div>

                                <!-- Amazon SES Fields -->
                                <div id="amazon-ses-fields" class="provider-grid-group is-hidden">
                                    <div>
                                        <label for="ses-access-key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Access Key ID <span class="text-red-500">*</span></label>
                                        <input type="text" name="settings[key]" id="ses-access-key" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                    </div>
                                    <div>
                                        <label for="ses-secret-key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Secret Access Key <span class="text-red-500">*</span></label>
                                        <input type="password" name="settings[secret]" id="ses-secret-key" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                    </div>
                                    <div>
                                        <label for="ses-region" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Region</label>
                                        <select name="settings[region]" id="ses-region" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                            <option value="us-east-1">US East (N. Virginia)</option>
                                            <option value="us-west-2">US West (Oregon)</option>
                                            <option value="eu-west-1">EU (Ireland)</option>
                                            <option value="ap-southeast-1">Asia Pacific (Singapore)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="ses-from-email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">From Email <span class="text-red-500">*</span></label>
                                        <input type="email" name="from_email" id="ses-from-email" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                    </div>
                                </div>

                                <!-- Postmark Fields -->
                                <div id="postmark-fields" class="provider-grid-group is-hidden">
                                    <div>
                                        <label for="postmark-token" class="block text-sm font-medium text-gray-700 dark:text-gray-300">API Token <span class="text-red-500">*</span></label>
                                        <input type="password" name="settings[token]" id="postmark-token" placeholder="pm-xxxxxxxxxxxxxxxxxxxx" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                    </div>
                                </div>

                                <!-- ZeptoMail API Fields -->
                                <div id="zeptomail-api-fields" class="provider-grid-group is-hidden">
                                    <div>
                                        <label for="zeptomail-token" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Send Mail Token <span class="text-red-500">*</span></label>
                                        <input type="password" name="settings[send_mail_token]" id="zeptomail-token" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                    </div>
                                    <div>
                                        <label for="zeptomail-mode" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mode</label>
                                        <select name="settings[mode]" id="zeptomail-mode" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                            <option value="raw">Raw HTML/Text</option>
                                            <option value="template">Template</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Common Additional Fields -->
                            <div>
                                <label for="from-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">From Name</label>
                                <input type="text" name="from_name" id="from-name" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                            </div>

                            <div>
                                <label for="daily-quota" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Daily Quota (0 = unlimited)</label>
                                <input type="number" name="daily_quota" id="daily-quota" value="0" min="0" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                            </div>

                            <div class="sm:col-span-2">
                                <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                                <textarea name="notes" id="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"></textarea>
                            </div>
 
                            <div class="sm:col-span-2">
                                <div class="flex items-center space-x-6">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="use_for" value="1" checked class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Use for campaigns</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="use_for_transactional" value="1" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Use for transactional</span>
                                    </label>
                                </div>
                            </div>

                            <div class="sm:col-span-2">
                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                                    <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Also use this server as</div>
                                    <div class="mt-3 flex items-center space-x-6">
                                        <label class="flex items-center">
                                            <input type="checkbox" name="use_as_reply_server" value="1" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700">
                                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Use as reply server</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="use_as_bounce_server" value="1" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700">
                                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Use as bounce server</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" @click="closeDrawer()" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600">
                        Cancel
                    </button>
                    <button type="submit" id="save-server-btn" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                        Save Server
                    </button>
                </div>
            </form>
                </div>
            </div>
        </div>
    </template>

    <x-modal name="pro-feature-extended-mailbox-providers" maxWidth="md">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Extended License Feature</h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                Gmail and Outlook mailbox providers are available on the Extended License.
            </p>
            <div class="mt-6 flex justify-end">
                <x-button type="button" variant="secondary" @click="$dispatch('close-modal', 'pro-feature-extended-mailbox-providers')">Close</x-button>
            </div>
        </div>
    </x-modal>

    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Owner</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Hostname</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($deliveryServers as $server)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ $server->name }}{{ $server->customer_id ? '' : ' (System)' }}
                            </td>
                            <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $server->customer_id ? 'Mine' : 'System' }}
                            </td>
                            <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $server->type }}
                            </td>
                            <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $server->hostname ?? '—' }}
                            </td>
                            <td class="px-6 py-2 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $server->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ ucfirst($server->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-2 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <x-button href="{{ route('customer.delivery-servers.show', $server) }}" variant="table" size="action" :pill="true" class="p-2" title="View" aria-label="View"><x-lucide name="eye" class="h-4 w-4" /><span class="sr-only">View</span></x-button>
                                    @if($server->customer_id)
                                        @customercan('servers.permissions.can_edit_delivery_servers')
                                            <x-button href="{{ route('customer.delivery-servers.edit', $server) }}" variant="table" size="action" :pill="true" class="p-2" title="Edit" aria-label="Edit"><x-lucide name="pencil" class="h-4 w-4" /><span class="sr-only">Edit</span></x-button>
                                        @endcustomercan

                                        @customercan('servers.permissions.can_delete_delivery_servers')
                                            <form method="POST" action="{{ route('customer.delivery-servers.destroy', $server) }}" class="inline" onsubmit="return confirm('Are you sure?');">
                                                @csrf
                                                @method('DELETE')
                                                <x-button type="submit" variant="table-danger" size="action" :pill="true" class="p-2" title="Delete" aria-label="Delete"><x-lucide name="trash-2" class="h-4 w-4" /><span class="sr-only">Delete</span></x-button>
                                            </form>
                                        @endcustomercan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                No delivery servers found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($deliveryServers->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">{{ $deliveryServers->links() }}</div>
        @endif
    </x-card>
</div>
@endsection
