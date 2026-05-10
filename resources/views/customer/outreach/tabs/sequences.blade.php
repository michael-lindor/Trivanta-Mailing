<div x-data="sequenceEditor()" x-init="init()" class="space-y-0">

    <form method="POST" action="{{ route('customer.outreach.campaigns.sequences.update', $campaign) }}" id="save-form-sequences" @submit.prevent="submitSequences($el)">
        @csrf

        {{-- Steps --}}
        <div class="space-y-0">
            <template x-for="(step, i) in steps" :key="step._id">
                <div class="flex gap-4">

                    {{-- Timeline --}}
                    <div class="flex flex-col items-center flex-shrink-0 w-9">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold z-10 flex-shrink-0 mt-3"
                            :class="i === 0 ? 'bg-primary-600 text-white' : 'bg-white dark:bg-admin-card border-2 border-gray-200 dark:border-admin-border text-gray-500 dark:text-admin-text-secondary'">
                            <span x-text="i + 1"></span>
                        </div>
                        <div class="w-px flex-1 bg-gray-200 dark:bg-admin-border my-1" x-show="i < steps.length - 1"></div>
                    </div>

                    {{-- Card --}}
                    <div class="flex-1 pb-4">
                        <div class="bg-white dark:bg-admin-card border border-gray-100 dark:border-admin-border rounded-xl overflow-hidden">

                            {{-- Step Header --}}
                            <div class="flex items-center justify-between px-5 py-3 bg-gray-50/60 dark:bg-white/2 border-b border-gray-100 dark:border-admin-border">
                                <div class="flex items-center gap-3">
                                    <span class="text-sm font-semibold text-gray-800 dark:text-admin-text-primary" x-show="i === 0">{{ __('Day 1') }} — {{ __('Send immediately') }}</span>
                                    <template x-if="i > 0">
                                        <div class="flex items-center gap-2 text-sm text-gray-700 dark:text-admin-text-primary">
                                            <span>{{ __('Wait') }}</span>
                                            <input type="number" min="1" x-model.number="step.delay_days"
                                                class="w-14 px-2 py-1 text-sm text-center border border-gray-200 dark:border-admin-border bg-white dark:bg-white/5 text-gray-900 dark:text-admin-text-primary rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1E5FEA]">
                                            <select x-model="step.delay_type" class="appearance-none px-2 py-1 pr-6 text-sm border border-gray-200 dark:border-admin-border bg-white dark:bg-white/5 text-gray-700 dark:text-admin-text-primary rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1E5FEA]">
                                                <option value="minutes">{{ __('Minutes') }}</option>
                                                <option value="hours">{{ __('Hours') }}</option>
                                                <option value="days">{{ __('Days') }}</option>
                                                <option value="weeks">{{ __('Weeks') }}</option>
                                                <option value="months">{{ __('Months') }}</option>
                                            </select>
                                            <span>{{ __('then send') }}</span>
                                        </div>
                                    </template>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button type="button" @click="duplicateStep(i)" class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-gray-600 dark:hover:text-admin-text-primary rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 transition-colors" title="{{ __('Duplicate') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    </button>
                                    <button type="button" @click="removeStep(i)" x-show="steps.length > 1" class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-red-500 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors" title="{{ __('Delete') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </div>

                            {{-- Variant Tabs --}}
                            <div class="px-5 pt-4">
                                <div class="flex items-center gap-1 border-b border-gray-100 dark:border-admin-border mb-4 -ml-5 pl-4">
                                    <button type="button" @click="step.activeVariant = 'a'"
                                        :class="step.activeVariant === 'a' ? 'border-primary-600 text-primary-600' : '!border-transparent text-gray-500 dark:text-admin-text-secondary hover:text-gray-700'"
                                        class="pb-2.5 px-1 mr-2 text-sm font-medium border-b-2 transition-colors">
                                        {{ __('Variant A') }} (<span x-text="step.has_variant_b ? step.variant_split + '%' : '100%'"></span>)
                                    </button>
                                    <button type="button" @click="step.activeVariant = 'b'" x-show="step.has_variant_b"
                                        :class="step.activeVariant === 'b' ? 'border-primary-600 text-primary-600' : 'border-transparent text-gray-500 dark:text-admin-text-secondary hover:text-gray-700'"
                                        class="pb-2.5 px-1 mr-2 text-sm font-medium border-b-2 transition-colors">
                                        {{ __('Variant B') }} (<span x-text="(100 - step.variant_split) + '%'"></span>)
                                    </button>
                                    <button type="button" @click="addVariantB(step)" x-show="!step.has_variant_b"
                                        class="pb-2.5 px-1 text-sm font-medium text-[#1E5FEA] hover:text-blue-700 transition-colors flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        {{ __('Add Variant') }}
                                    </button>
                                    <button type="button" @click="removeVariantB(step)" x-show="step.has_variant_b"
                                        class="pb-2.5 px-1 ml-auto text-xs text-red-500 hover:text-red-700 transition-colors">
                                        {{ __('Remove B') }}
                                    </button>
                                </div>

                                {{-- Variant A --}}
                                <div x-show="step.activeVariant === 'a'" class="space-y-3 pb-4">
                                    <div class="flex items-center border border-gray-200 dark:border-admin-border rounded-lg overflow-hidden">
                                        <span class="px-3 py-2.5 text-xs font-medium text-gray-500 dark:text-admin-text-secondary border-r border-gray-200 dark:border-admin-border bg-gray-50 dark:bg-white/5 flex-shrink-0">{{ __('Subject') }}</span>
                                        @php $placeholderSubjectA = __('Quick question about {companyName}'); $placeholderSubjectRest = __('Leave empty to send as a reply in the same thread'); @endphp
                                        <input type="text" x-model="step.subject_a"
                                            :placeholder="i === 0 ? '{{ $placeholderSubjectA }}' : '{{ $placeholderSubjectRest }}'"
                                            class="border-0 focus:outline-none flex-1 px-3 py-2.5 text-sm bg-white dark:bg-white/5 text-gray-900 dark:text-admin-text-primary focus:outline-none placeholder-gray-400">
                                    </div>
                                    <template x-if="i > 0 && !step.subject_a">
                                        <p class="text-xs text-gray-400 dark:text-admin-text-secondary -mt-1">{{ __('Leaving subject empty will send this as a reply:') }} <span class="text-[#1E5FEA]">{{ __('Re: {previous subject}') }}</span></p>
                                    </template>
                                    <div class="border border-gray-200 dark:border-admin-border rounded-lg overflow-hidden">
                                        <div x-show="!(step.template_preview_a && step.template_preview_a.builder === 'unlayer' && step.template_preview_a.html_content)" class="flex items-center gap-1 px-3 py-2 border-b border-gray-100 dark:border-admin-border bg-gray-50/50 dark:bg-white/2">
                                            <button type="button" class="w-6 h-6 flex items-center justify-center text-gray-500 hover:text-gray-700 rounded font-bold text-sm">B</button>
                                            <button type="button" class="w-6 h-6 flex items-center justify-center text-gray-500 hover:text-gray-700 rounded italic text-sm">I</button>
                                            <button type="button" class="w-6 h-6 flex items-center justify-center text-gray-500 hover:text-gray-700 rounded underline text-sm">U</button>
                                            <span class="w-px h-4 bg-gray-200 dark:bg-admin-border mx-1"></span>
                                            <button type="button" class="w-6 h-6 flex items-center justify-center text-gray-500 hover:text-gray-700 rounded">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                                            </button>
                                            <button type="button" class="w-6 h-6 flex items-center justify-center text-gray-500 hover:text-gray-700 rounded">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 015.656 0l4-4a4 4 0 01-5.656-5.656l-1.1 1.1"/></svg>
                                            </button>
                                        </div>
                                        <div x-show="step.template_preview_a && step.template_preview_a.builder === 'unlayer' && step.template_preview_a.html_content" class="flex items-center justify-between gap-3 px-4 py-3 border-b border-gray-100 dark:border-admin-border bg-gray-50/50 dark:bg-white/2">
                                            <div class="min-w-0">
                                                <div class="text-sm font-medium text-gray-900 dark:text-admin-text-primary" x-text="step.template_preview_a.name || '{{ __('Template Preview') }}'"></div>
                                                <div class="mt-1 text-xs text-gray-500 dark:text-admin-text-secondary">{{ __('Preview mode') }}</div>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <a x-show="step.template_preview_a.id" :href="editTemplateUrl(step.template_preview_a.id)" class="text-xs text-[#1E5FEA] hover:text-blue-700 font-medium">{{ __('Edit Template') }}</a>
                                                <button type="button" @click="clearTemplatePreview(step, 'a')" class="text-xs text-red-500 hover:text-red-700 font-medium">{{ __('Remove Preview') }}</button>
                                            </div>
                                        </div>
                                        <template x-if="step.template_preview_a && step.template_preview_a.builder === 'unlayer' && step.template_preview_a.html_content">
                                            <iframe class="w-full min-h-[260px] bg-white" :srcdoc="step.template_preview_a.html_content"></iframe>
                                        </template>
                                        <textarea x-model="step.body_a" rows="5"
                                            @focus="rememberSelection($event, step, 'a')"
                                            @click="rememberSelection($event, step, 'a')"
                                            @keyup="rememberSelection($event, step, 'a')"
                                            placeholder="{{ __('Hi {firstName},\n\nI noticed that {companyName} is scaling up...') }}"
                                            x-show="!(step.template_preview_a && step.template_preview_a.builder === 'unlayer' && step.template_preview_a.html_content)"
                                            class="border-0 outline-none w-full px-4 py-3 text-sm bg-white dark:bg-white/5 text-gray-900 dark:text-admin-text-primary resize-none focus:outline-none placeholder-gray-400"></textarea>
                                    </div>
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-3">
                                            <button type="button" @click="openVariablePicker(step, 'a')"
                                                class="text-xs text-[#1E5FEA] hover:text-blue-700 flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                                                {{ __('Insert Variable') }}
                                            </button>
                                            <button type="button" @click="openTemplatePicker(step, 'a')"
                                                class="text-xs text-[#1E5FEA] hover:text-blue-700 flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                                                {{ __('Pick Template') }}
                                            </button>
                                        </div>
                                        <span class="text-xs text-gray-400 dark:text-admin-text-secondary">{{ __('Auto-saved') }}</span>
                                    </div>
                                </div>

                                {{-- Variant B --}}
                                <div x-show="step.activeVariant === 'b' && step.has_variant_b" class="space-y-3 pb-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs text-gray-500 dark:text-admin-text-secondary">{{ __('Split ratio') }}</span>
                                        <div class="flex items-center gap-2 text-xs">
                                            <span class="text-gray-600 dark:text-admin-text-secondary">A: <span x-text="step.variant_split + '%'"></span></span>
                                            <input type="range" min="10" max="90" x-model.number="step.variant_split" class="w-24 accent-[#1E5FEA]">
                                            <span class="text-gray-600 dark:text-admin-text-secondary">B: <span x-text="(100 - step.variant_split) + '%'"></span></span>
                                        </div>
                                    </div>
                                    <div class="flex items-center border border-gray-200 dark:border-admin-border rounded-lg overflow-hidden">
                                        <span class="px-3 py-2.5 text-xs font-medium text-gray-500 dark:text-admin-text-secondary border-r border-gray-200 dark:border-admin-border bg-gray-50 dark:bg-white/5 flex-shrink-0">{{ __('Subject') }}</span>
                                        <input type="text" x-model="step.subject_b" placeholder="{{ __('Variant B subject line...') }}"
                                            class="flex-1 px-3 py-2.5 text-sm bg-white dark:bg-white/5 text-gray-900 dark:text-admin-text-primary focus:outline-none placeholder-gray-400">
                                    </div>
                                    <div class="border border-gray-200 dark:border-admin-border rounded-lg overflow-hidden">
                                        <div x-show="!(step.template_preview_b && step.template_preview_b.builder === 'unlayer' && step.template_preview_b.html_content)" class="flex items-center gap-1 px-3 py-2 border-b border-gray-100 dark:border-admin-border bg-gray-50/50 dark:bg-white/2">
                                            <button type="button" class="w-6 h-6 flex items-center justify-center text-gray-500 hover:text-gray-700 rounded font-bold text-sm">B</button>
                                            <button type="button" class="w-6 h-6 flex items-center justify-center text-gray-500 hover:text-gray-700 rounded italic text-sm">I</button>
                                            <button type="button" class="w-6 h-6 flex items-center justify-center text-gray-500 hover:text-gray-700 rounded underline text-sm">U</button>
                                        </div>
                                        <div x-show="step.template_preview_b && step.template_preview_b.builder === 'unlayer' && step.template_preview_b.html_content" class="flex items-center justify-between gap-3 px-4 py-3 border-b border-gray-100 dark:border-admin-border bg-gray-50/50 dark:bg-white/2">
                                            <div class="min-w-0">
                                                <div class="text-sm font-medium text-gray-900 dark:text-admin-text-primary" x-text="step.template_preview_b.name || '{{ __('Template Preview') }}'"></div>
                                                <div class="mt-1 text-xs text-gray-500 dark:text-admin-text-secondary">{{ __('Preview mode') }}</div>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <a x-show="step.template_preview_b.id" :href="editTemplateUrl(step.template_preview_b.id)" class="text-xs text-[#1E5FEA] hover:text-blue-700 font-medium">{{ __('Edit Template') }}</a>
                                                <button type="button" @click="clearTemplatePreview(step, 'b')" class="text-xs text-red-500 hover:text-red-700 font-medium">{{ __('Remove Preview') }}</button>
                                            </div>
                                        </div>
                                        <template x-if="step.template_preview_b && step.template_preview_b.builder === 'unlayer' && step.template_preview_b.html_content">
                                            <iframe class="w-full min-h-[260px] bg-white" :srcdoc="step.template_preview_b.html_content"></iframe>
                                        </template>
                                        <textarea x-model="step.body_b" rows="5"
                                            @focus="rememberSelection($event, step, 'b')"
                                            @click="rememberSelection($event, step, 'b')"
                                            @keyup="rememberSelection($event, step, 'b')"
                                            placeholder="{{ __('Variant B email body...') }}"
                                            x-show="!(step.template_preview_b && step.template_preview_b.builder === 'unlayer' && step.template_preview_b.html_content)"
                                            class="w-full px-4 py-3 text-sm bg-white dark:bg-white/5 text-gray-900 dark:text-admin-text-primary resize-none focus:outline-none placeholder-gray-400"></textarea>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <button type="button" @click="openVariablePicker(step, 'b')"
                                            class="text-xs text-[#1E5FEA] hover:text-blue-700 flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                                            {{ __('Insert Variable') }}
                                        </button>
                                        <button type="button" @click="openTemplatePicker(step, 'b')"
                                            class="text-xs text-[#1E5FEA] hover:text-blue-700 flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                                            {{ __('Pick Template') }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </template>

            {{-- Add Step --}}
            <div class="flex pl-[52px]">
                <button type="button" @click="addStep()"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-admin-text-primary border border-gray-200 dark:border-admin-border bg-white dark:bg-white/5 hover:bg-gray-50 dark:hover:bg-white/10 rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ __('Add Step') }}
                </button>
            </div>
        </div>

        {{-- Hidden JSON payload --}}
        <input type="hidden" name="steps_json" :value="JSON.stringify(steps)">
    </form>

    {{-- Variable Picker Modal --}}
    <div x-cloak x-show="showVarPicker"
        x-transition:enter="ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 bg-black/50"
        @click.self="closeVariablePicker()"
    >
        <div class="bg-white dark:bg-admin-card rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-admin-border">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-admin-text-primary">{{ __('Insert Variable') }}</h3>
                <button type="button" @click="closeVariablePicker()" class="text-gray-400 hover:text-gray-600 dark:hover:text-admin-text-primary transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="px-5 py-4 max-h-[65vh] overflow-y-auto">
                <div class="relative mb-6">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35"/><circle cx="11" cy="11" r="6"/></svg>
                    </span>
                    <input type="text" x-model="variableSearch" placeholder="{{ __('Search variables...') }}"
                        class="w-full h-11 pl-11 pr-4 rounded-xl border border-gray-200 dark:border-admin-border bg-white dark:bg-white/5 text-sm text-gray-900 dark:text-admin-text-primary placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#1E5FEA]">
                </div>

                <div class="space-y-6">
                    <template x-for="group in filteredVariableGroups" :key="group.label">
                        <div>
                            <h4 class="mb-3 text-[11px] font-semibold tracking-[0.16em] text-slate-400 uppercase" x-text="group.label"></h4>
                            <div class="space-y-2">
                                <template x-for="variable in group.items" :key="variable.token">
                                    <button type="button" @click="selectVariable(variable.token)"
                                        class="w-full flex items-center justify-between gap-3 px-4 py-3 text-left rounded-xl border transition-colors"
                                        :class="selectedVariableToken === variable.token ? 'border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-900/20' : 'border-transparent hover:border-gray-200 hover:bg-slate-50 dark:hover:border-admin-border dark:hover:bg-white/5'">
                                        <div class="flex items-center gap-4 min-w-0">
                                            <span class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 border border-slate-200 dark:border-admin-border dark:text-admin-text-secondary" x-html="variable.icon"></span>
                                            <span class="text-sm font-medium text-gray-900 dark:text-admin-text-primary truncate" x-text="variable.name"></span>
                                        </div>
                                        <span class="px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-white/10 text-xs font-mono text-gray-700 dark:text-admin-text-primary" x-text="variable.token"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
            <div class="flex items-center justify-between gap-3 px-5 py-4 bg-blue-50/70 dark:bg-blue-900/10 border-t border-blue-100 dark:border-blue-900/30">
                <button type="button" class="inline-flex items-center gap-2 text-sm font-medium text-[#1E5FEA] hover:text-blue-700 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m7-7H5"/></svg>
                    {{ __('Manage Variables') }}
                </button>
                <button type="button" @click="applySelectedVariable()" :disabled="!selectedVariableToken"
                    class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-[#2563eb] text-white text-sm font-semibold hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    {{ __('Insert Variable') }}
                </button>
            </div>
        </div>
    </div>

    <div x-cloak x-show="showTemplatePicker"
        x-transition:enter="ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 bg-black/50"
        @click.self="closeTemplatePicker()"
    >
        <div class="bg-white dark:bg-admin-card rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-admin-border">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-admin-text-primary">{{ __('Pick Template') }}</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-admin-text-secondary">{{ __('Load an existing template into this sequence step.') }}</p>
                </div>
                <button type="button" @click="closeTemplatePicker()" class="text-gray-400 hover:text-gray-600 dark:hover:text-admin-text-primary transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                <input type="text" x-model="templateSearch" placeholder="{{ __('Search templates...') }}"
                    class="w-full h-11 px-4 rounded-xl border border-gray-200 dark:border-admin-border bg-white dark:bg-white/5 text-sm text-gray-900 dark:text-admin-text-primary placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#1E5FEA]">

                <div class="space-y-3" x-show="filteredTemplates.length > 0">
                    <template x-for="template in filteredTemplates" :key="template.id">
                        <button type="button" @click="selectTemplate(template.id)"
                            class="w-full text-left px-4 py-4 rounded-xl border transition-colors"
                            :class="selectedTemplateId === template.id ? 'border-blue-200 bg-blue-50/60 dark:border-blue-800 dark:bg-blue-900/10' : 'border-gray-200 dark:border-admin-border hover:border-blue-200 hover:bg-blue-50/60 dark:hover:border-blue-800 dark:hover:bg-blue-900/10'">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold text-gray-900 dark:text-admin-text-primary" x-text="template.name"></div>
                                    <div class="mt-1 text-sm text-gray-500 dark:text-admin-text-secondary" x-text="template.description || '{{ __('No description') }}'"></div>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-white/10 text-xs font-medium text-gray-600 dark:text-admin-text-secondary">#<span x-text="template.id"></span></span>
                            </div>
                        </button>
                    </template>
                </div>

                <div x-show="filteredTemplates.length === 0" class="rounded-xl border border-dashed border-gray-200 dark:border-admin-border px-4 py-10 text-center text-sm text-gray-500 dark:text-admin-text-secondary">
                    {{ __('No templates matched your search.') }}
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function sequenceEditor() {
    return {
        steps: [],
        templates: @json(($templates ?? collect())->map(fn ($template) => ['id' => $template->id, 'name' => $template->name, 'description' => $template->description])->values()),
        showVarPicker: false,
        showTemplatePicker: false,
        variableSearch: '',
        templateSearch: '',
        selectedVariableToken: '',
        selectedTemplateId: null,
        selectedTemplatePreview: null,
        templatePreviewLoading: false,
        activeVarStep: null,
        activeVarVariant: 'a',
        activeTemplateStep: null,
        activeTemplateVariant: 'a',
        lastFocusedField: null,
        _counter: 0,
        variableGroups: [
            {
                label: 'CONTACT',
                items: [
                    { name: 'First Name', token: '{firstName}', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11.5 15H7a4 4 0 0 0-4 4v2"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21.378 16.626a1 1 0 0 0-3.004-3.004l-4.01 4.012a2 2 0 0 0-.506.854l-.837 2.87a.5.5 0 0 0 .62.62l2.87-.837a2 2 0 0 0 .854-.506z"></path><circle cx="10" cy="7" r="4" stroke-width="1.8"></circle></svg>' },
                    { name: 'Last Name', token: '{lastName}', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 19a6 6 0 0 0-12 0"></path><circle cx="9" cy="7" r="4" stroke-width="1.8"></circle></svg>' },
                    { name: 'Email Address', token: '{email}', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2" ry="2" stroke-width="1.8"></rect><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M22 6l-10 7L2 6"></path></svg>' },
                    { name: 'Job Title', token: '{jobTitle}', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h16v11H4z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 7V5h6v2"></path></svg>' },
                ],
            },
            {
                label: 'COMPANY',
                items: [
                    { name: 'Company Name', token: '{companyName}', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 21h16"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 21V7h10v14"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 10h.01M9 13h.01M9 16h.01M15 10h.01M15 13h.01M15 16h.01"></path></svg>' },
                    { name: 'Website', token: '{website}', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.8"></circle><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2 12h20"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 2a15.3 15.3 0 0 1 0 20"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 2a15.3 15.3 0 0 0 0 20"></path></svg>' },
                    { name: 'Unsubscribe Link', token: '{unsubscribeLink}', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 14 21 3"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 3h5v5"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 14v5h-5"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10l11 11"></path></svg>' },
                ],
            },
        ],

        get filteredVariableGroups() {
            const search = this.variableSearch.trim().toLowerCase();

            return this.variableGroups
                .map((group) => ({
                    ...group,
                    items: group.items.filter((item) => {
                        if (!search) {
                            return true;
                        }

                        return [item.name, item.token].some((value) => String(value).toLowerCase().includes(search));
                    }),
                }))
                .filter((group) => group.items.length > 0);
        },

        get filteredTemplates() {
            const search = this.templateSearch.trim().toLowerCase();

            return this.templates.filter((template) => {
                if (!search) {
                    return true;
                }

                return [template.name, template.description]
                    .filter(Boolean)
                    .some((value) => String(value).toLowerCase().includes(search));
            });
        },

        init() {
            const existing = @json($sequences ?? []);
            if (existing.length > 0) {
                this.steps = existing.map((s, i) => ({
                    _id: i,
                    delay_days: s.delay_days ?? 0,
                    delay_type: s.delay_type ?? 'days',
                    subject_a: s.subject_a ?? '',
                    body_a: s.body_a ?? '',
                    subject_b: s.subject_b ?? '',
                    body_b: s.body_b ?? '',
                    variant_split: s.variant_split ?? 50,
                    has_variant_b: s.has_variant_b ?? false,
                    template_preview_a: null,
                    template_preview_b: null,
                    activeVariant: 'a',
                }));
                this._counter = existing.length;
            } else {
                this.addStep();
            }
        },

        addStep() {
            this.steps.push({
                _id: this._counter++,
                delay_days: this.steps.length === 0 ? 0 : 3,
                delay_type: 'days',
                subject_a: '',
                body_a: '',
                subject_b: '',
                body_b: '',
                variant_split: 50,
                has_variant_b: false,
                template_preview_a: null,
                template_preview_b: null,
                activeVariant: 'a',
            });
        },

        duplicateStep(i) {
            const copy = JSON.parse(JSON.stringify(this.steps[i]));
            copy._id = this._counter++;
            copy.activeVariant = 'a';
            this.steps.splice(i + 1, 0, copy);
        },

        removeStep(i) {
            this.steps.splice(i, 1);
        },

        addVariantB(step) {
            step.has_variant_b = true;
            step.activeVariant = 'b';
        },

        removeVariantB(step) {
            step.has_variant_b = false;
            step.subject_b = '';
            step.body_b = '';
            step.activeVariant = 'a';
        },

        rememberSelection(event, step, variant) {
            this.lastFocusedField = {
                stepId: step._id,
                variant,
                element: event.target,
                start: event.target.selectionStart ?? 0,
                end: event.target.selectionEnd ?? 0,
            };
        },

        openVariablePicker(step, variant) {
            this.activeVarStep = step;
            this.activeVarVariant = variant;
            this.variableSearch = '';
            this.selectedVariableToken = '';
            this.showVarPicker = true;
        },

        closeVariablePicker() {
            this.showVarPicker = false;
            this.variableSearch = '';
            this.selectedVariableToken = '';
        },

        selectVariable(token) {
            this.selectedVariableToken = token;
        },

        applySelectedVariable() {
            if (!this.selectedVariableToken || !this.activeVarStep) {
                return;
            }

            this.insertToken(this.activeVarStep, this.activeVarVariant, this.selectedVariableToken);
            this.closeVariablePicker();
        },

        openTemplatePicker(step, variant) {
            this.activeTemplateStep = step;
            this.activeTemplateVariant = variant;
            this.templateSearch = '';
            this.selectedTemplateId = null;
            this.selectedTemplatePreview = null;
            this.templatePreviewLoading = false;
            this.showTemplatePicker = true;
        },

        closeTemplatePicker() {
            this.showTemplatePicker = false;
            this.templateSearch = '';
            this.selectedTemplateId = null;
            this.templatePreviewLoading = false;
        },

        insertToken(step, variant, token) {
            const field = variant === 'a' ? 'body_a' : 'body_b';
            const currentValue = step[field] ?? '';
            const focus = this.lastFocusedField;

            if (focus && focus.stepId === step._id && focus.variant === variant && focus.element) {
                const start = focus.start ?? currentValue.length;
                const end = focus.end ?? start;
                step[field] = currentValue.slice(0, start) + token + currentValue.slice(end);

                this.$nextTick(() => {
                    focus.element.focus();
                    const cursor = start + token.length;
                    focus.element.setSelectionRange(cursor, cursor);
                    this.lastFocusedField = {
                        stepId: step._id,
                        variant,
                        element: focus.element,
                        start: cursor,
                        end: cursor,
                    };
                });

                return;
            }

            step[field] = currentValue + token;
        },

        async selectTemplate(templateId) {
            if (!templateId) {
                return;
            }

            this.selectedTemplateId = templateId;
            this.templatePreviewLoading = true;
            this.selectedTemplatePreview = null;

            const response = await fetch(this.templateContentUrl(templateId), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                this.templatePreviewLoading = false;
                return;
            }

            const data = await response.json();
            this.templatePreviewLoading = false;
            const field = this.activeTemplateVariant === 'a' ? 'body_a' : 'body_b';
            const previewField = this.activeTemplateVariant === 'a' ? 'template_preview_a' : 'template_preview_b';
            const plain = String(data.plain_text_content || '').trim();
            const html = String(data.html_content || '').trim();

            this.activeTemplateStep[field] = plain || this.stripHtml(html);
            this.activeTemplateStep[previewField] = {
                id: templateId,
                name: this.templates.find((template) => template.id === templateId)?.name || null,
                builder: data.builder || null,
                html_content: html,
                plain_text_content: plain,
            };
            this.closeTemplatePicker();
        },

        templateContentUrl(templateId) {
            return `{{ url('/customer/templates') }}/${templateId}/content`;
        },

        editTemplateUrl(templateId) {
            return `{{ url('/customer/templates') }}/${templateId}/edit`;
        },

        clearTemplatePreview(step, variant) {
            const previewField = variant === 'a' ? 'template_preview_a' : 'template_preview_b';
            step[previewField] = null;
        },

        stripHtml(html) {
            return String(html || '')
                .replace(/<style[\s\S]*?<\/style>/gi, ' ')
                .replace(/<script[\s\S]*?<\/script>/gi, ' ')
                .replace(/<[^>]+>/g, ' ')
                .replace(/&nbsp;/gi, ' ')
                .replace(/\s+/g, ' ')
                .trim();
        },

        submitSequences(form) {
            const payload = this.steps.map((s, i) => ({
                delay_days:    s.delay_days,
                delay_type:    s.delay_type,
                subject_a:     s.subject_a,
                body_a:        s.body_a,
                subject_b:     s.subject_b,
                body_b:        s.body_b,
                variant_split: s.variant_split,
                has_variant_b: s.has_variant_b,
            }));

            // Build a hidden form and submit it
            const hiddenForm = document.createElement('form');
            hiddenForm.method = 'POST';
            hiddenForm.action = form.action;

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            hiddenForm.appendChild(csrf);

            payload.forEach((step, i) => {
                Object.entries(step).forEach(([key, val]) => {
                    const inp = document.createElement('input');
                    inp.type = 'hidden';
                    inp.name = `steps[${i}][${key}]`;
                    inp.value = val;
                    hiddenForm.appendChild(inp);
                });
            });

            document.body.appendChild(hiddenForm);
            hiddenForm.submit();
        },
    };
}
</script>
@endpush
