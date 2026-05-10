@extends('layouts.admin')

@section('title', __('RBAC'))
@section('page-title', __('RBAC'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        @admincan('admin.accessibility_control.access')
            <a href="{{ route('admin.accessibility-control.create') }}">
                <x-button type="button" variant="primary">{{ __('Manage Roles') }}</x-button>
            </a>
        @endadmincan
    </div>

    <x-card title="{{ __('Role Permissions') }}" :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Role') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Permissions') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($customerGroups as $g)
                        @php
                            $perms = (array) ($g->permissions ?? []);
                        @endphp
                        <tr>
                            <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ $g->name }}</td>
                            <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ count($perms) }}</td>
                            <td class="px-6 py-2 whitespace-nowrap text-sm text-right">
                                @admincan('admin.accessibility_control.access')
                                    <a href="{{ route('admin.accessibility-control.create', ['target_role_id' => $g->id]) }}">
                                        <x-button type="button" variant="secondary">{{ __('Edit') }}</x-button>
                                    </a>
                                @endadmincan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-card>
</div>
@endsection
