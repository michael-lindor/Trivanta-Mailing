@php
    $isEdit = isset($template);
    $requireInitialTemplateDetails = $requireInitialTemplateDetails ?? false;
    $unlayerProjectId = config('services.unlayer.project_id');

    $unlayerDesign = null;
    if ($isEdit) {
        $data = is_array($template->grapesjs_data) ? $template->grapesjs_data : null;
        if ($data !== null && ($data['builder'] ?? null) === 'unlayer' && is_array($data['unlayer'] ?? null)) {
            $unlayerDesign = $data['unlayer'];
        }
    }

    $initialName = $isEdit ? old('name', $template->name) : old('name', $name ?? '');
    $initialType = $isEdit ? old('type', $template->type) : old('type', $type ?? 'email');
    $showModal    = $requireInitialTemplateDetails && !trim((string) $initialName);
@endphp

@if($unlayerDesign !== null)
<script type="application/json" id="unlayer-design-json">@json($unlayerDesign)</script>
@endif

<div
    x-data="{
        showModal: @js($showModal),
        name: @js($initialName),
        type: @js($initialType),
        error: '',
        confirm() {
            if (!this.name.trim()) { this.error = 'Template name is required.'; return; }
            this.error = '';
            const n = document.getElementById('name');
            const t = document.getElementById('type');
            const h = document.getElementById('template-header-title');
            if (n) n.value = this.name;
            if (t) t.value = this.type;
            if (h) h.textContent = this.name;
            this.showModal = false;
            if (typeof window.__mailpurseSetupUnlayerEditors === 'function') {
                window.__mailpurseSetupUnlayerEditors();
            }
        }
    }"
    class="flex-1 min-h-0 overflow-hidden"
>
    {{-- Initial template details modal (create flow only) --}}
    @if($requireInitialTemplateDetails)
    <div x-cloak x-show="showModal" class="fixed inset-0 z-[70] flex items-center justify-center p-4" aria-modal="true" role="dialog">
        <div class="absolute inset-0 bg-slate-950/45"></div>
        <div class="relative w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
            <div class="mb-5">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Create template</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Enter the template name and choose a type to start editing.</p>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">Template Name</label>
                    <input type="text" x-model="name" @keydown.enter.prevent="confirm()" class="block w-full rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-950 dark:text-white text-sm" placeholder="My new template">
                    <p x-show="error" x-text="error" class="mt-2 text-xs text-red-600"></p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">Template Type</label>
                    <select x-model="type" class="block w-full rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-950 dark:text-white text-sm">
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
                <button type="button" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors" @click="confirm()">
                    Start Editing
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Unlayer editor --}}
    <div
        id="editor-container"
        data-unlayer-editor
        data-unlayer-display-mode="email"
        @if($unlayerProjectId) data-unlayer-project-id="{{ $unlayerProjectId }}" @endif
        @if($unlayerDesign !== null) data-unlayer-design-script-id="unlayer-design-json" @endif
        class="fixed w-[calc(100%-40px)] mt-[50px]"
        style="height:calc(100vh - 50px);"
    >
        <div data-unlayer-loading style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;background:#f8fafc;z-index:20;gap:16px;">
            <svg style="width:40px;height:40px;color:#6366f1;animation:spin 1s linear infinite;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle style="opacity:.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path style="opacity:.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p style="font-size:14px;font-weight:500;color:#64748b;margin:0;">Loading builder&hellip;</p>
            <style>@keyframes spin{to{transform:rotate(360deg)}}</style>
        </div>
    </div>
</div>

@once
    @push('scripts')
    <script>
    (function () {
        const bindSave = () => {
            const btn  = document.getElementById('btn-save');
            const form = document.getElementById('unlayer-form');
            if (!btn || !form || btn.dataset.unlayerBound === '1') return;
            btn.dataset.unlayerBound = '1';
            btn.addEventListener('click', () => {
                if (!window.unlayer || typeof window.unlayer.exportHtml !== 'function') {
                    form.submit();
                    return;
                }
                btn.disabled = true;
                window.unlayer.exportHtml((data) => {
                    const html   = (data && data.html)   ? String(data.html)   : '';
                    const design = (data && data.design) ? data.design         : null;
                    const htmlEl  = document.getElementById('html_content');
                    const plainEl = document.getElementById('plain_text_content');
                    const dataEl  = document.getElementById('grapesjs_data');
                    if (htmlEl)  htmlEl.value  = html;
                    if (plainEl) plainEl.value = html.replace(/<style[\s\S]*?<\/style>/gi, ' ').replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
                    if (dataEl && design) dataEl.value = JSON.stringify({ builder: 'unlayer', unlayer: design });
                    form.submit();
                });
            });
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', bindSave);
        } else {
            bindSave();
        }

        if (typeof window.__mailpurseSetupUnlayerEditors === 'function') {
            window.__mailpurseSetupUnlayerEditors();
        }
    })();
    </script>
    @endpush
@endonce
