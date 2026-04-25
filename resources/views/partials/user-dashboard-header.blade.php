<section class="sticky top-0 z-20 mb-[10px] hidden flex-wrap items-center justify-between gap-4 border-b border-slate-200/80 bg-white/95 px-1 py-5 backdrop-blur md:-mt-8 md:-mx-6 md:flex md:px-0 lg:-mx-8">
    <div class="flex items-center gap-3 md:pl-6 lg:pl-8">
        <flux:sidebar.toggle
            class="hidden lg:inline-flex size-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:border-cyan-300 hover:text-cyan-700"
            icon="bars-2"
            inset="left"
        />
    </div>

    <div class="flex items-center gap-4 md:pr-6 lg:pr-8">
        <div class="text-right">
            <div class="text-sm font-semibold text-slate-900">
                {{ auth()->user()->name }}
            </div>
            <div class="mt-0.5 text-xs font-medium text-slate-500">
                {{ auth()->user()->hasVerifiedEmail() ? 'Pengguna terverifikasi' : 'Menunggu verifikasi' }}
            </div>
        </div>

        <a
            href="{{ route('profile.edit') }}"
            class="group inline-flex size-12 items-center justify-center rounded-full bg-[linear-gradient(135deg,#0f172a,#1d4ed8)] text-sm font-semibold text-white shadow-[0_12px_25px_rgba(37,99,235,0.18)] transition hover:brightness-110"
            aria-label="Buka profil pengguna"
        >
            <span class="group-hover:scale-105 transition">
                {{ auth()->user()->initials() }}
            </span>
        </a>
    </div>
</section>
