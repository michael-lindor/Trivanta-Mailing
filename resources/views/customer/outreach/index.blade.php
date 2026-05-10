@extends('layouts.customer')

@section('title', __('Outreach'))
@section('page-title', __('Outreach'))

@section('page-header')
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="min-w-0">
            <nav aria-label="Breadcrumb" class="mb-0">
                <ol class="flex flex-wrap items-center gap-1.5 text-[12px] text-admin-text-secondary">
                    <li>
                        <a href="{{ route('customer.dashboard') }}" class="font-medium transition hover:text-admin-text-primary">
                            Dashboard
                        </a>
                    </li>
                    <li aria-hidden="true" class="text-admin-text-secondary/60">/</li>
                    <li class="font-medium text-admin-text-primary">{{ __('Outreach') }}</li>
                </ol>
            </nav>
            <div class="flex flex-wrap items-center gap-3 min-w-0">
                <h1 class="text-[22px] font-semibold tracking-tight text-admin-text-primary">{{ __('Outreach') }}</h1>
            </div>
        </div>

        <div class="flex w-full flex-col gap-3 lg:w-auto lg:items-end">
            <button
                type="button"
                x-on:click="$dispatch('open-outreach-create')"
                class="inline-flex w-full sm:w-auto items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-[#1E5FEA] hover:bg-blue-700 rounded-lg transition-colors shadow-sm"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('New Campaign') }}
            </button>
        </div>
    </div>
@endsection

@section('content')
<div class="space-y-6" x-data="{ showCreate: false }" x-on:open-outreach-create.window="showCreate = true">

    @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-sm text-green-800 dark:text-green-300">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Campaign List --}}
    @if($campaigns->isEmpty())
        <div class="flex flex-col items-center justify-center py-20 bg-white dark:bg-admin-card border border-gray-100 dark:border-admin-border rounded-xl text-center">
            <div class="w-14 h-14 bg-blue-50 dark:bg-blue-900/20 rounded-2xl flex items-center justify-center mb-4">
                <svg class="w-7 h-7 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-admin-text-primary">{{ __('No campaigns yet') }}</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-admin-text-secondary max-w-xs">{{ __('Create your first outreach campaign to start connecting with prospects.') }}</p>
            <button type="button" @click="showCreate = true" class="mt-4 px-4 py-2 text-sm font-semibold text-white bg-[#1E5FEA] hover:bg-blue-700 rounded-lg transition-colors">
                {{ __('Create Campaign') }}
            </button>
        </div>
    @else
        <div class="bg-white dark:bg-admin-card border border-gray-100 dark:border-admin-border rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-admin-border bg-gray-50/50 dark:bg-white/2">
                        <th class="text-left text-xs font-medium text-gray-500 dark:text-admin-text-secondary px-5 py-3">{{ __('Campaign') }}</th>
                        <th class="text-left text-xs font-medium text-gray-500 dark:text-admin-text-secondary px-4 py-3">{{ __('Status') }}</th>
                        <th class="text-left text-xs font-medium text-gray-500 dark:text-admin-text-secondary px-4 py-3">{{ __('Leads') }}</th>
                        <th class="text-left text-xs font-medium text-gray-500 dark:text-admin-text-secondary px-4 py-3">{{ __('Created') }}</th>
                        <th class="text-right text-xs font-medium text-gray-500 dark:text-admin-text-secondary px-5 py-3">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-admin-border">
                    @foreach($campaigns as $c)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/2 transition-colors">
                            <td class="px-5 py-3.5">
                                <a href="{{ route('customer.outreach.campaigns.show', $c) }}" class="font-medium text-gray-900 dark:text-admin-text-primary hover:text-[#1E5FEA] transition-colors">{{ $c->name }}</a>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full border {{ $c->status_color }}">
                                    @if($c->status === 'active')<span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>@endif
                                    {{ ucfirst($c->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-gray-600 dark:text-admin-text-secondary">{{ number_format($c->leads_count) }}</td>
                            <td class="px-4 py-3.5 text-gray-500 dark:text-admin-text-secondary">{{ $c->created_at->format('M j, Y') }}</td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('customer.outreach.campaigns.show', $c) }}"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 dark:border-admin-border bg-white dark:bg-white/5 text-gray-700 dark:text-admin-text-primary shadow-sm transition-colors hover:bg-gray-50 dark:hover:bg-white/10"
                                        title="{{ __('View') }}"
                                        aria-label="{{ __('View') }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye-icon lucide-eye"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg>
                                    </a>
                                    <a href="{{ route('customer.outreach.campaigns.show', [$c, 'tab' => 'options']) }}"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 dark:border-admin-border bg-white dark:bg-white/5 text-gray-700 dark:text-admin-text-primary shadow-sm transition-colors hover:bg-gray-50 dark:hover:bg-white/10"
                                        title="{{ __('Edit') }}"
                                        aria-label="{{ __('Edit') }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pen-line-icon lucide-pen-line"><path d="M13 21h8"/><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('customer.outreach.campaigns.duplicate', $c) }}" class="inline-flex">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 dark:border-admin-border bg-white dark:bg-white/5 text-gray-700 dark:text-admin-text-primary shadow-sm transition-colors hover:bg-gray-50 dark:hover:bg-white/10"
                                            title="{{ __('Duplicate') }}"
                                            aria-label="{{ __('Duplicate') }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-copy-icon lucide-copy"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('customer.outreach.campaigns.destroy', $c) }}" class="inline-flex" onsubmit="return confirm('{{ __('Delete this campaign?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-[#ef4444] text-white shadow-sm transition-colors hover:bg-red-600"
                                            title="{{ __('Delete') }}"
                                            aria-label="{{ __('Delete') }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash2-icon lucide-trash-2"><path d="M10 11v6"/><path d="M14 11v6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Create Campaign Modal --}}
    <div
        x-cloak x-show="showCreate"
        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
        @click.self="showCreate = false"
    >
        <div x-show="showCreate"
            x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            class="bg-white dark:bg-admin-card rounded-2xl shadow-xl w-full max-w-md p-6 space-y-5"
        >
            <div class="flex items-center justify-between">
                <h2 class="text-base font-semibold text-gray-900 dark:text-admin-text-primary">{{ __('New Outreach Campaign') }}</h2>
                <button type="button" @click="showCreate = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('customer.outreach.campaigns.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-admin-text-primary mb-1.5">{{ __('Campaign Name') }}</label>
                    <input type="text" name="name" required placeholder="{{ __('e.g. Q1 B2B Outreach') }}"
                        class="w-full px-3 py-2.5 text-sm border border-gray-200 dark:border-admin-border bg-white dark:bg-white/5 text-gray-900 dark:text-admin-text-primary rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1E5FEA] focus:border-transparent placeholder-gray-400">
                </div>
                <div class="flex gap-3 pt-1">
                    <button type="submit" class="flex-1 py-2.5 text-sm font-semibold text-white bg-[#1E5FEA] hover:bg-blue-700 rounded-lg transition-colors">{{ __('Create Campaign') }}</button>
                    <button type="button" @click="showCreate = false" class="flex-1 py-2.5 text-sm font-medium text-gray-700 dark:text-admin-text-primary border border-gray-200 dark:border-admin-border bg-white dark:bg-white/5 hover:bg-gray-50 dark:hover:bg-white/10 rounded-lg transition-colors">{{ __('Cancel') }}</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
