@php
    $statusOptions = ['pending', 'sent', 'opened', 'clicked', 'replied', 'bounced', 'unsubscribed'];
@endphp

{{-- Toolbar --}}
<div class="flex flex-col sm:flex-row sm:items-center gap-3">
    <form method="GET" action="{{ route('customer.outreach.campaigns.show', [$campaign, 'tab' => 'leads']) }}" class="flex-1 flex items-center gap-3">
        <div class="relative flex-1 max-w-sm">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <input type="text" name="search" value="{{ $searchQuery }}" placeholder="{{ __('Search leads by name, email, or company') }}"
                class="pl-10 pr-4 py-2 w-full text-sm border border-gray-200 dark:border-admin-border bg-white dark:bg-white/5 text-gray-900 dark:text-admin-text-primary rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1E5FEA] placeholder-gray-400">
        </div>
        <div class="relative">
            <select name="status" onchange="this.form.submit()"
                class="appearance-none pl-3 pr-8 py-2 text-sm border border-gray-200 dark:border-admin-border bg-white dark:bg-white/5 text-gray-700 dark:text-admin-text-primary rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1E5FEA]">
                <option value="">{{ __('All Statuses') }}</option>
                @foreach($statusOptions as $s)
                    <option value="{{ $s }}" {{ $statusFilter === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <svg class="absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </div>
    </form>
    <div class="flex items-center gap-2 flex-shrink-0">
        <a href="#" class="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-medium text-gray-700 dark:text-admin-text-primary border border-gray-200 dark:border-admin-border bg-white dark:bg-white/5 hover:bg-gray-50 dark:hover:bg-white/10 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            {{ __('Export') }}
        </a>
        <button type="button" x-data @click="$dispatch('open-add-leads')"
            class="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-semibold text-white bg-[#1E5FEA] hover:bg-blue-700 rounded-lg transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            {{ __('Add Leads') }}
        </button>
    </div>
</div>

{{-- Leads Table --}}
<div class="bg-white dark:bg-admin-card border border-gray-100 dark:border-admin-border rounded-xl overflow-hidden" x-data="{ showAddLeads: false }" @open-add-leads.window="showAddLeads = true">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 dark:border-admin-border bg-gray-50/50 dark:bg-white/2">
                <th class="w-10 px-4 py-3"><input type="checkbox" class="rounded border-gray-300"></th>
                <th class="text-left text-xs font-medium text-gray-500 dark:text-admin-text-secondary px-4 py-3">{{ __('Lead Info') }}</th>
                <th class="text-left text-xs font-medium text-gray-500 dark:text-admin-text-secondary px-4 py-3">{{ __('Company') }}</th>
                <th class="text-left text-xs font-medium text-gray-500 dark:text-admin-text-secondary px-4 py-3">{{ __('Status') }}</th>
                <th class="text-left text-xs font-medium text-gray-500 dark:text-admin-text-secondary px-4 py-3">{{ __('Last Activity') }}</th>
                <th class="text-right text-xs font-medium text-gray-500 dark:text-admin-text-secondary px-4 py-3">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50 dark:divide-admin-border">
            @forelse($leads as $lead)
                <tr class="hover:bg-gray-50/40 dark:hover:bg-white/2 transition-colors">
                    <td class="px-4 py-3.5"><input type="checkbox" class="rounded border-gray-300"></td>
                    <td class="px-4 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white text-xs font-semibold flex-shrink-0">
                                {{ $lead->initials }}
                            </div>
                            <div class="min-w-0">
                                <p class="font-medium text-gray-900 dark:text-admin-text-primary truncate">{{ $lead->full_name ?: '—' }}</p>
                                <p class="text-xs text-gray-400 dark:text-admin-text-secondary truncate">{{ $lead->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3.5 text-gray-600 dark:text-admin-text-secondary">{{ $lead->company ?: '—' }}</td>
                    <td class="px-4 py-3.5">
                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full border {{ $lead->status_color }}">
                            {{ ucfirst($lead->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-xs text-gray-500 dark:text-admin-text-secondary">
                        {{ $lead->last_activity_at ? $lead->last_activity_at->diffForHumans() : __('—') }}
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <button type="button" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-600 dark:hover:text-admin-text-primary rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </button>
                            <form method="POST" action="{{ route('customer.outreach.campaigns.leads.destroy', [$campaign, $lead]) }}" onsubmit="return confirm('{{ __('Remove this lead?') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-red-600 dark:hover:text-red-400 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        <div class="flex flex-col items-center justify-center gap-2 py-16">
                            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#bbb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-star-icon lucide-user-star"><path d="M16.051 12.616a1 1 0 0 1 1.909.024l.737 1.452a1 1 0 0 0 .737.535l1.634.256a1 1 0 0 1 .588 1.806l-1.172 1.168a1 1 0 0 0-.282.866l.259 1.613a1 1 0 0 1-1.541 1.134l-1.465-.75a1 1 0 0 0-.912 0l-1.465.75a1 1 0 0 1-1.539-1.133l.258-1.613a1 1 0 0 0-.282-.866l-1.156-1.153a1 1 0 0 1 .572-1.822l1.633-.256a1 1 0 0 0 .737-.535z"/><path d="M8 15H7a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/></svg>
                            <p class="text-sm text-gray-400 dark:text-admin-text-secondary">{{ __('No leads yet. Click "Add Leads" to import.') }}</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination --}}
    @if($leads->hasPages())
        <div class="px-5 py-3.5 border-t border-gray-100 dark:border-admin-border flex items-center justify-between">
            <p class="text-xs text-gray-500 dark:text-admin-text-secondary">
                {{ __('Showing') }} <strong>{{ $leads->firstItem() }}</strong> {{ __('to') }} <strong>{{ $leads->lastItem() }}</strong> {{ __('of') }} <strong>{{ number_format($leads->total()) }}</strong> {{ __('leads') }}
            </p>
            <div class="flex items-center gap-1">
                @if($leads->onFirstPage())
                    <span class="w-8 h-8 flex items-center justify-center text-gray-300 dark:text-admin-text-secondary rounded-lg border border-gray-200 dark:border-admin-border cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </span>
                @else
                    <a href="{{ $leads->previousPageUrl() }}" class="w-8 h-8 flex items-center justify-center text-gray-500 dark:text-admin-text-secondary hover:text-[#1E5FEA] rounded-lg border border-gray-200 dark:border-admin-border hover:border-[#1E5FEA] transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                @endif
                @foreach($leads->getUrlRange(max(1, $leads->currentPage() - 2), min($leads->lastPage(), $leads->currentPage() + 2)) as $page => $url)
                    <a href="{{ $url }}" class="w-8 h-8 flex items-center justify-center text-xs rounded-lg border transition-colors
                        {{ $page == $leads->currentPage() ? 'bg-[#1E5FEA] border-[#1E5FEA] text-white font-semibold' : 'border-gray-200 dark:border-admin-border text-gray-600 dark:text-admin-text-secondary hover:border-[#1E5FEA] hover:text-[#1E5FEA]' }}">
                        {{ $page }}
                    </a>
                @endforeach
                @if($leads->hasMorePages())
                    <a href="{{ $leads->nextPageUrl() }}" class="w-8 h-8 flex items-center justify-center text-gray-500 dark:text-admin-text-secondary hover:text-[#1E5FEA] rounded-lg border border-gray-200 dark:border-admin-border hover:border-[#1E5FEA] transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                @endif
            </div>
        </div>
    @endif

    {{-- Add Leads Modal --}}
    <div x-cloak x-show="showAddLeads"
        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
        @click.self="showAddLeads = false"
    >
        <div x-show="showAddLeads"
            x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            class="bg-white dark:bg-admin-card rounded-2xl shadow-xl w-full max-w-lg p-6 space-y-4"
        >
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-admin-text-primary">{{ __('Import Leads') }}</h2>
                    <p class="text-xs text-gray-500 dark:text-admin-text-secondary mt-0.5">{{ __('One per line: email, first_name, last_name, company') }}</p>
                </div>
                <button type="button" @click="showAddLeads = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('customer.outreach.campaigns.leads.update', $campaign) }}" id="save-form-leads" class="space-y-4">
                @csrf
                <div>
                    <textarea name="leads" rows="8" placeholder="john@example.com, John, Doe, Acme Corp&#10;jane@example.com, Jane, Smith, Globex"
                        class="w-full px-3 py-2.5 text-sm font-mono border border-gray-200 dark:border-admin-border bg-white dark:bg-white/5 text-gray-900 dark:text-admin-text-primary rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1E5FEA] placeholder-gray-400 resize-none"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-admin-text-secondary mb-2">{{ __('Import Mode') }}</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                            <input type="radio" name="leads_import_mode" value="append" checked class="text-[#1E5FEA]">
                            <span class="text-gray-700 dark:text-admin-text-primary">{{ __('Append to existing') }}</span>
                        </label>
                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                            <input type="radio" name="leads_import_mode" value="replace" class="text-[#1E5FEA]">
                            <span class="text-gray-700 dark:text-admin-text-primary">{{ __('Replace all leads') }}</span>
                        </label>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 py-2.5 text-sm font-semibold text-white bg-[#1E5FEA] hover:bg-blue-700 rounded-lg transition-colors">{{ __('Import Leads') }}</button>
                    <button type="button" @click="showAddLeads = false" class="flex-1 py-2.5 text-sm font-medium text-gray-700 dark:text-admin-text-primary border border-gray-200 dark:border-admin-border bg-white dark:bg-white/5 hover:bg-gray-50 dark:hover:bg-white/10 rounded-lg transition-colors">{{ __('Cancel') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
