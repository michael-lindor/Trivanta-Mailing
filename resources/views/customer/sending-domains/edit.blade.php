@extends('layouts.customer')

@section('title', 'Edit Sending Domain')
@section('page-title', 'Edit Sending Domain')

@section('content')
<div class="max-w-2xl">
    <nav aria-label="Breadcrumb" class="mb-6">
        <ol class="flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
            <li><a href="{{ route('customer.dashboard') }}" class="font-medium text-gray-600 transition hover:text-primary-600 dark:text-gray-300 dark:hover:text-primary-400">{{ __('Home') }}</a></li>
            <li aria-hidden="true">/</li>
            <li><a href="{{ route('customer.sending-domains.index') }}" class="font-medium text-gray-600 transition hover:text-primary-600 dark:text-gray-300 dark:hover:text-primary-400">{{ __('Sending Domains') }}</a></li>
            <li aria-hidden="true">/</li>
            <li><a href="{{ route('customer.sending-domains.show', $sendingDomain) }}" class="font-medium text-gray-600 transition hover:text-primary-600 dark:text-gray-300 dark:hover:text-primary-400">{{ $sendingDomain->domain }}</a></li>
            <li aria-hidden="true">/</li>
            <li class="text-gray-900 dark:text-gray-100">{{ __('Edit') }}</li>
        </ol>
    </nav>
    <x-card title="Edit Sending Domain">
        <form method="POST" action="{{ route('customer.sending-domains.update', $sendingDomain) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                <textarea name="notes" id="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">{{ old('notes', $sendingDomain->notes) }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('customer.sending-domains.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Cancel</a>
                    @customercan('domains.sending_domains.permissions.can_edit_sending_domains')
                        <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded hover:bg-primary-700">Update Sending Domain</button>
                    @endcustomercan
                </div>
            </div>
        </form>
    </x-card>
</div>
@endsection
