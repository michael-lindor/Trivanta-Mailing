@extends('layouts.customer')

@section('title', __('API'))
@section('page-title', __('API'))

@section('page-actions')
    <div class="flex w-full justify-end">
        <x-button href="{{ route('api.docs.public') }}" variant="secondary" target="_blank" rel="noopener" class="w-full sm:w-auto">{{ __('API Docs') }}</x-button>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    @if(session('plain_text_token'))
        <x-card>
            <div class="space-y-2">
                <div class="text-sm font-medium text-admin-text-primary">{{ __('New API Key') }}</div>
                <div class="text-xs text-admin-text-secondary">{{ __('Copy this token now. You will not be able to see it again.') }}</div>
                <div class="rounded-lg border border-admin-border bg-white/5 p-3 font-mono text-sm break-all">{{ session('plain_text_token') }}</div>
            </div>
        </x-card>
    @endif

    <x-card>
        <form method="POST" action="{{ route('customer.api.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm text-admin-text-secondary mb-1">{{ __('Name') }}</label>
                <input
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    class="w-full rounded-md border border-admin-border bg-white/5 text-admin-text-primary"
                    placeholder="{{ __('e.g. Zapier Integration') }}"
                    required
                />
                @error('name')
                    <div class="mt-1 text-sm text-red-500">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label class="block text-sm text-admin-text-secondary mb-1">{{ __('Abilities (optional)') }}</label>
                @php
                    $selectedAbilities = collect(old('abilities', ['*']))
                        ->filter(fn ($ability) => is_string($ability) && trim($ability) !== '')
                        ->map(fn ($ability) => trim($ability))
                        ->values()
                        ->all();
                @endphp
                <select
                    name="abilities[]"
                    multiple
                    size="12"
                    class="w-full rounded-md border border-admin-border bg-white/5 text-admin-text-primary"
                >
                    @foreach(($availableAbilityGroups ?? []) as $group)
                        <optgroup label="{{ $group['label'] }}">
                            @foreach(($group['options'] ?? []) as $value => $label)
                                <option value="{{ $value }}" {{ in_array($value, $selectedAbilities, true) ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                <div class="mt-1 text-xs text-admin-text-secondary">
                    {{ __('Hold Cmd/Ctrl to select multiple abilities. Leave only Full access selected to allow everything.') }}
                </div>
                @error('abilities')
                    <div class="mt-1 text-sm text-red-500">{{ $message }}</div>
                @enderror
                @error('abilities.*')
                    <div class="mt-1 text-sm text-red-500">{{ $message }}</div>
                @enderror
            </div>

            <div class="flex items-center justify-end">
                <x-button type="submit" variant="primary">{{ __('Create API Key') }}</x-button>
            </div>
        </form>
    </x-card>

    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Name') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Last used') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($tokens as $token)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $token->name }}</td>
                            <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                {{ $token->last_used_at ? $token->last_used_at->diffForHumans() : __('Never') }}
                            </td>
                            <td class="px-6 py-2 whitespace-nowrap text-right text-sm font-medium">
                                <form method="POST" action="{{ route('customer.api.destroy', $token->id) }}" class="inline" onsubmit="return confirm(@json(__('Revoke this API key?')));">
                                    @csrf
                                    @method('DELETE')
                                    <x-button type="submit" variant="table-danger" size="action" :pill="true">
                                        <x-lucide name="trash-2" class="h-4 w-4" />
                                        <span class="sr-only">{{ __('Revoke') }}</span>
                                    </x-button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('No API keys yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</div>
@endsection
