@extends('layouts.customer')

@section('title', 'Add Delivery Server')
@section('page-title', 'Add Delivery Server')

@section('content')
<div class="max-w-4xl">
    <x-card title="Add Delivery Server">
        <form method="POST" action="{{ route('customer.delivery-servers.store') }}" class="space-y-6">
            @csrf

            <input type="hidden" name="flow" value="{{ $flow }}">
            <input type="hidden" name="type" value="{{ $type }}">

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Server Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    @error('name')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Server Type</label>
                    <div class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100">
                        {{ $type ? ucfirst(str_replace('-', ' ', $type)) : 'Select a type' }}
                    </div>
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                    <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <!-- SMTP Fields (for Gmail, Outlook, Generic SMTP) -->
                @if(in_array($type, ['smtp', 'gmail', 'outlook']))
                    <div>
                        <label for="hostname" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Hostname <span class="text-red-500">*</span></label>
                        <input type="text" name="hostname" id="hostname" value="{{ old('hostname', $type == 'gmail' ? 'smtp.gmail.com' : ($type == 'outlook' ? 'smtp.office365.com' : '')) }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        @error('hostname')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="port" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Port <span class="text-red-500">*</span></label>
                        <input type="number" name="port" id="port" value="{{ old('port', 587) }}" min="1" max="65535" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        @error('port')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Username <span class="text-red-500">*</span></label>
                        <input type="text" name="username" id="username" value="{{ old('username') }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        @error('username')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password" id="password" value="{{ old('password') }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        @error('password')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="encryption" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Encryption</label>
                        <select name="encryption" id="encryption" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                            <option value="tls" {{ old('encryption', 'tls') == 'tls' ? 'selected' : '' }}>TLS</option>
                            <option value="ssl" {{ old('encryption') == 'ssl' ? 'selected' : '' }}>SSL</option>
                            <option value="none" {{ old('encryption') == 'none' ? 'selected' : '' }}>None</option>
                        </select>
                    </div>

                    @if(in_array($type, ['gmail', 'outlook']))
                        <div class="sm:col-span-2 rounded-lg border border-blue-200 dark:border-blue-800 p-4 bg-blue-50 dark:bg-blue-900/20">
                            <div class="text-sm text-blue-800 dark:text-blue-200">
                                <strong>Note:</strong> For {{ $type == 'gmail' ? 'Gmail' : 'Outlook' }}, you need to use an App Password. 
                                <a href="{{ $type == 'gmail' ? 'https://support.google.com/accounts/answer/185833' : 'https://support.microsoft.com/en-us/account-billing/using-app-passwords-with-apps-that-don-t-support-two-step-verification-5896ed9b-4263-e681-128a-a6f2979a7944' }}" target="_blank" class="underline">Learn how to generate one</a>.
                            </div>
                        </div>
                    @endif
                @endif

                <!-- API Provider Fields -->
                @if($type == 'mailgun')
                    <div>
                        <label for="settings_domain" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mailgun Domain <span class="text-red-500">*</span></label>
                        <input type="text" name="settings[domain]" id="settings_domain" value="{{ old('settings.domain') }}" placeholder="mg.yourdomain.com" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        @error('settings.domain')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="settings_secret" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mailgun API Key <span class="text-red-500">*</span></label>
                        <input type="password" name="settings[secret]" id="settings_secret" value="{{ old('settings.secret') }}" placeholder="key-xxxxxxxxxxxxxxxxxxxx" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        @error('settings.secret')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                @endif

                @if($type == 'sendgrid')
                    <div class="sm:col-span-2">
                        <label for="settings_api_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">SendGrid API Key <span class="text-red-500">*</span></label>
                        <input type="password" name="settings[api_key]" id="settings_api_key" value="{{ old('settings.api_key') }}" placeholder="SG.xxxxx" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        @error('settings.api_key')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                @endif

                @if($type == 'postmark')
                    <div class="sm:col-span-2">
                        <label for="settings_token" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Postmark API Token <span class="text-red-500">*</span></label>
                        <input type="password" name="settings[token]" id="settings_token" value="{{ old('settings.token') }}" placeholder="pm-xxxxxxxxxxxxxxxxxxxx" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        @error('settings.token')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                @endif

                @if($type == 'sparkpost')
                    <div class="sm:col-span-2">
                        <label for="settings_secret" class="block text-sm font-medium text-gray-700 dark:text-gray-300">SparkPost API Key <span class="text-red-500">*</span></label>
                        <input type="password" name="settings[secret]" id="settings_secret" value="{{ old('settings.secret') }}" placeholder="xxxxxxxxxxxxxxxxxxxx" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        @error('settings.secret')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                @endif

                @if($type == 'amazon-ses')
                    <div>
                        <label for="settings_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Access Key ID <span class="text-red-500">*</span></label>
                        <input type="text" name="settings[key]" id="settings_key" value="{{ old('settings.key') }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        @error('settings.key')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="settings_secret" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Secret Access Key <span class="text-red-500">*</span></label>
                        <input type="password" name="settings[secret]" id="settings_secret" value="{{ old('settings.secret') }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        @error('settings.secret')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="settings_region" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Region</label>
                        <select name="settings[region]" id="settings_region" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                            <option value="us-east-1" {{ old('settings.region', 'us-east-1') == 'us-east-1' ? 'selected' : '' }}>US East (N. Virginia)</option>
                            <option value="us-west-2" {{ old('settings.region') == 'us-west-2' ? 'selected' : '' }}>US West (Oregon)</option>
                            <option value="eu-west-1" {{ old('settings.region') == 'eu-west-1' ? 'selected' : '' }}>EU (Ireland)</option>
                            <option value="ap-southeast-1" {{ old('settings.region') == 'ap-southeast-1' ? 'selected' : '' }}>Asia Pacific (Singapore)</option>
                        </select>
                    </div>

                    <div>
                        <label for="from_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">From Email <span class="text-red-500">*</span></label>
                        <input type="email" name="from_email" id="from_email" value="{{ old('from_email') }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        @error('from_email')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                @endif

                @if($type == 'zeptomail-api')
                    <div class="sm:col-span-2">
                        <label for="settings_send_mail_token" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Send Mail Token <span class="text-red-500">*</span></label>
                        <input type="password" name="settings[send_mail_token]" id="settings_send_mail_token" value="{{ old('settings.send_mail_token') }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        @error('settings.send_mail_token')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="settings_mode" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mode</label>
                        <select name="settings[mode]" id="settings_mode" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                            <option value="raw" {{ old('settings.mode', 'raw') == 'raw' ? 'selected' : '' }}>Raw HTML/Text</option>
                            <option value="template" {{ old('settings.mode') == 'template' ? 'selected' : '' }}>Template</option>
                        </select>
                    </div>

                    <div>
                        <label for="settings_template_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Template Key</label>
                        <input type="text" name="settings[template_key]" id="settings_template_key" value="{{ old('settings.template_key') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    </div>

                    <div>
                        <label for="settings_template_alias" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Template Alias</label>
                        <input type="text" name="settings[template_alias]" id="settings_template_alias" value="{{ old('settings.template_alias') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    </div>

                    <div>
                        <label for="settings_bounce_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bounce Address</label>
                        <input type="email" name="settings[bounce_address]" id="settings_bounce_address" value="{{ old('settings.bounce_address') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    </div>
                @endif

                <!-- Common fields for all types -->
                @if(!in_array($type, ['amazon-ses']))
                    <div>
                        <label for="from_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">From Email</label>
                        <input type="email" name="from_email" id="from_email" value="{{ old('from_email') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        @error('from_email')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                @endif

                <div>
                    <label for="bounce_server_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bounce Server</label>
                    <select name="bounce_server_id" id="bounce_server_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        <option value="">None</option>
                        @foreach($bounceServers as $bounceServer)
                            <option value="{{ $bounceServer->id }}" {{ (string) old('bounce_server_id') === (string) $bounceServer->id ? 'selected' : '' }}>
                                {{ $bounceServer->name }} ({{ $bounceServer->username }})
                            </option>
                        @endforeach
                    </select>
                    @error('bounce_server_id')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="from_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">From Name</label>
                    <input type="text" name="from_name" id="from_name" value="{{ old('from_name') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    @error('from_name')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="daily_quota" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Daily Quota (0 = unlimited)</label>
                    <input type="number" name="daily_quota" id="daily_quota" value="{{ old('daily_quota', 0) }}" min="0" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    @error('daily_quota')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                    <textarea name="notes" id="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">{{ old('notes') }}</textarea>
                    @error('notes')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-2">
                    <div class="flex items-center space-x-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="use_for" value="1" {{ old('use_for', true) ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Use for campaigns</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="use_for_transactional" value="1" {{ old('use_for_transactional') ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Use for transactional</span>
                        </label>
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                        <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Also use this server as</div>
                        <div class="mt-3 flex items-center space-x-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="use_as_reply_server" value="1" {{ old('use_as_reply_server') ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Use as reply server</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="use_as_bounce_server" value="1" {{ old('use_as_bounce_server') ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Use as bounce server</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <x-button href="{{ route('customer.delivery-servers.index') }}" variant="secondary">Cancel</x-button>
                @customercan('servers.permissions.can_create_delivery_servers')
                    <x-button type="submit" variant="primary">Create Server</x-button>
                @endcustomercan
            </div>
        </form>
    </x-card>
</div>
@endsection
