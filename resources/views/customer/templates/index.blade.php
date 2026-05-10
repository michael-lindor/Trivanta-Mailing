@extends('layouts.customer')

@php
    $currentType = request()->query('type');
    $pageTitle = match ($currentType) {
        'footer' => 'Footer templates',
        'signature' => 'Signature',
        default => 'Email Templates',
    };
@endphp

@push('styles')
<style>[x-cloak]{display:none!important}</style>
@endpush

@section('title', $pageTitle)
@section('page-title', $pageTitle)
@section('page-actions')
    @php
        $viewMode = request()->query('view', 'list');
        if (!in_array($viewMode, ['list', 'grid'], true)) {
            $viewMode = 'list';
        }
    @endphp

    <div class="flex flex-wrap items-center gap-2">
        <form method="GET" action="{{ route('customer.templates.index') }}" class="flex items-center gap-2">
            <input type="hidden" name="view" value="{{ $viewMode }}">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search templates..." class="w-48 px-3 py-1 rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white h-[38px]">
            <select name="type" class="px-3 py-1 text-sm rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white h-[38px]">
                <option value="">All Types</option>
                <option value="email" {{ ($filters['type'] ?? '') === 'email' ? 'selected' : '' }}>Email</option>
                <option value="campaign" {{ ($filters['type'] ?? '') === 'campaign' ? 'selected' : '' }}>Campaign</option>
                <option value="transactional" {{ ($filters['type'] ?? '') === 'transactional' ? 'selected' : '' }}>Transactional</option>
                <option value="autoresponder" {{ ($filters['type'] ?? '') === 'autoresponder' ? 'selected' : '' }}>Autoresponder</option>
                <option value="footer" {{ ($filters['type'] ?? '') === 'footer' ? 'selected' : '' }}>Footer Template</option>
                <option value="signature" {{ ($filters['type'] ?? '') === 'signature' ? 'selected' : '' }}>Signature</option>
            </select>
            <button type="submit" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">
                Filter
            </button>
        </form>

        <div class="inline-flex rounded-md shadow-sm" role="group">
            <a href="{{ route('customer.templates.index', array_merge(request()->query(), ['view' => 'list'])) }}"
               class="h-[38px] px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-l-md {{ $viewMode === 'list' ? 'bg-primary-600 text-white border-primary-600' : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300' }}">
                <x-lucide name="list" class="h-4 w-4" />
            </a>
            <a href="{{ route('customer.templates.index', array_merge(request()->query(), ['view' => 'grid'])) }}"
               class="h-[38px] px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-r-md -ml-px {{ $viewMode === 'grid' ? 'bg-primary-600 text-white border-primary-600' : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300' }}">
                <x-lucide name="layout-grid" class="h-4 w-4" />
            </a>
        </div>

        @customercan('templates.permissions.can_create_templates')
            <button type="button"
                @click="$dispatch('open-create-template-modal', { type: '{{ $currentType ?? 'email' }}' })"
                class="h-[38px] px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md shadow-sm hover:bg-primary-700">
                Create Template
            </button>
        @endcustomercan
    </div>
@endsection

@section('content')
<div
    class="space-y-6"
    x-data="{
        showCreateModal: false,
        newName: '',
        newType: 'email',
        nameError: '',
        startEditing() {
            if (!this.newName.trim()) { this.nameError = 'Template name is required.'; return; }
            this.nameError = '';
            const base = '{{ route('customer.templates.create') }}';
            window.location.href = base + '?name=' + encodeURIComponent(this.newName.trim()) + '&type=' + encodeURIComponent(this.newType);
        }
    }"
    @open-create-template-modal.window="showCreateModal = true; newName = ''; nameError = ''; newType = ($event.detail && $event.detail.type) ? $event.detail.type : 'email'"
>

    {{-- Create Template Modal --}}
    <div x-cloak x-show="showCreateModal" class="fixed inset-0 z-[70] flex items-center justify-center p-4" aria-modal="true" role="dialog">
        <div class="absolute inset-0 bg-slate-950/45" @click="showCreateModal = false"></div>
        <div class="relative w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
            <div class="mb-5">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Create template</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Enter the template name and choose a type to start editing.</p>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">Template Name</label>
                    <input type="text" x-model="newName" @keydown.enter.prevent="startEditing()" @keydown.escape.prevent="showCreateModal = false" autofocus
                        class="block w-full rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-950 dark:text-white text-sm"
                        placeholder="My new template">
                    <p x-show="nameError" x-text="nameError" class="mt-2 text-xs text-red-600"></p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">Template Type</label>
                    <select x-model="newType" class="block w-full rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-950 dark:text-white text-sm">
                        <option value="email">Email</option>
                        <option value="campaign">Campaign</option>
                        <option value="transactional">Transactional</option>
                        <option value="autoresponder">Autoresponder</option>
                        <option value="footer">Footer Template</option>
                        <option value="signature">Signature</option>
                    </select>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-3">
                <button type="button" @click="showCreateModal = false" class="rounded-lg border border-slate-200 dark:border-slate-700 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">Cancel</button>
                <button type="button" @click="startEditing()" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">Start Editing</button>
            </div>
        </div>
    </div>
    <!-- Templates Grid -->
    @if($templates->count() > 0)
        @if($viewMode === 'grid')
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($templates as $template)
                    <x-card class="hover:shadow-lg transition-shadow cursor-pointer" onclick="window.location='{{ route('customer.templates.show', $template) }}'">
                        <div class="space-y-4">
                            @php
                                $thumbnail = $template->thumbnail;
                                $thumbnailUrl = null;
                                $thumbnailExists = false;

                                if (is_string($thumbnail) && $thumbnail !== '') {
                                    if (preg_match('/^https?:\/\//i', $thumbnail)) {
                                        $thumbnailUrl = $thumbnail;
                                        $thumbnailExists = true;
                                    } else {
                                        $relative = ltrim($thumbnail, '/');
                                        if (str_starts_with($relative, 'public/')) {
                                            $relative = substr($relative, 7);
                                        }

                                        $publicRelative = str_starts_with($relative, 'storage/')
                                            ? $relative
                                            : ('storage/' . $relative);

                                        $thumbnailExists = file_exists(public_path($publicRelative));
                                        if ($thumbnailExists) {
                                            $thumbnailUrl = asset($publicRelative);
                                        }
                                    }
                                }
                            @endphp

                            @if($thumbnailUrl && $thumbnailExists)
                                <img src="{{ $thumbnailUrl }}" alt="{{ $template->name }}" class="w-full h-48 object-cover rounded-md">
                            @elseif(!empty($template->html_content))
                                <iframe
                                    srcdoc="{!! htmlspecialchars($template->html_content, ENT_QUOTES, 'UTF-8') !!}"
                                    class="w-full h-48 border-0 rounded-md bg-white"
                                    loading="lazy"
                                    sandbox
                                ></iframe>
                            @else
                                <div class="w-full h-48 bg-gray-200 dark:bg-gray-700 rounded-md flex items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            @endif
                            
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $template->name }}</h3>
                                    @if(is_array($template->settings) && (($template->settings['origin'] ?? null) === 'ai'))
                                        <span class="inline-flex items-center rounded-full bg-primary-100 px-2 py-0.5 text-[10px] font-semibold text-primary-800 dark:bg-primary-900 dark:text-primary-200">AI</span>
                                    @endif
                                </div>
                                @if($template->description)
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 line-clamp-2">{{ $template->description }}</p>
                                @endif
                            </div>
                            
                            <div class="flex items-center justify-between text-sm">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200">
                                    {{ ucfirst($template->type) }}
                                </span>
                                <span class="text-gray-500 dark:text-gray-400">
                                    Used {{ $template->usage_count }} times
                                </span>
                            </div>
                            
                            <div class="flex items-center gap-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                                <x-button href="{{ route('customer.templates.show', $template) }}" variant="table" size="action" :pill="true" class="p-2" title="View" aria-label="View"><x-lucide name="eye" class="h-4 w-4" /><span class="sr-only">View</span></x-button>
                                @customercan('templates.permissions.can_edit_templates')
                                    <x-button href="{{ route('customer.templates.edit', $template) }}" variant="table" size="action" :pill="true" class="p-2" title="Edit" aria-label="Edit"><x-lucide name="pencil" class="h-4 w-4" /><span class="sr-only">Edit</span></x-button>
                                @endcustomercan
                            </div>
                        </div>
                    </x-card>
                @endforeach
            </div>
        @else
            <x-card :padding="false">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Visibility</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Used</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($templates as $template)
                                @php
                                    $settings = is_array($template->settings) ? $template->settings : [];
                                    $isAi = ($settings['origin'] ?? null) === 'ai';

                                    $visibility = 'Private';
                                    if ($template->is_public) {
                                        $visibility = 'Public';
                                    }
                                    if ($template->is_system || $template->customer_id === null) {
                                        $visibility = 'System';
                                    }
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                        <a href="{{ route('customer.templates.show', $template) }}" class="hover:underline">
                                            {{ $template->name }}
                                        </a>
                                        @if($isAi)
                                            <div class="mt-1">
                                                <span class="inline-flex items-center rounded-full bg-primary-100 px-2 py-0.5 text-[10px] font-semibold text-primary-800 dark:bg-primary-900 dark:text-primary-200">AI</span>
                                            </div>
                                        @endif
                                        @if($template->description)
                                            <div class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-[520px]">{{ $template->description }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ ucfirst($template->type) }}</td>
                                    <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ $visibility }}</td>
                                    <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ (int) $template->usage_count }}</td>
                                    <td class="px-6 py-2 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <x-button href="{{ route('customer.templates.show', $template) }}" variant="table" size="action" :pill="true" class="p-2" title="View" aria-label="View"><x-lucide name="eye" class="h-4 w-4" /><span class="sr-only">View</span></x-button>

                                            @customercan('templates.permissions.can_edit_templates')
                                                <x-button href="{{ route('customer.templates.edit', $template) }}" variant="table" size="action" :pill="true" class="p-2" title="Edit" aria-label="Edit"><x-lucide name="pencil" class="h-4 w-4" /><span class="sr-only">Edit</span></x-button>
                                            @endcustomercan

                                            @customercan('templates.permissions.can_create_templates')
                                                <form method="POST" action="{{ route('customer.templates.duplicate', $template) }}" class="inline">
                                                    @csrf
                                                    <x-button type="submit" variant="table" size="action" :pill="true" class="p-2" title="Duplicate" aria-label="Duplicate"><x-lucide name="copy" class="h-4 w-4" /><span class="sr-only">Duplicate</span></x-button>
                                                </form>
                                            @endcustomercan

                                            @customercan('templates.permissions.can_delete_templates')
                                                <form method="POST" action="{{ route('customer.templates.destroy', $template) }}" class="inline" onsubmit="return confirm('Delete template?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <x-button type="submit" variant="table-danger" size="action" :pill="true" class="p-2" title="Delete" aria-label="Delete"><x-lucide name="trash-2" class="h-4 w-4" /><span class="sr-only">Delete</span></x-button>
                                                </form>
                                            @endcustomercan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>
        @endif

        <!-- Pagination -->
        <div class="mt-6">
            {{ $templates->links() }}
        </div>
    @else
        <x-card>
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No templates</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new template.</p>
                <div class="mt-6">
                    @customercan('templates.permissions.can_create_templates')
                        <button type="button"
                            @click="$dispatch('open-create-template-modal', { type: 'email' })"
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700">
                            Create Template
                        </button>
                    @endcustomercan
                </div>
            </div>
        </x-card>
    @endif
</div>
@endsection

