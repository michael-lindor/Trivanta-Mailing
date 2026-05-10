@extends('layouts.admin')

@section('title', __('SuperScrape Settings'))

@section('page-header')
<div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div>
        <nav aria-label="Breadcrumb" class="mb-0">
            <ol class="flex flex-wrap items-center gap-1.5 text-[12px] text-admin-text-secondary">
                <li><a href="{{ route('admin.dashboard') }}" class="font-medium transition hover:text-admin-text-primary">{{ __('Dashboard') }}</a></li>
                <li aria-hidden="true" class="text-admin-text-secondary/60">/</li>
                <li><a href="{{ route('admin.addons.index') }}" class="font-medium transition hover:text-admin-text-primary">{{ __('Addons') }}</a></li>
                <li aria-hidden="true" class="text-admin-text-secondary/60">/</li>
                <li class="font-medium text-admin-text-primary">SuperScrape</li>
            </ol>
        </nav>
        <h1 class="text-[22px] font-semibold tracking-tight text-admin-text-primary mt-1">{{ __('SuperScrape — Settings') }}</h1>
        <p class="text-sm text-admin-text-secondary mt-0.5">{{ __('Configure API keys and credit limits for Google lead scraping.') }}</p>
    </div>
</div>
@endsection

@section('content')
<div class="max-w-2xl space-y-6">

    @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-sm text-green-800 dark:text-green-300">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.super-scrape.settings.update') }}">
        @csrf
        @method('PUT')

        {{-- API Keys --}}
        <div class="bg-white dark:bg-admin-card border border-gray-100 dark:border-admin-border rounded-xl p-6 space-y-5">
            <div class="pb-4 border-b border-gray-100 dark:border-admin-border">
                <h2 class="text-sm font-semibold text-admin-text-primary">{{ __('API Keys') }}</h2>
                <p class="text-xs text-admin-text-secondary mt-0.5">{{ __('Free tiers available at serpapi.com and serper.dev') }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-admin-text-primary mb-1.5">
                    SerpAPI Key
                    <span class="ml-1 text-xs font-normal text-admin-text-secondary">(Maps, Places, Reviews, Images)</span>
                </label>
                <div class="relative">
                    <input type="password" name="superscrape_serpapi_key" id="serpapi_key"
                        value="{{ old('superscrape_serpapi_key', $serpApiKey) }}"
                        placeholder="Enter SerpAPI key"
                        class="w-full px-3 py-2.5 pr-10 text-sm border @error('superscrape_serpapi_key') border-red-400 @else border-gray-200 dark:border-admin-border @enderror rounded-lg bg-white dark:bg-admin-main text-admin-text-primary placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#1E5FEA]/30 focus:border-[#1E5FEA]">
                    <button type="button" onclick="toggleVisibility('serpapi_key', this)" class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
                @error('superscrape_serpapi_key')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
                <p class="mt-1.5 text-xs text-admin-text-secondary">
                    Get a free key at <a href="https://serpapi.com/manage-api-key" target="_blank" class="text-[#1E5FEA] hover:underline">serpapi.com</a> — 100 free searches/month.
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-admin-text-primary mb-1.5">
                    Serper.dev Key
                    <span class="ml-1 text-xs font-normal text-admin-text-secondary">(Google News)</span>
                </label>
                <div class="relative">
                    <input type="password" name="superscrape_serper_key" id="serper_key"
                        value="{{ old('superscrape_serper_key', $serperKey) }}"
                        placeholder="Enter Serper.dev key"
                        class="w-full px-3 py-2.5 pr-10 text-sm border @error('superscrape_serper_key') border-red-400 @else border-gray-200 dark:border-admin-border @enderror rounded-lg bg-white dark:bg-admin-main text-admin-text-primary placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#1E5FEA]/30 focus:border-[#1E5FEA]">
                    <button type="button" onclick="toggleVisibility('serper_key', this)" class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
                @error('superscrape_serper_key')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
                <p class="mt-1.5 text-xs text-admin-text-secondary">
                    Get a free key at <a href="https://serper.dev/api-key" target="_blank" class="text-[#1E5FEA] hover:underline">serper.dev</a> — 2,500 free queries/month.
                </p>
            </div>
        </div>

        {{-- Credit Limits --}}
        <div class="bg-white dark:bg-admin-card border border-gray-100 dark:border-admin-border rounded-xl p-6 space-y-5 mt-5">
            <div class="pb-4 border-b border-gray-100 dark:border-admin-border">
                <h2 class="text-sm font-semibold text-admin-text-primary">{{ __('Credit Limits') }}</h2>
                <p class="text-xs text-admin-text-secondary mt-0.5">{{ __('1 credit = 10 records. Resets monthly per customer.') }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-admin-text-primary mb-1.5">
                    {{ __('Default Monthly Credits per Customer') }}
                </label>
                <input type="number" name="superscrape_monthly_credits" min="1" max="100000"
                    value="{{ old('superscrape_monthly_credits', $monthlyCredits) }}"
                    class="w-full px-3 py-2.5 text-sm border border-gray-200 dark:border-admin-border rounded-lg bg-white dark:bg-admin-main text-admin-text-primary focus:outline-none focus:ring-2 focus:ring-[#1E5FEA]/30 focus:border-[#1E5FEA]">
                @error('superscrape_monthly_credits')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
                <p class="mt-1.5 text-xs text-admin-text-secondary">{{ __('e.g. 500 credits = 5,000 records/month per customer') }}</p>
            </div>
        </div>

        <div class="mt-5 flex justify-end">
            <button type="submit"
                class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-[#1E5FEA] hover:bg-blue-700 rounded-lg transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ __('Save Settings') }}
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function toggleVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
@endpush
@endsection
