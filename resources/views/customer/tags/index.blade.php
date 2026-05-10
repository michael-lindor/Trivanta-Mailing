@extends('layouts.customer')

@section('title', 'Tags')

@section('content')
<div class="space-y-6" x-data="{
    createOpen: false,
    editOpen: false,
    editTag: { id: null, name: '', description: '' }
}">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-50">Tags</h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Organize and segment your audience using custom tags.</p>
        </div>
        <button
            type="button"
            @click="createOpen = true"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-700"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Create Tag
        </button>
    </div>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-gray-500 dark:text-gray-400">Showing {{ number_format($tags->count()) }} total {{ \Illuminate\Support\Str::plural('tag', $tags->count()) }}</p>

        <form method="GET" action="{{ route('customer.tags.index') }}" class="flex items-center gap-2">
            <div class="relative">
                <select
                    name="sort"
                    onchange="this.form.submit()"
                    class="appearance-none rounded-lg border border-gray-200 bg-white py-2.5 pl-10 pr-10 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                >
                    @foreach($sortOptions as $value => $label)
                        <option value="{{ $value }}" {{ $sortBy === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                </svg>
                <svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                </svg>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-blue-50/80 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Tag Name</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Subscribers</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Created</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($tags as $tag)
                        <tr class="align-top">
                            <td class="px-6 py-5">
                                <div class="inline-flex items-center gap-2 rounded-lg bg-blue-50 px-3 py-1 text-sm font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-tag-icon lucide-tag"><path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"/><circle cx="7.5" cy="7.5" r=".5" fill="currentColor"/></svg>
                                    {{ $tag['name'] }}
                                </div>
                                @if($tag['description'])
                                    <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">{{ $tag['description'] }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-5 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ number_format($tag['subscribers']) }}</td>
                            <td class="px-6 py-5 text-sm text-gray-500 dark:text-gray-400">{{ optional($tag['created_at'])->format('M d, Y') ?? '—' }}</td>
                            <td class="px-6 py-5">
                                <div class="flex items-center justify-end gap-3">
                                    <button
                                        type="button"
                                        @click='editTag = @json(["id" => $tag["id"], "name" => $tag["name"], "description" => $tag["description"]]); editOpen = true'
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-primary-600 dark:hover:bg-gray-700 dark:hover:text-primary-400"
                                        aria-label="Edit tag"
                                    >
                                        <x-lucide name="pencil" class="h-4 w-4" />
                                    </button>
                                    <form method="POST" action="{{ route('customer.tags.destroy', $tag['id']) }}" onsubmit="return confirm('Delete this tag? This will remove it from related lists and subscribers.')">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-300 transition hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-900/20 dark:hover:text-red-400"
                                            aria-label="Delete tag"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash2-icon lucide-trash-2"><path d="M10 11v6"/><path d="M14 11v6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center">
                                <div class="mx-auto max-w-md">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">No tags yet</h3>
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Create your first tag to start segmenting subscribers across your audience.</p>
                                    <button
                                        type="button"
                                        @click="createOpen = true"
                                        class="mt-5 inline-flex items-center justify-center gap-2 rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-700"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                        Create Tag
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <template x-teleport="body">
        <div
            x-cloak
            x-show="createOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/50 backdrop-blur-md px-4 py-8"
            @keydown.escape.window="createOpen = false"
        >
            <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-800" @click.outside="createOpen = false"
                 x-transition:enter="transition ease-out duration-250"
                 x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-180"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-y-3 scale-95">
                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-5 dark:border-gray-700">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Create New Tag</h2>
                    <button type="button" @click="createOpen = false" class="rounded-md p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('customer.tags.store') }}">
                    @csrf
                    <div class="space-y-5 px-6 py-5">
                        <div>
                            <label for="create_tag_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tag Name</label>
                            <input id="create_tag_name" name="name" type="text" value="{{ old('name') }}" required maxlength="100" class="mt-2 block w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                            @error('name')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="create_tag_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description <span class="text-gray-400">(Optional)</span></label>
                            <textarea id="create_tag_description" name="description" rows="4" maxlength="1000" class="mt-2 block w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" placeholder="Add a short description to remember what this tag is for...">{{ old('description') }}</textarea>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3 bg-blue-50/70 px-6 py-4 dark:bg-gray-700/40">
                        <button type="button" @click="createOpen = false" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">Cancel</button>
                        <button type="submit" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-700">Create Tag</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <template x-teleport="body">
        <div
            x-cloak
            x-show="editOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/50 backdrop-blur-md px-4 py-8"
            @keydown.escape.window="editOpen = false"
        >
            <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-800" @click.outside="editOpen = false"
                 x-transition:enter="transition ease-out duration-250"
                 x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-180"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-y-3 scale-95">
                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-5 dark:border-gray-700">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Edit Tag</h2>
                    <button type="button" @click="editOpen = false" class="rounded-md p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form :action="editTag.id ? '{{ url('customer/tags') }}/' + editTag.id : '#'" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="space-y-5 px-6 py-5">
                        <div>
                            <label for="edit_tag_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tag Name</label>
                            <input id="edit_tag_name" name="name" type="text" x-model="editTag.name" required maxlength="100" class="mt-2 block w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                            <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">This is how the tag will appear across the platform.</p>
                        </div>
                        <div>
                            <label for="edit_tag_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description <span class="text-gray-400">(Optional)</span></label>
                            <textarea id="edit_tag_description" name="description" rows="4" maxlength="1000" x-model="editTag.description" class="mt-2 block w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"></textarea>
                            <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">Internal note to help your team understand this tag's purpose.</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3 bg-blue-50/70 px-6 py-4 dark:bg-gray-700/40">
                        <button type="button" @click="editOpen = false" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">Cancel</button>
                        <button type="submit" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-700">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
@endsection
