<div class="space-y-4">
    <div class="bg-white dark:bg-admin-card border border-gray-100 dark:border-admin-border rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-admin-border flex items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-admin-text-primary">{{ __('Campaign Logs') }}</h2>
                <p class="text-xs text-gray-500 dark:text-admin-text-secondary mt-0.5">{{ __('Recent pause or failure reasons appear here so you can understand why the campaign stopped or could not start.') }}</p>
            </div>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full border {{ $campaign->status_color }}">
                {{ ucfirst($campaign->status) }}
            </span>
        </div>

        <div class="p-5 space-y-4">
            @if(!empty($statusInsights['issues']))
                <div class="rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 p-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <div class="min-w-0">
                            <h3 class="text-sm font-semibold text-amber-800 dark:text-amber-300">{{ __('Setup issues to review') }}</h3>
                            <div class="mt-2 space-y-2">
                                @foreach($statusInsights['issues'] as $issue)
                                    <div class="text-sm text-amber-700 dark:text-amber-200">{{ $issue }}</div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if(!empty($statusInsights['logs']))
                <div class="space-y-3">
                    @foreach($statusInsights['logs'] as $log)
                        <div class="rounded-xl border border-gray-200 dark:border-admin-border p-4 bg-gray-50/60 dark:bg-white/5">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full border {{ $log['badge'] }}">{{ $log['title'] }}</span>
                                        @if(!empty($log['created_at']))
                                            <span class="text-xs text-gray-400 dark:text-admin-text-secondary">{{ \Carbon\Carbon::parse($log['created_at'])->diffForHumans() }}</span>
                                        @endif
                                    </div>
                                    <p class="mt-2 text-sm text-gray-700 dark:text-admin-text-primary">{{ $log['message'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if(empty($statusInsights['issues']) && empty($statusInsights['logs']))
                <div class="rounded-xl border border-dashed border-gray-200 dark:border-admin-border p-8 text-center bg-white dark:bg-white/5">
                    <div class="mx-auto w-11 h-11 rounded-full bg-gray-100 dark:bg-white/10 flex items-center justify-center mb-3">
                        <svg class="w-5 h-5 text-gray-400 dark:text-admin-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M7 8h10M7 3h5a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"/></svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-admin-text-primary">{{ __('No logs yet') }}</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-admin-text-secondary">{{ __('Pause, start, or failure details will appear here when campaign activity is recorded.') }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
