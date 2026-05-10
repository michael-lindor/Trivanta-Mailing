@props([
    'name',
    'options' => [],
    'selected' => [],
    'placeholder' => 'Select options',
    'searchPlaceholder' => 'Search...',
])

@php
    $selectedValues = collect($selected)
        ->map(fn ($value) => (string) $value)
        ->values()
        ->all();

    $normalizedOptions = collect($options)
        ->map(function ($option) {
            if (is_array($option)) {
                $value = $option['id'] ?? $option['value'] ?? null;
                $label = $option['label'] ?? $option['name'] ?? $value;

                if ($value === null) {
                    return null;
                }

                return [
                    'id' => (string) $value,
                    'label' => (string) $label,
                ];
            }

            if (is_object($option)) {
                $value = $option->id ?? $option->value ?? null;
                $label = $option->label ?? $option->name ?? $value;

                if ($value === null) {
                    return null;
                }

                return [
                    'id' => (string) $value,
                    'label' => (string) $label,
                ];
            }

            return null;
        })
        ->filter()
        ->values()
        ->all();
@endphp

<div
    x-data="{
        open: false,
        query: '',
        selected: {{ \Illuminate\Support\Js::from($selectedValues) }},
        options: {{ \Illuminate\Support\Js::from($normalizedOptions) }},
        init() {
            const optionIds = this.options.map((option) => String(option.id));
            this.selected = this.selected.filter((value) => optionIds.includes(String(value)));
        },
        isSelected(value) {
            return this.selected.includes(String(value));
        },
        toggle(value) {
            value = String(value);

            if (this.isSelected(value)) {
                this.selected = this.selected.filter((item) => item !== value);
                return;
            }

            this.selected = [...this.selected, value];
        },
        remove(value) {
            value = String(value);
            this.selected = this.selected.filter((item) => item !== value);
        },
        get selectedItems() {
            return this.options.filter((option) => this.selected.includes(String(option.id)));
        },
        get filteredOptions() {
            const search = this.query.trim().toLowerCase();

            if (!search) {
                return this.options;
            }

            return this.options.filter((option) => option.label.toLowerCase().includes(search));
        }
    }"
    @click.away="open = false"
    class="relative"
>
    <select name="{{ $name }}" multiple class="hidden" tabindex="-1" aria-hidden="true">
        <template x-for="selectedValue in selected" :key="`selected-${selectedValue}`">
            <option :value="selectedValue" selected></option>
        </template>
    </select>

    <div
        {{ $attributes->merge(['class' => 'mt-1 min-h-[42px] w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-2 py-1.5 shadow-sm focus-within:border-primary-500 focus-within:ring-1 focus-within:ring-primary-500']) }}
        @click="open = true; $nextTick(() => $refs.searchInput.focus())"
    >
        <div class="flex flex-wrap items-center gap-1.5">
            <template x-for="item in selectedItems" :key="`tag-${item.id}`">
                <span class="inline-flex items-center gap-1.5 rounded bg-gray-100 dark:bg-gray-600 px-2 py-1 text-sm text-gray-800 dark:text-gray-100">
                    <span x-text="item.label"></span>
                    <button
                        type="button"
                        @click.stop="remove(item.id)"
                        class="inline-flex h-5 w-5 items-center justify-center rounded-full text-gray-500 transition hover:bg-gray-200 hover:text-gray-800 dark:text-gray-300 dark:hover:bg-gray-500 dark:hover:text-gray-100"
                        aria-label="Remove"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </span>
            </template>

            <input
                x-ref="searchInput"
                type="text"
                x-model="query"
                @focus="open = true"
                @keydown.escape.stop="open = false"
                @keydown.backspace="if (!query && selected.length > 0) remove(selected[selected.length - 1])"
                :placeholder="selected.length ? '{{ $searchPlaceholder }}' : '{{ $placeholder }}'"
                class="min-w-[120px] flex-1 border-0 bg-transparent p-1 text-sm text-gray-900 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-400 focus:outline-none focus:ring-0"
            >
        </div>
    </div>

    <div
        x-show="open"
        x-transition.origin.top.left
        class="absolute z-30 mt-1 max-h-56 w-full overflow-auto rounded-md border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 shadow-lg"
        style="display: none;"
    >
        <template x-if="filteredOptions.length === 0">
            <div class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">No results found</div>
        </template>

        <template x-for="option in filteredOptions" :key="`option-${option.id}`">
            <button
                type="button"
                @click.prevent="toggle(option.id)"
                class="flex w-full items-center justify-between px-3 py-2 text-left text-sm"
                :class="isSelected(option.id)
                    ? 'bg-gray-100 text-gray-900 dark:bg-gray-600 dark:text-gray-100'
                    : 'text-gray-700 dark:text-gray-200 hover:bg-primary-500 hover:text-white'"
            >
                <span x-text="option.label"></span>
                <span x-show="isSelected(option.id)" class="text-xs">selected</span>
            </button>
        </template>
    </div>
</div>
