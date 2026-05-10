@extends('layouts.customer')

@section('title', 'Tracking Domains')
@section('page-title', 'Tracking Domains')

@section('page-actions')
    @customercan('domains.tracking_domains.permissions.can_create_tracking_domains')
        <x-button href="{{ route('customer.tracking-domains.create') }}" variant="primary" class="w-full sm:w-auto">Add Domain</x-button>
    @endcustomercan
@endsection

@section('content')
<div class="space-y-6">
    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Domain</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Verified</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($trackingDomains as $domain)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $domain->domain }}</td>
                            <td class="px-6 py-2 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $domain->status === 'verified' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ ucfirst($domain->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $domain->verified_at ? $domain->verified_at->format('M d, Y') : 'Not verified' }}
                            </td>
                            <td class="px-6 py-2 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <x-button href="{{ route('customer.tracking-domains.show', $domain) }}" variant="table" size="action" :pill="true" class="p-2" title="View" aria-label="View"><x-lucide name="eye" class="h-4 w-4" /><span class="sr-only">View</span></x-button>
                                    @if($domain->customer_id)
                                        @customercan('domains.tracking_domains.permissions.can_edit_tracking_domains')
                                            <x-button href="{{ route('customer.tracking-domains.edit', $domain) }}" variant="table" size="action" :pill="true" class="p-2" title="Edit" aria-label="Edit"><x-lucide name="pencil" class="h-4 w-4" /><span class="sr-only">Edit</span></x-button>
                                        @endcustomercan

                                        @customercan('domains.tracking_domains.permissions.can_delete_tracking_domains')
                                            <form method="POST" action="{{ route('customer.tracking-domains.destroy', $domain) }}" class="inline" onsubmit="return confirm('Are you sure?');">
                                                @csrf
                                                @method('DELETE')
                                                <x-button type="submit" variant="table-danger" size="action" :pill="true" class="p-2" title="Delete" aria-label="Delete"><x-lucide name="trash-2" class="h-4 w-4" /><span class="sr-only">Delete</span></x-button>
                                            </form>
                                        @endcustomercan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                No tracking domains found.
                                @customercan('domains.tracking_domains.permissions.can_create_tracking_domains')
                                    <a href="{{ route('customer.tracking-domains.create') }}" class="text-primary-600">Add your first domain</a>
                                @endcustomercan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($trackingDomains->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">{{ $trackingDomains->links() }}</div>
        @endif
    </x-card>
</div>
@endsection

