@php
    $fromEmail = $deliveryServer->from_email ?? null;
    $sendingDomain = $fromEmail ? substr(strrchr($fromEmail, '@'), 1) : null;
    $serverHostname = $deliveryServer->hostname ?? null;
    $serverType = $deliveryServer->type ?? 'smtp';
    $isSmtpType = in_array($serverType, ['smtp', 'gmail', 'outlook', 'zeptomail', 'mailjet']);
    $spfValue = 'v=spf1 ' . ($serverHostname ? 'include:' . $serverHostname . ' ' : '') . 'a:' . ($sendingDomain ?? '') . ' ~all';
    $spfMergeExample = 'v=spf1 include:_spf.google.com ' . ($serverHostname ? 'include:' . $serverHostname . ' ' : '') . 'a:' . ($sendingDomain ?? '') . ' ~all';

    $deliveryLogs = $deliveryLogs ?? collect();
    $bounceLogs = $bounceLogs ?? collect();
    $failedRecipients = $failedRecipients ?? collect();

    $recentErrors = $deliveryLogs->where('status', 'failed');
    $recentSuccesses = $deliveryLogs->where('status', 'success');
    $hasErrors = $recentErrors->isNotEmpty() || $bounceLogs->isNotEmpty() || $failedRecipients->isNotEmpty();

    $categoryLabels = [
        'spf_fail' => 'SPF Authentication Failed',
        'dkim_fail' => 'DKIM Authentication Failed',
        'dmarc_fail' => 'DMARC Policy Failed',
        'auth_fail' => 'Sender Authentication Failed',
        'tls_error' => 'TLS/SSL Certificate Error',
        'connection' => 'Connection Error',
        'quota' => 'Quota / Rate Limit',
        'reputation' => 'IP/Domain Reputation',
        'content' => 'Content Filtering',
        'unknown' => 'Other Error',
    ];

    $categoryColors = [
        'spf_fail' => 'red',
        'dkim_fail' => 'red',
        'dmarc_fail' => 'red',
        'auth_fail' => 'red',
        'tls_error' => 'orange',
        'connection' => 'yellow',
        'quota' => 'yellow',
        'reputation' => 'red',
        'content' => 'orange',
        'unknown' => 'gray',
    ];
@endphp

{{-- ==================== DELIVERY STATUS & ERRORS ==================== --}}
@if($deliveryLogs->isNotEmpty() || $bounceLogs->isNotEmpty() || $failedRecipients->isNotEmpty())
<x-card title="{{ __('Delivery Status & Errors') }}">
    {{-- Summary counters --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 mb-6">
        <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg text-center">
            <div class="text-2xl font-bold text-green-700 dark:text-green-300">{{ $recentSuccesses->count() }}</div>
            <div class="text-xs text-green-600 dark:text-green-400">{{ __('Sent OK') }}</div>
        </div>
        <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-lg text-center">
            <div class="text-2xl font-bold text-red-700 dark:text-red-300">{{ $recentErrors->count() }}</div>
            <div class="text-xs text-red-600 dark:text-red-400">{{ __('Send Failures') }}</div>
        </div>
        <div class="p-3 bg-orange-50 dark:bg-orange-900/20 rounded-lg text-center">
            <div class="text-2xl font-bold text-orange-700 dark:text-orange-300">{{ $bounceLogs->count() }}</div>
            <div class="text-xs text-orange-600 dark:text-orange-400">{{ __('Bounces') }}</div>
        </div>
        <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg text-center">
            <div class="text-2xl font-bold text-yellow-700 dark:text-yellow-300">{{ $failedRecipients->count() }}</div>
            <div class="text-xs text-yellow-600 dark:text-yellow-400">{{ __('Campaign Failures') }}</div>
        </div>
    </div>

    {{-- Recent send errors with fix suggestions --}}
    @if($recentErrors->isNotEmpty())
        <div class="mb-6">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" /></svg>
                {{ __('Recent Send Errors') }}
            </h4>
            <div class="space-y-3">
                @foreach($recentErrors->take(10) as $log)
                    @php
                        $cat = $log->error_category ?? 'unknown';
                        $color = $categoryColors[$cat] ?? 'gray';
                        $fixSuggestion = \App\Models\DeliveryServerLog::getFixSuggestion($cat, $sendingDomain, $serverHostname);
                    @endphp
                    <div class="border border-{{ $color }}-200 dark:border-{{ $color }}-800 rounded-lg overflow-hidden">
                        <div class="px-4 py-3 bg-{{ $color }}-50 dark:bg-{{ $color }}-900/20 flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-{{ $color }}-100 dark:bg-{{ $color }}-900/40 text-{{ $color }}-800 dark:text-{{ $color }}-200">
                                        {{ $categoryLabels[$cat] ?? __('Error') }}
                                    </span>
                                    @if($log->error_code)
                                        <code class="text-xs text-gray-500 dark:text-gray-400">{{ $log->error_code }}</code>
                                    @endif
                                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ $log->created_at->diffForHumans() }}</span>
                                </div>
                                @if($log->to_email)
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('To:') }} {{ $log->to_email }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="px-4 py-3 space-y-2">
                            <div class="p-2 bg-gray-50 dark:bg-gray-800 rounded text-xs font-mono text-gray-700 dark:text-gray-300 break-all max-h-24 overflow-y-auto">
                                {{ $log->error_message }}
                            </div>
                            <div class="p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                <div class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd" /></svg>
                                    <div>
                                        <span class="text-xs font-semibold text-blue-800 dark:text-blue-200">{{ __('How to fix:') }}</span>
                                        <p class="text-xs text-blue-700 dark:text-blue-300 mt-1">{{ $fixSuggestion }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Bounce logs --}}
    @if($bounceLogs->isNotEmpty())
        <div class="mb-6">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-orange-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                {{ __('Recent Bounces') }}
            </h4>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="py-2 pr-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Email') }}</th>
                            <th class="py-2 pr-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Type') }}</th>
                            <th class="py-2 pr-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Code') }}</th>
                            <th class="py-2 pr-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Reason') }}</th>
                            <th class="py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Date') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($bounceLogs->take(10) as $bounce)
                            <tr>
                                <td class="py-2 pr-4 text-gray-900 dark:text-gray-100">{{ $bounce->email }}</td>
                                <td class="py-2 pr-4">
                                    <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full {{ $bounce->bounce_type === 'hard' ? 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-200' }}">
                                        {{ ucfirst($bounce->bounce_type) }}
                                    </span>
                                </td>
                                <td class="py-2 pr-4 text-gray-500 dark:text-gray-400 font-mono text-xs">{{ $bounce->bounce_code ?? '-' }}</td>
                                <td class="py-2 pr-4 text-gray-600 dark:text-gray-400 text-xs max-w-xs truncate" title="{{ $bounce->reason ?? $bounce->diagnostic_code }}">{{ Str::limit($bounce->reason ?? $bounce->diagnostic_code ?? '-', 80) }}</td>
                                <td class="py-2 text-gray-400 dark:text-gray-500 text-xs whitespace-nowrap">{{ $bounce->bounced_at ? $bounce->bounced_at->diffForHumans() : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Campaign failed recipients --}}
    @if($failedRecipients->isNotEmpty())
        <div>
            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" /></svg>
                {{ __('Campaign Delivery Failures') }}
            </h4>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="py-2 pr-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Email') }}</th>
                            <th class="py-2 pr-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Status') }}</th>
                            <th class="py-2 pr-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Reason') }}</th>
                            <th class="py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Date') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($failedRecipients->take(10) as $recipient)
                            <tr>
                                <td class="py-2 pr-4 text-gray-900 dark:text-gray-100">{{ $recipient->email }}</td>
                                <td class="py-2 pr-4">
                                    <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full {{ $recipient->status === 'bounced' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-200' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200' }}">
                                        {{ ucfirst($recipient->status) }}
                                    </span>
                                </td>
                                <td class="py-2 pr-4 text-gray-600 dark:text-gray-400 text-xs max-w-xs truncate" title="{{ $recipient->failure_reason }}">{{ Str::limit($recipient->failure_reason ?? '-', 80) }}</td>
                                <td class="py-2 text-gray-400 dark:text-gray-500 text-xs whitespace-nowrap">{{ ($recipient->failed_at ?? $recipient->bounced_at)?->diffForHumans() ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- All activity log --}}
    @if($recentSuccesses->isNotEmpty() && $recentErrors->isEmpty())
        <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg flex items-center gap-3">
            <svg class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
            <div>
                <p class="text-sm font-medium text-green-800 dark:text-green-200">{{ __('All recent test emails sent successfully') }}</p>
                <p class="text-xs text-green-600 dark:text-green-400 mt-0.5">{{ __('Last sent:') }} {{ $recentSuccesses->first()->created_at->diffForHumans() }}</p>
            </div>
        </div>
    @endif
</x-card>
@endif

{{-- ==================== DOMAIN AUTHENTICATION GUIDE (collapsible) ==================== --}}
@if($isSmtpType && $sendingDomain)
<x-card>
    <div x-data="{ open: {{ $hasErrors ? 'true' : 'false' }} }">
        <button @click="open = !open" type="button" class="w-full flex items-center justify-between text-left">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                </svg>
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ __('Domain Authentication Guide') }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('SPF, DKIM & DMARC setup for') }} <strong>{{ $sendingDomain }}</strong></p>
                </div>
            </div>
            <svg :class="open ? 'rotate-180' : ''" class="w-5 h-5 text-gray-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
            </svg>
        </button>

        <div x-show="open" x-collapse x-cloak class="mt-5 space-y-6">
            <!-- Step 1: SPF -->
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 text-xs font-bold">1</span>
                        {{ __('SPF (Sender Policy Framework)') }}
                    </h4>
                </div>
                <div class="p-4 space-y-3">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('SPF tells receiving mail servers which IP addresses are allowed to send email on behalf of your domain. Add a TXT record:') }}
                    </p>
                    <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg space-y-2">
                        <div>
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Host:') }}</span>
                            <code class="ml-2 text-sm text-gray-900 dark:text-gray-100">{{ $sendingDomain }}</code>
                            <span class="text-xs text-gray-400">({{ __('or') }} <code>@</code>)</span>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('TXT Value:') }}</span>
                            <code class="block mt-1 p-2 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded text-sm text-gray-900 dark:text-gray-100 break-all select-all">{{ $spfValue }}</code>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400"><strong>{{ __('Tip:') }}</strong> {{ __('Merge into existing SPF record if one already exists. Only ONE SPF record per domain.') }}</p>
                </div>
            </div>

            <!-- Step 2: DKIM -->
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 text-xs font-bold">2</span>
                        {{ __('DKIM (DomainKeys Identified Mail)') }}
                    </h4>
                </div>
                <div class="p-4 space-y-3">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Get the DKIM public key from your SMTP provider') }}@if($serverHostname) (<code>{{ $serverHostname }}</code>)@endif {{ __('and add a TXT record:') }}
                    </p>
                    <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg space-y-2">
                        <div>
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Host:') }}</span>
                            <code class="ml-2 text-sm text-gray-900 dark:text-gray-100">default._domainkey.{{ $sendingDomain }}</code>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('TXT Value:') }}</span>
                            <code class="ml-2 text-sm text-gray-500 dark:text-gray-400 italic">{{ __('(provided by your SMTP provider)') }}</code>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 3: DMARC -->
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 text-xs font-bold">3</span>
                        {{ __('DMARC') }}
                    </h4>
                </div>
                <div class="p-4 space-y-3">
                    <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg space-y-2">
                        <div>
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Host:') }}</span>
                            <code class="ml-2 text-sm text-gray-900 dark:text-gray-100">_dmarc.{{ $sendingDomain }}</code>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('TXT Value:') }}</span>
                            <code class="block mt-1 p-2 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded text-sm text-gray-900 dark:text-gray-100 break-all select-all">v=DMARC1; p=none; rua=mailto:dmarc-reports@{{ $sendingDomain }}</code>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Verify -->
            <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg space-y-2">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Verify with') }} <a href="https://mxtoolbox.com/SuperTool.aspx" target="_blank" rel="noopener" class="text-primary-600 dark:text-primary-400 hover:underline">MXToolbox</a> {{ __('or terminal:') }}</h4>
                <code class="block p-2 bg-gray-900 text-green-400 rounded text-xs select-all">dig TXT {{ $sendingDomain }} +short</code>
                <code class="block p-2 bg-gray-900 text-green-400 rounded text-xs select-all">dig TXT default._domainkey.{{ $sendingDomain }} +short</code>
                <code class="block p-2 bg-gray-900 text-green-400 rounded text-xs select-all">dig TXT _dmarc.{{ $sendingDomain }} +short</code>
            </div>
        </div>
    </div>
</x-card>
@endif
