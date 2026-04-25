@php($fieldId = $fieldId ?? 'copy-field-'.md5($label.$value))

<div>
    @if (! empty($label))
        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400 dark:text-zinc-500">
            {{ $label }}
        </div>
    @endif

    <div class="mt-2 flex flex-col gap-2 sm:flex-row">
        <input
            id="{{ $fieldId }}"
            type="text"
            readonly
            value="{{ $value }}"
            class="w-full rounded-[var(--radius-lg)] border border-zinc-200 bg-white px-3 py-2 text-xs text-zinc-900 shadow-sm outline-none ring-0 focus:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white"
        />

        <x-ui.button
            variant="ghost"
            size="sm"
            type="button"
            onclick="navigator.clipboard.writeText(document.getElementById('{{ $fieldId }}').value); const label = this.querySelector('[data-copy-label]'); if (label) { const original = label.dataset.originalLabel; label.textContent = 'Copied'; setTimeout(() => label.textContent = original, 1500); }"
        >
            <span data-copy-label data-original-label="Copy">Copy</span>
        </x-ui.button>
    </div>
</div>
