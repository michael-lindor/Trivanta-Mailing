@extends('layouts.customer')

@section('title', 'Create Email Warmup')
@section('page-title', 'Create Email Warmup')

@section('content')
<style>
    .warmup-step {
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        background: #fff;
        padding: 28px;
    }

    .warmup-step-number {
        width: 40px;
        height: 40px;
        border-radius: 9999px;
        background: #6457e6;
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
    }

    .warmup-soft-panel {
        background: #eef2ff;
        border: 1px solid #d8e0ff;
        border-radius: 12px;
    }

    .warmup-option {
        display: block;
        width: 100%;
        border: 1px solid #d1d5db;
        border-radius: 12px;
        padding: 18px;
        cursor: pointer;
        position: relative;
        transition: all .2s ease;
    }

    .warmup-option.active {
        border-color: #665be8;
        box-shadow: 0 0 0 1px #665be8 inset;
        background: #f5f3ff;
    }

    .warmup-recommended {
        position: absolute;
        top: -10px;
        right: 14px;
        background: #665be8;
        color: #fff;
        font-size: 12px;
        line-height: 1;
        padding: 5px 9px;
        border-radius: 9999px;
        font-weight: 600;
    }

    .warmup-chart {
        height: 220px;
        display: flex;
        align-items: flex-end;
        gap: 4px;
    }

    .warmup-chart-bar {
        flex: 1;
        min-width: 8px;
        border-radius: 4px 4px 0 0;
        background: linear-gradient(180deg, #7b74ee 0%, #6158df 100%);
    }

    .warmup-switch {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
    }

    .warmup-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .warmup-switch-slider {
        position: absolute;
        inset: 0;
        border-radius: 9999px;
        background: #d1d5db;
        transition: .2s;
    }

    .warmup-switch-slider:before {
        content: '';
        position: absolute;
        width: 18px;
        height: 18px;
        left: 3px;
        top: 3px;
        border-radius: 50%;
        background: #fff;
        transition: .2s;
    }

    .warmup-switch input:checked + .warmup-switch-slider {
        background: #6457e6;
    }

    .warmup-switch input:checked + .warmup-switch-slider:before {
        transform: translateX(20px);
    }

    .dark .warmup-step {
        background: #111827;
        border-color: #374151;
    }

    .dark .warmup-soft-panel {
        background: #1f2937;
        border-color: #374151;
    }

    .dark .warmup-option {
        border-color: #4b5563;
        background: #111827;
    }

    .dark .warmup-option.active {
        background: #1f2340;
        border-color: #7f78ef;
        box-shadow: 0 0 0 1px #7f78ef inset;
    }
</style>

<div class="mx-auto max-w-6xl">
    <form method="POST" action="{{ route('customer.warmups.store') }}" class="space-y-8" id="warmup-create-form">
        @csrf

        <section class="warmup-step space-y-6">
            <div class="flex items-center gap-4">
                <span class="warmup-step-number">1</span>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Inbox Setup</h2>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Warmup Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="New Domain Warmup" class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                    @error('name')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="delivery_server_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Delivery Server</label>
                    <select id="delivery_server_id" name="delivery_server_id" required class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                        <option value="">Select a delivery server</option>
                        @foreach($deliveryServers as $server)
                            <option value="{{ $server->id }}" {{ old('delivery_server_id') == $server->id ? 'selected' : '' }}>{{ $server->name }} - {{ strtoupper($server->type) }}</option>
                        @endforeach
                    </select>
                    @error('delivery_server_id')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="from_email" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">From Email</label>
                    <input type="email" name="from_email" id="from_email" value="{{ old('from_email') }}" required placeholder="hello@yourdomain.com" class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                    @error('from_email')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="from_name" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">From Name</label>
                    <input type="text" name="from_name" id="from_name" value="{{ old('from_name') }}" placeholder="Your Name or Company" class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                    @error('from_name')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="warmup-soft-panel p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Domain Authentication</h3>
                        <p class="mt-2 text-base text-gray-600 dark:text-gray-300">We check your DNS records to ensure high deliverability and avoid spam filters.</p>
                    </div>
                    <button id="check-domain-auth-btn" type="button" class="rounded-lg border border-gray-200 bg-white px-5 py-3 text-base font-semibold text-gray-800 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">Check DNS &amp; Auth</button>
                </div>
                <div class="mt-5 flex flex-wrap items-center gap-3 text-base font-semibold">
                    <span id="spf-status-badge" class="rounded-lg border border-gray-300 bg-gray-100 px-4 py-2 text-gray-700">SPF: Not checked</span>
                    <span id="dkim-status-badge" class="rounded-lg border border-gray-300 bg-gray-100 px-4 py-2 text-gray-700">DKIM: Not checked</span>
                    <span id="dmarc-status-badge" class="rounded-lg border border-gray-300 bg-gray-100 px-4 py-2 text-gray-700">DMARC: Not checked</span>
                </div>
                <p id="domain-auth-message" class="mt-3 text-sm text-gray-500 dark:text-gray-400">Select inbox details and click "Check DNS &amp; Auth" to verify your domain records.</p>
            </div>
        </section>

        <section class="warmup-step space-y-6">
            <div class="flex items-center gap-4">
                <span class="warmup-step-number">2</span>
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Warmup Strategy</h2>
                    <p class="mt-1 text-base text-gray-500 dark:text-gray-400">Select how aggressively you want to scale your sending volume.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3" id="strategy-options">
                <label class="warmup-option" data-strategy-card="safe" data-rate="1.15" data-start="10" data-max="80" data-days="21">
                    <input type="radio" name="warmup_strategy" value="safe" class="sr-only" {{ old('warmup_strategy', 'safe') === 'safe' ? 'checked' : '' }}>
                    <span class="warmup-recommended">Recommended</span>
                    <div class="text-xl font-semibold text-indigo-600">Safe Warmup</div>
                    <p class="mt-2 text-base text-gray-700 dark:text-gray-300">Starts slow, best for new domains.</p>
                </label>

                <label class="warmup-option" data-strategy-card="balanced" data-rate="1.25" data-start="20" data-max="250" data-days="30">
                    <input type="radio" name="warmup_strategy" value="balanced" class="sr-only" {{ old('warmup_strategy') === 'balanced' ? 'checked' : '' }}>
                    <div class="text-xl font-semibold text-gray-900 dark:text-gray-100">Balanced</div>
                    <p class="mt-2 text-base text-gray-600 dark:text-gray-300">Medium speed growth.</p>
                </label>

                <label class="warmup-option" data-strategy-card="aggressive" data-rate="1.4" data-start="40" data-max="500" data-days="30">
                    <input type="radio" name="warmup_strategy" value="aggressive" class="sr-only" {{ old('warmup_strategy') === 'aggressive' ? 'checked' : '' }}>
                    <div class="text-xl font-semibold text-gray-900 dark:text-gray-100">Aggressive</div>
                    <p class="mt-2 text-base text-gray-600 dark:text-gray-300">Faster scaling, higher risk.</p>
                </label>
            </div>

            <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
                <div>
                    <label for="starting_volume" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Start Volume <span class="text-gray-500">(emails/day)</span></label>
                    <input type="number" name="starting_volume" id="starting_volume" value="{{ old('starting_volume', 10) }}" min="1" max="100" required class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                    @error('starting_volume')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="max_volume" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Max Volume <span class="text-gray-500">(emails/day)</span></label>
                    <input type="number" name="max_volume" id="max_volume" value="{{ old('max_volume', 80) }}" min="10" max="10000" required class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                    @error('max_volume')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="total_days" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Duration <span class="text-gray-500">(days)</span></label>
                    <input type="number" name="total_days" id="total_days" value="{{ old('total_days', 21) }}" min="7" max="90" required class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                    @error('total_days')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <input type="hidden" name="daily_increase_rate" id="daily_increase_rate" value="{{ old('daily_increase_rate', 1.15) }}" required>
            @error('daily_increase_rate')<p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror

            <div class="warmup-soft-panel p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Growth Projection</h3>
                <div class="mt-5 warmup-chart" id="growth-projection-bars"></div>
                <div class="mt-4 flex items-center justify-between text-sm text-gray-600 dark:text-gray-300">
                    <span id="growth-day-1">Day 1 (10 emails)</span>
                    <span id="growth-last-day">Day 21 (80 emails)</span>
                </div>
            </div>
        </section>

        <section class="warmup-step space-y-6">
            <div class="flex items-center gap-4">
                <span class="warmup-step-number">3</span>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Warmup Network</h2>
            </div>

            <div class="space-y-3" id="network-options">
                <label class="warmup-option active" data-network-card="network">
                    <input type="radio" name="warmup_network" value="network" class="sr-only" {{ old('warmup_network', 'network') === 'network' ? 'checked' : '' }}>
                    <div class="flex items-center gap-2 text-lg font-semibold text-indigo-600">
                        <span>Use {{ \App\Models\Setting::get('app_name', config('app.name', 'MailPurse')) }} warmup network</span>
                        <span class="rounded-full bg-indigo-500 px-2 py-1 text-xs font-semibold text-white">Recommended</span>
                    </div>
                    <p class="mt-2 text-base text-gray-700 dark:text-gray-300">Leverage our network of 10,000+ real inboxes with high reputation to build your deliverability safely.</p>
                </label>

                <label class="warmup-option" data-network-card="seed">
                    <input type="radio" name="warmup_network" value="seed" class="sr-only" {{ old('warmup_network') === 'seed' ? 'checked' : '' }}>
                    <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">Use my own seed emails</div>
                    <p class="mt-2 text-base text-gray-600 dark:text-gray-300">Provide a custom list of email addresses to send warmup emails to instead of our network.</p>
                </label>
            </div>

            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                <div>
                    <label for="email_list_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Seed Email List (Optional)</label>
                    <select id="email_list_id" name="email_list_id" class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                        <option value="">Use manual seed emails instead</option>
                        @foreach($emailLists as $list)
                            <option value="{{ $list->id }}" {{ old('email_list_id') == $list->id ? 'selected' : '' }}>{{ $list->name }} ({{ number_format($list->subscribers_count ?? 0) }} subscribers)</option>
                        @endforeach
                    </select>
                    @error('email_list_id')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="send_time" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Daily Send Time</label>
                    <input type="time" name="send_time" id="send_time" value="{{ old('send_time', '09:00') }}" required class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                    @error('send_time')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <div id="seed-emails-wrap" class="{{ old('warmup_network') === 'seed' ? '' : 'hidden' }}">
                <label for="seed_emails" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Seed Email Addresses (one per line)</label>
                <textarea id="seed_emails" name="seed_emails" rows="4" placeholder="email1@example.com&#10;email2@example.com&#10;email3@example.com" class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">{{ old('seed_emails') }}</textarea>
                @error('seed_emails')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Human Behavior Simulation</h3>
                <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
                    <div class="flex items-center justify-between rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                        <div>
                            <p class="text-base font-semibold text-gray-900 dark:text-gray-100">Auto replies</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Automatically reply to warmup emails</p>
                        </div>
                        <label class="warmup-switch"><input type="checkbox" name="simulate_auto_replies" checked><span class="warmup-switch-slider"></span></label>
                    </div>

                    <div class="flex items-center justify-between rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                        <div>
                            <p class="text-base font-semibold text-gray-900 dark:text-gray-100">Mark as important</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Star and mark emails as important</p>
                        </div>
                        <label class="warmup-switch"><input type="checkbox" name="simulate_mark_important" checked><span class="warmup-switch-slider"></span></label>
                    </div>

                    <div class="flex items-center justify-between rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                        <div>
                            <p class="text-base font-semibold text-gray-900 dark:text-gray-100">Remove from spam</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Rescue emails that land in spam</p>
                        </div>
                        <label class="warmup-switch"><input type="checkbox" name="simulate_remove_spam" checked><span class="warmup-switch-slider"></span></label>
                    </div>

                    <div class="flex items-center justify-between rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                        <div>
                            <p class="text-base font-semibold text-gray-900 dark:text-gray-100">Random sending time</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Send at random intervals to look natural</p>
                        </div>
                        <label class="warmup-switch"><input type="checkbox" name="simulate_random_time" checked><span class="warmup-switch-slider"></span></label>
                    </div>
                </div>
            </div>

            <div>
                <label for="timezone" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Timezone</label>
                <select id="timezone" name="timezone" required class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                    @foreach(timezone_identifiers_list() as $tz)
                        <option value="{{ $tz }}" {{ old('timezone', 'UTC') === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                    @endforeach
                </select>
                @error('timezone')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
        </section>

        <section class="rounded-2xl border border-indigo-200 bg-indigo-50 p-7 dark:border-indigo-800 dark:bg-indigo-950/40">
            <h3 class="text-xl font-semibold text-indigo-700 dark:text-indigo-300">Summary &amp; Activation</h3>
            <div class="mt-5 rounded-xl border border-white/70 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                <div class="grid grid-cols-2 gap-4 text-sm lg:grid-cols-5">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Inbox</p>
                        <p id="summary-inbox" class="mt-1 font-semibold text-gray-900 dark:text-gray-100">{{ old('from_email', 'hello@domain.com') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Duration</p>
                        <p id="summary-duration" class="mt-1 font-semibold text-gray-900 dark:text-gray-100">{{ old('total_days', 21) }} days</p>
                    </div>
                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Daily Max</p>
                        <p id="summary-max" class="mt-1 font-semibold text-gray-900 dark:text-gray-100">{{ old('max_volume', 80) }} emails</p>
                    </div>
                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Strategy</p>
                        <p id="summary-strategy" class="mt-1 font-semibold text-gray-900 dark:text-gray-100">{{ ucfirst(old('warmup_strategy', 'safe')) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Est. Readiness</p>
                        <p class="mt-1 font-semibold text-green-600">High</p>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                <x-button href="{{ route('customer.warmups.index') }}" variant="secondary">Cancel</x-button>
                <x-button type="submit" variant="primary" class="sm:px-8">Start Warmup</x-button>
            </div>
        </section>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const strategyCards = Array.from(document.querySelectorAll('[data-strategy-card]'));
        const networkCards = Array.from(document.querySelectorAll('[data-network-card]'));
        const growthBarsEl = document.getElementById('growth-projection-bars');
        const day1Label = document.getElementById('growth-day-1');
        const lastDayLabel = document.getElementById('growth-last-day');

        const startInput = document.getElementById('starting_volume');
        const maxInput = document.getElementById('max_volume');
        const daysInput = document.getElementById('total_days');
        const rateInput = document.getElementById('daily_increase_rate');
        const fromEmailInput = document.getElementById('from_email');
        const summaryInbox = document.getElementById('summary-inbox');
        const summaryDuration = document.getElementById('summary-duration');
        const summaryMax = document.getElementById('summary-max');
        const summaryStrategy = document.getElementById('summary-strategy');
        const seedWrap = document.getElementById('seed-emails-wrap');
        const deliveryServerInput = document.getElementById('delivery_server_id');
        const checkDomainAuthBtn = document.getElementById('check-domain-auth-btn');
        const domainAuthMessage = document.getElementById('domain-auth-message');
        const spfStatusBadge = document.getElementById('spf-status-badge');
        const dkimStatusBadge = document.getElementById('dkim-status-badge');
        const dmarcStatusBadge = document.getElementById('dmarc-status-badge');

        function activeStrategyCard() {
            return strategyCards.find((card) => card.querySelector('input[type="radio"]').checked);
        }

        function activeNetworkCard() {
            return networkCards.find((card) => card.querySelector('input[type="radio"]').checked);
        }

        function setStrategyUI() {
            strategyCards.forEach((card) => {
                const checked = card.querySelector('input[type="radio"]').checked;
                card.classList.toggle('active', checked);
            });

            const selected = activeStrategyCard();
            summaryStrategy.textContent = selected ? selected.dataset.strategyCard.charAt(0).toUpperCase() + selected.dataset.strategyCard.slice(1) : 'Safe';
        }

        function setNetworkUI() {
            networkCards.forEach((card) => {
                card.classList.toggle('active', card.querySelector('input[type="radio"]').checked);
            });

            const selected = activeNetworkCard();
            seedWrap.classList.toggle('hidden', !selected || selected.dataset.networkCard !== 'seed');
        }

        function buildProjection() {
            const start = Math.max(1, parseInt(startInput.value || '10', 10));
            const max = Math.max(start, parseInt(maxInput.value || '80', 10));
            const days = Math.max(7, parseInt(daysInput.value || '21', 10));
            const rate = Math.max(1.05, parseFloat(rateInput.value || '1.15'));

            const points = [];
            let current = start;
            for (let i = 0; i < days; i += 1) {
                points.push(Math.min(max, Math.round(current)));
                current *= rate;
            }

            const highest = Math.max(...points, 1);
            growthBarsEl.innerHTML = '';
            points.forEach((value) => {
                const bar = document.createElement('div');
                bar.className = 'warmup-chart-bar';
                bar.style.height = `${Math.max(10, (value / highest) * 100)}%`;
                growthBarsEl.appendChild(bar);
            });

            day1Label.textContent = `Day 1 (${start} emails)`;
            lastDayLabel.textContent = `Day ${days} (${Math.min(max, points[points.length - 1] || max)} emails)`;
            summaryDuration.textContent = `${days} days`;
            summaryMax.textContent = `${max} emails`;
            summaryInbox.textContent = fromEmailInput.value || 'hello@domain.com';
        }

        function applyAuthBadge(el, label, status, extraText = '') {
            const classMap = {
                pass: 'rounded-lg border border-green-300 bg-green-100 px-4 py-2 text-green-800',
                missing: 'rounded-lg border border-yellow-300 bg-yellow-100 px-4 py-2 text-yellow-800',
                checking: 'rounded-lg border border-indigo-300 bg-indigo-100 px-4 py-2 text-indigo-700',
                error: 'rounded-lg border border-red-300 bg-red-100 px-4 py-2 text-red-700',
                idle: 'rounded-lg border border-gray-300 bg-gray-100 px-4 py-2 text-gray-700',
            };

            el.className = classMap[status] || classMap.idle;
            el.textContent = `${label}: ${extraText || (status === 'pass' ? 'Pass' : status === 'missing' ? 'Missing' : status === 'checking' ? 'Checking...' : status === 'error' ? 'Error' : 'Not checked')}`;
        }

        function resetAuthBadges() {
            applyAuthBadge(spfStatusBadge, 'SPF', 'idle');
            applyAuthBadge(dkimStatusBadge, 'DKIM', 'idle');
            applyAuthBadge(dmarcStatusBadge, 'DMARC', 'idle');
            domainAuthMessage.className = 'mt-3 text-sm text-gray-500 dark:text-gray-400';
            domainAuthMessage.textContent = 'Select inbox details and click "Check DNS & Auth" to verify your domain records.';
        }

        async function checkDomainAuth() {
            const fromEmail = (fromEmailInput.value || '').trim();
            const deliveryServerId = (deliveryServerInput.value || '').trim();

            if (!fromEmail) {
                domainAuthMessage.className = 'mt-3 text-sm text-red-600 dark:text-red-400';
                domainAuthMessage.textContent = 'Please enter a valid From Email first.';
                return;
            }

            applyAuthBadge(spfStatusBadge, 'SPF', 'checking');
            applyAuthBadge(dkimStatusBadge, 'DKIM', 'checking');
            applyAuthBadge(dmarcStatusBadge, 'DMARC', 'checking');

            const originalButtonText = checkDomainAuthBtn.textContent;
            checkDomainAuthBtn.disabled = true;
            checkDomainAuthBtn.textContent = 'Checking...';
            domainAuthMessage.className = 'mt-3 text-sm text-gray-500 dark:text-gray-400';
            domainAuthMessage.textContent = 'Checking DNS records...';

            try {
                const response = await fetch('{{ route('customer.warmups.check-domain-auth') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({
                        from_email: fromEmail,
                        delivery_server_id: deliveryServerId || null,
                    }),
                });

                const payload = await response.json();
                if (!response.ok) {
                    const errorMessage = payload?.message || payload?.errors?.from_email?.[0] || 'Unable to check domain authentication right now.';
                    throw new Error(errorMessage);
                }

                const checks = payload.checks || {};
                applyAuthBadge(spfStatusBadge, 'SPF', checks.spf?.status === 'pass' ? 'pass' : 'missing');

                const dkimStatus = checks.dkim?.status === 'pass' ? 'pass' : 'missing';
                const dkimText = dkimStatus === 'pass' && checks.dkim?.selector ? `Pass (${checks.dkim.selector})` : undefined;
                applyAuthBadge(dkimStatusBadge, 'DKIM', dkimStatus, dkimText);

                applyAuthBadge(dmarcStatusBadge, 'DMARC', checks.dmarc?.status === 'pass' ? 'pass' : 'missing');

                domainAuthMessage.className = 'mt-3 text-sm text-green-700 dark:text-green-400';
                domainAuthMessage.textContent = `Checked ${payload.domain}. DNS authentication status updated.`;
            } catch (error) {
                applyAuthBadge(spfStatusBadge, 'SPF', 'error');
                applyAuthBadge(dkimStatusBadge, 'DKIM', 'error');
                applyAuthBadge(dmarcStatusBadge, 'DMARC', 'error');
                domainAuthMessage.className = 'mt-3 text-sm text-red-600 dark:text-red-400';
                domainAuthMessage.textContent = error.message || 'Failed to check DNS records.';
            } finally {
                checkDomainAuthBtn.disabled = false;
                checkDomainAuthBtn.textContent = originalButtonText;
            }
        }

        strategyCards.forEach((card) => {
            card.addEventListener('click', function () {
                card.querySelector('input[type="radio"]').checked = true;
                startInput.value = card.dataset.start;
                maxInput.value = card.dataset.max;
                daysInput.value = card.dataset.days;
                rateInput.value = card.dataset.rate;
                setStrategyUI();
                buildProjection();
            });
        });

        networkCards.forEach((card) => {
            card.addEventListener('click', function () {
                card.querySelector('input[type="radio"]').checked = true;
                setNetworkUI();
            });
        });

        [startInput, maxInput, daysInput, rateInput, fromEmailInput].forEach((input) => {
            input.addEventListener('input', buildProjection);
            input.addEventListener('change', buildProjection);
        });

        fromEmailInput.addEventListener('change', resetAuthBadges);
        deliveryServerInput.addEventListener('change', resetAuthBadges);
        checkDomainAuthBtn.addEventListener('click', checkDomainAuth);

        setStrategyUI();
        setNetworkUI();
        buildProjection();
        resetAuthBadges();
    });
</script>
@endsection
