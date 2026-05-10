@php
    $tz    = $campaign->getSetting('timezone', 'UTC');
    $days  = $campaign->getSetting('send_days', ['mon','tue','wed','thu','fri']);
    $start = $campaign->getSetting('send_hours_start', '09:00');
    $end   = $campaign->getSetting('send_hours_end', '17:30');
    $timeBlocks = $campaign->getSetting('send_time_blocks', [
        ['start' => $start, 'end' => $end],
    ]);
    $maxPd = $campaign->getSetting('max_per_day', 150);
    $delay = $campaign->getSetting('min_delay_minutes', 5);
    $trackOpens  = $campaign->getSetting('track_opens', true);
    $trackClicks = $campaign->getSetting('track_clicks', false);

    $dayMap = ['mon' => 'M', 'tue' => 'T', 'wed' => 'W', 'thu' => 'T', 'fri' => 'F', 'sat' => 'S', 'sun' => 'S'];

    $timeOptions = [];
    for ($h = 0; $h < 24; $h++) {
        foreach ([0, 30] as $m) {
            $val  = sprintf('%02d:%02d', $h, $m);
            $ampm = $h < 12 ? 'AM' : 'PM';
            $h12  = $h % 12 === 0 ? 12 : $h % 12;
            $timeOptions[$val] = sprintf('%02d:%02d %s', $h12, $m, $ampm);
        }
    }
@endphp

<form method="POST" action="{{ route('customer.outreach.campaigns.schedule.update', $campaign) }}" id="save-form-schedule"
    x-data="scheduleForm()" class="space-y-5">
    @csrf

    {{-- Sending Schedule --}}
    <div class="bg-white dark:bg-admin-card border border-gray-100 dark:border-admin-border rounded-xl divide-y divide-gray-100 dark:divide-admin-border">
        <div class="px-6 py-4">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-admin-text-primary">{{ __('Sending Schedule') }}</h2>
            <p class="text-xs text-gray-500 dark:text-admin-text-secondary mt-0.5">{{ __('Define when your emails should be sent to prospects.') }}</p>
        </div>

        {{-- Timezone --}}
        <div class="px-6 py-5 flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="sm:w-56 flex-shrink-0">
                <label class="text-sm font-medium text-gray-700 dark:text-admin-text-primary">{{ __('Timezone') }}</label>
            </div>
            <div class="flex-1">
                <select name="timezone" class="w-full sm:w-72 appearance-none px-3 py-2.5 text-sm border border-gray-200 dark:border-admin-border bg-white dark:bg-white/5 text-gray-900 dark:text-admin-text-primary rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1E5FEA]">
                    @foreach(timezone_identifiers_list() as $tzId)
                        <option value="{{ $tzId }}" {{ $tz === $tzId ? 'selected' : '' }}>{{ $tzId }}</option>
                    @endforeach
                </select>
                <p class="mt-1.5 text-xs text-gray-400 dark:text-admin-text-secondary">{{ __('All times below are based on this timezone.') }}</p>
            </div>
        </div>

        {{-- Days to send --}}
        <div class="px-6 py-5 flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="sm:w-56 flex-shrink-0">
                <label class="text-sm font-medium text-gray-700 dark:text-admin-text-primary">{{ __('Days to send') }}</label>
            </div>
            <div class="flex items-center gap-2">
                @foreach($dayMap as $key => $label)
                    <label class="cursor-pointer">
                        <input type="checkbox" name="send_days[]" value="{{ $key }}" {{ in_array($key, $days) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-semibold border-2 transition-colors
                            peer-checked:bg-primary-600 peer-checked:border-primary-600 peer-checked:text-white
                            border-gray-200 dark:border-admin-border text-gray-500 dark:text-admin-text-secondary
                            hover:border-[#1E5FEA] hover:text-[#1E5FEA]">
                            {{ $label }}
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Sending Hours --}}
        <div class="px-6 py-5 flex flex-col sm:flex-row sm:items-start gap-4">
            <div class="sm:w-56 flex-shrink-0 pt-2">
                <label class="text-sm font-medium text-gray-700 dark:text-admin-text-primary">{{ __('Sending hours') }}</label>
            </div>
            <div class="space-y-3">
                <template x-for="(block, index) in timeBlocks" :key="block.id">
                    <div class="flex items-center gap-2">
                        <input type="hidden" :name="`send_time_blocks[${index}][start]`" :value="block.start">
                        <input type="hidden" :name="`send_time_blocks[${index}][end]`" :value="block.end">

                        <select x-model="block.start" :name="index === 0 ? 'send_hours_start' : null" class="appearance-none px-3 py-2 pr-8 text-sm border border-gray-200 dark:border-admin-border bg-white dark:bg-white/5 text-gray-700 dark:text-admin-text-primary rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1E5FEA]">
                            @foreach($timeOptions as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <span class="text-sm text-gray-500 dark:text-admin-text-secondary">{{ __('to') }}</span>
                        <select x-model="block.end" :name="index === 0 ? 'send_hours_end' : null" class="appearance-none px-3 py-2 pr-8 text-sm border border-gray-200 dark:border-admin-border bg-white dark:bg-white/5 text-gray-700 dark:text-admin-text-primary rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1E5FEA]">
                            @foreach($timeOptions as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <button type="button" @click="removeTimeBlock(index)" :disabled="timeBlocks.length === 1" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-red-500 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:text-gray-400 disabled:hover:bg-transparent">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </template>
                <button type="button" @click="addTimeBlock()" class="inline-flex items-center gap-1.5 text-sm text-[#1E5FEA] hover:text-blue-700 font-medium transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ __('Add Time Block') }}
                </button>
            </div>
        </div>
    </div>

    {{-- Sending Limits & Behavior --}}
    <div class="bg-white dark:bg-admin-card border border-gray-100 dark:border-admin-border rounded-xl divide-y divide-gray-100 dark:divide-admin-border">
        <div class="px-6 py-4">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-admin-text-primary">{{ __('Sending Limits & Behavior') }}</h2>
            <p class="text-xs text-gray-500 dark:text-admin-text-secondary mt-0.5">{{ __('Control the volume and frequency of your campaign to protect deliverability.') }}</p>
        </div>

        {{-- Max per day --}}
        <div class="px-6 py-5 flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="flex-1">
                <label class="text-sm font-medium text-gray-700 dark:text-admin-text-primary">{{ __('Max emails per day') }}</label>
                <p class="text-xs text-gray-400 dark:text-admin-text-secondary mt-0.5">{{ __('Maximum number of emails to send daily for this campaign.') }}</p>
            </div>
            <input type="number" name="max_per_day" value="{{ $maxPd }}" min="1" max="5000"
                class="w-24 px-3 py-2 text-sm text-right border border-gray-200 dark:border-admin-border bg-white dark:bg-white/5 text-gray-900 dark:text-admin-text-primary rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1E5FEA]">
        </div>

        {{-- Minimum delay --}}
        <div class="px-6 py-5 flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="flex-1">
                <label class="text-sm font-medium text-gray-700 dark:text-admin-text-primary">{{ __('Minimum delay between emails') }}</label>
                <p class="text-xs text-gray-400 dark:text-admin-text-secondary mt-0.5">{{ __('Wait time between sending consecutive emails to mimic human behavior.') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <input type="number" name="min_delay_minutes" value="{{ $delay }}" min="1" max="120"
                    class="w-20 px-3 py-2 text-sm text-right border border-gray-200 dark:border-admin-border bg-white dark:bg-white/5 text-gray-900 dark:text-admin-text-primary rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1E5FEA]">
                <span class="text-sm text-gray-500 dark:text-admin-text-secondary">{{ __('minutes') }}</span>
            </div>
        </div>

        {{-- Track opens --}}
        <div class="px-6 py-5 flex items-center justify-between">
            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-admin-text-primary">{{ __('Track email opens') }}</label>
                <p class="text-xs text-gray-400 dark:text-admin-text-secondary mt-0.5">{{ __('Embed a tracking pixel to see when recipients open your emails.') }}</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                <input type="checkbox" name="track_opens" value="1" {{ $trackOpens ? 'checked' : '' }} class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 dark:bg-white/10 rounded-full peer peer-checked:bg-[#1E5FEA] transition-colors after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
            </label>
        </div>

        {{-- Track clicks --}}
        <div class="px-6 py-5 flex items-center justify-between">
            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-admin-text-primary">{{ __('Track link clicks') }}</label>
                <p class="text-xs text-gray-400 dark:text-admin-text-secondary mt-0.5">{{ __('Wrap links to track when recipients click them. (May affect deliverability slightly)') }}</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                <input type="checkbox" name="track_clicks" value="1" {{ $trackClicks ? 'checked' : '' }} class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 dark:bg-white/10 rounded-full peer peer-checked:bg-[#1E5FEA] transition-colors after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
            </label>
        </div>
    </div>
</form>

@push('scripts')
<script>
function scheduleForm() {
    return {
        timeBlocks: (@json($timeBlocks)).map((block, index) => ({
            id: index + 1,
            start: block.start || '09:00',
            end: block.end || '17:30',
        })),
        nextBlockId: ((@json($timeBlocks)).length || 0) + 1,

        addTimeBlock() {
            const lastBlock = this.timeBlocks[this.timeBlocks.length - 1] || { start: '09:00', end: '17:30' };

            this.timeBlocks.push({
                id: this.nextBlockId++,
                start: lastBlock.start,
                end: lastBlock.end,
            });
        },

        removeTimeBlock(index) {
            if (this.timeBlocks.length === 1) {
                return;
            }

            this.timeBlocks.splice(index, 1);
        },
    };
}
</script>
@endpush
