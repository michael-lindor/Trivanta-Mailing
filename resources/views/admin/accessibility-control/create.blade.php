@extends('layouts.admin')

@php
    $isEditingPermission = !is_null(old('target_role_id', $selectedRoleId));
    $permissionVerb = $isEditingPermission ? 'Edit' : 'Create';
@endphp

@section('title', $permissionVerb . ' RBAC Role')
@section('page-title', $permissionVerb . ' RBAC Role')

@section('content')
<div class="space-y-6">
    <x-card :title="$permissionVerb . ' RBAC Role'">
        <form method="POST" action="{{ route('admin.accessibility-control.update') }}">
            @csrf

            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Target Role</label>
                    <select name="target_role_id" class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900" id="target_role_id" required>
                        <option value="">Select role</option>
                        @foreach($customerGroups as $g)
                            <option value="{{ $g->id }}" {{ (int) old('target_role_id', $selectedRoleId) === (int) $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-6 overflow-x-auto">
                @php
                    $currentPermissions = (array) old('permissions', $selectedPermissions);
                    $customerVisibleActions = array_values(array_filter(
                        $customerActions,
                        function (array $action) use ($customerModules, $customerSpecial): bool {
                            $actionKey = $action['key'] ?? null;
                            if (!is_string($actionKey) || trim($actionKey) === '') {
                                return false;
                            }

                            foreach ($customerModules as $module) {
                                if (data_get($module, 'perms.' . $actionKey)) {
                                    return true;
                                }
                            }

                            foreach ($customerSpecial as $row) {
                                if (data_get($row, 'perms.' . $actionKey)) {
                                    return true;
                                }
                            }

                            return false;
                        }
                    ));
                @endphp

                <div id="customer_permissions_wrap">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Module</th>
                                @foreach($customerVisibleActions as $action)
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        <div class="flex flex-col items-center gap-2">
                                            <span>{{ $action['label'] }}</span>
                                            <input type="checkbox" data-toggle-column="{{ $action['key'] }}">
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($customerModules as $module)
                                <tr>
                                    <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                        {{ $module['label'] }}
                                    </td>
                                    @foreach($customerVisibleActions as $action)
                                        @php
                                            $perm = data_get($module, 'perms.' . $action['key']);
                                        @endphp
                                        <td class="px-6 py-4 text-center">
                                            @if($perm)
                                                <input type="checkbox" data-action="{{ $action['key'] }}" name="permissions[]" value="{{ $perm }}" {{ in_array($perm, $currentPermissions, true) ? 'checked' : '' }}>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach

                            @if(!empty($customerSpecial))
                                @foreach($customerSpecial as $item)
                                    <tr>
                                        <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                            {{ $item['label'] }}
                                        </td>
                                        @foreach($customerVisibleActions as $action)
                                            @php
                                                $perm = data_get($item, 'perms.' . $action['key']);
                                            @endphp
                                            <td class="px-6 py-4 text-center">
                                                @if($perm)
                                                    <input type="checkbox" data-action="{{ $action['key'] }}" name="permissions[]" value="{{ $perm }}" {{ in_array($perm, $currentPermissions, true) ? 'checked' : '' }}>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-between">
                <a href="{{ route('admin.accessibility-control.index') }}">
                    <x-button type="button" variant="secondary">Back</x-button>
                </a>
                <x-button type="submit" variant="primary">{{ $isEditingPermission ? 'Save' : 'Create' }}</x-button>
            </div>
        </form>
    </x-card>
 </div>

 <script>
     function initAccessibilityControlForm() {
         var role = document.getElementById('target_role_id');
         if (!role) return;
         if (role.dataset && role.dataset.bound === '1') return;
         if (role.dataset) role.dataset.bound = '1';

         var customerPermissionsWrap = document.getElementById('customer_permissions_wrap');

        function wireColumnToggles(wrap) {
            if (!wrap) return;
            wrap.querySelectorAll('[data-toggle-column]').forEach(function (toggle) {
                toggle.addEventListener('change', function () {
                    var action = toggle.getAttribute('data-toggle-column');
                    wrap.querySelectorAll('input[type="checkbox"][data-action="' + action + '"]').forEach(function (cb) {
                        if (!cb.disabled) {
                            cb.checked = toggle.checked;
                        }
                    });
                });
            });
        }

         wireColumnToggles(customerPermissionsWrap);
     }

     if (document.readyState === 'loading') {
         document.addEventListener('DOMContentLoaded', initAccessibilityControlForm);
     } else {
         initAccessibilityControlForm();
     }

     document.addEventListener('turbo:load', initAccessibilityControlForm);
 </script>
 @endsection
