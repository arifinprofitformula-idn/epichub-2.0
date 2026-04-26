@php($fieldId = $fieldId ?? 'copy-field-'.md5($label.$value))

@once
<script>
function epicCopyField(btn, fieldId) {
    var input = document.getElementById(fieldId);
    if (!input) return;
    var text = input.value;
    var iconClip = btn.querySelector('[data-icon-clip]');
    var iconCheck = btn.querySelector('[data-icon-check]');
    var lbl = btn.querySelector('[data-copy-label]');

    navigator.clipboard.writeText(text).then(function() {
        if (iconClip) iconClip.classList.add('hidden');
        if (iconCheck) iconCheck.classList.remove('hidden');
        if (lbl) lbl.textContent = 'Tersalin!';
        btn.classList.remove('border-zinc-200', 'bg-white', 'text-zinc-600', 'hover:bg-zinc-50', 'dark:border-zinc-700', 'dark:bg-zinc-900', 'dark:text-zinc-400');
        btn.classList.add('border-emerald-300', 'bg-emerald-50', 'text-emerald-700', 'dark:border-emerald-700', 'dark:bg-emerald-950', 'dark:text-emerald-400');
        setTimeout(function() {
            if (iconClip) iconClip.classList.remove('hidden');
            if (iconCheck) iconCheck.classList.add('hidden');
            if (lbl) lbl.textContent = 'Salin';
            btn.classList.add('border-zinc-200', 'bg-white', 'text-zinc-600', 'hover:bg-zinc-50', 'dark:border-zinc-700', 'dark:bg-zinc-900', 'dark:text-zinc-400');
            btn.classList.remove('border-emerald-300', 'bg-emerald-50', 'text-emerald-700', 'dark:border-emerald-700', 'dark:bg-emerald-950', 'dark:text-emerald-400');
        }, 2000);
    }).catch(function() {
        input.select();
        try { document.execCommand('copy'); } catch(e) {}
    });
}
</script>
@endonce

<div>
    @if (! empty($label))
        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400 dark:text-zinc-500">
            {{ $label }}
        </div>
    @endif

    <div class="{{ ! empty($label) ? 'mt-2' : '' }} flex gap-2">
        <input
            id="{{ $fieldId }}"
            type="text"
            readonly
            value="{{ $value }}"
            onclick="this.select()"
            class="w-full min-w-0 rounded-[var(--radius-lg)] border border-zinc-200 bg-zinc-50/80 px-3 py-2 text-xs text-zinc-700 shadow-sm outline-none transition-colors duration-150 focus:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900/60 dark:text-zinc-300"
        />

        <button
            type="button"
            onclick="epicCopyField(this, '{{ $fieldId }}')"
            class="group flex shrink-0 items-center gap-1.5 rounded-[var(--radius-lg)] border border-zinc-200 bg-white px-3 py-2 text-xs font-semibold text-zinc-600 shadow-sm transition-all duration-200 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400"
        >
            <svg data-icon-clip viewBox="0 0 24 24" fill="none" class="size-3.5 transition-transform duration-150 group-hover:scale-110" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <rect x="9.25" y="4.75" width="9" height="12.5" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
                <path d="M15 4.75H7C6.17157 4.75 5.5 5.42157 5.5 6.25V17.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <svg data-icon-check viewBox="0 0 24 24" fill="none" class="hidden size-3.5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M5 12L10 17L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span data-copy-label>Salin</span>
        </button>
    </div>
</div>
