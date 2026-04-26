@component('layouts::app', ['title' => $course->title])
    @php
        $lessonsBySection = $lessons->groupBy(fn ($lesson) => $lesson->course_section_id ?? 0);
        $continueLesson = $lessons->first(function ($lesson) use ($progressByLessonId) {
            return ($progressByLessonId[$lesson->id] ?? null)?->value !== 'completed';
        }) ?? $lessons->sortBy('sort_order')->first();
        $totalSections = $sections->count() + ($lessonsBySection->get(0, collect())->count() > 0 ? 1 : 0);
        $remainingLessons = max($totalLessons - $completedLessons, 0);
    @endphp

    <div class="mx-auto flex min-h-[calc(100vh-1rem)] w-full max-w-[min(1520px,calc(100vw-40px))] flex-col px-0 pb-0 pt-0 md:min-h-screen md:pb-0">
        @include('partials.user-dashboard-header')

        <section class="px-1 py-8 md:px-6 lg:px-8">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <x-ui.button variant="ghost" size="sm" :href="route('my-courses.index')">
                    <- Kembali
                </x-ui.button>
                <x-ui.badge variant="info">Ecourse Workspace</x-ui.badge>
            </div>

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1.7fr)_380px]">
                <div class="space-y-6">
                    <x-ui.card class="overflow-hidden border-zinc-200/80 bg-[radial-gradient(circle_at_top_left,rgba(251,191,36,0.22),transparent_32%),linear-gradient(135deg,#111827_0%,#27272a_48%,#18181b_100%)] p-0 text-white shadow-[var(--shadow-card)]">
                        <div class="grid gap-6 p-6 md:p-8 lg:grid-cols-[minmax(0,1.3fr)_260px] lg:items-end">
                            <div>
                                <div class="flex flex-wrap items-center gap-2 text-xs uppercase tracking-[0.22em] text-white/60">
                                    <span>Learning Access</span>
                                    <span class="rounded-full border border-white/15 px-3 py-1 text-[11px] tracking-[0.18em] text-white/70">
                                        {{ $userProduct->product?->title ?? 'Produk kelas' }}
                                    </span>
                                </div>

                                <h1 class="mt-4 max-w-3xl text-2xl font-semibold tracking-tight text-white md:text-4xl">
                                    {{ $course->title }}
                                </h1>

                                @if (filled($course->short_description))
                                    <p class="mt-4 max-w-2xl text-sm leading-7 text-white/72 md:text-base">
                                        {{ $course->short_description }}
                                    </p>
                                @endif

                                <div class="mt-6 flex flex-wrap gap-3">
                                    @if ($continueLesson)
                                        <x-ui.button variant="primary" :href="route('my-courses.lessons.show', [$userProduct, $continueLesson])">
                                            {{ $completedLessons > 0 ? 'Lanjutkan belajar' : 'Mulai belajar' }}
                                        </x-ui.button>
                                    @endif

                                    <div class="inline-flex items-center rounded-[var(--radius-md)] border border-white/12 bg-white/8 px-4 py-2 text-sm text-white/85">
                                        {{ $completedLessons }} selesai / {{ $totalLessons }} materi
                                    </div>
                                </div>
                            </div>

                            <div class="grid gap-3">
                                <div class="rounded-[var(--radius-xl)] border border-white/10 bg-white/8 p-5 backdrop-blur">
                                    <div class="text-xs uppercase tracking-[0.2em] text-white/55">Progress kelas</div>
                                    <div class="mt-3 flex items-end justify-between gap-3">
                                        <div class="text-4xl font-semibold text-white">{{ $progressPercent }}%</div>
                                        <div class="text-right text-xs text-white/60">
                                            <div>{{ $completedLessons }} lesson selesai</div>
                                            <div>{{ $remainingLessons }} lesson tersisa</div>
                                        </div>
                                    </div>
                                    <div class="mt-4 h-2 overflow-hidden rounded-full bg-white/10">
                                        <div class="h-full rounded-full bg-[linear-gradient(90deg,#fbbf24_0%,#f59e0b_100%)]" style="width: {{ $progressPercent }}%"></div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-3 gap-3">
                                    <div class="rounded-[var(--radius-lg)] border border-white/10 bg-black/20 p-4">
                                        <div class="text-xs text-white/55">Bab</div>
                                        <div class="mt-2 text-xl font-semibold text-white">{{ $totalSections }}</div>
                                    </div>
                                    <div class="rounded-[var(--radius-lg)] border border-white/10 bg-black/20 p-4">
                                        <div class="text-xs text-white/55">Materi</div>
                                        <div class="mt-2 text-xl font-semibold text-white">{{ $totalLessons }}</div>
                                    </div>
                                    <div class="rounded-[var(--radius-lg)] border border-white/10 bg-black/20 p-4">
                                        <div class="text-xs text-white/55">Tersisa</div>
                                        <div class="mt-2 text-xl font-semibold text-white">{{ $remainingLessons }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-ui.card>

                    @if (($lessons->count() ?? 0) === 0)
                        <x-ui.empty-state
                            title="Belum ada lesson"
                            description="Materi kelas ini belum ditambahkan."
                        />
                    @else
                        <div class="grid gap-4 md:grid-cols-3">
                            <x-ui.card class="p-5">
                                <div class="text-xs uppercase tracking-[0.18em] text-zinc-500">Kesiapan belajar</div>
                                <div class="mt-3 text-2xl font-semibold text-zinc-950">{{ $progressPercent }}%</div>
                                <p class="mt-2 text-sm leading-6 text-zinc-600">
                                    Ritme sudah tercatat. Tinggal lanjutkan materi yang belum selesai untuk menutup gap progres.
                                </p>
                            </x-ui.card>
                            <x-ui.card class="p-5">
                                <div class="text-xs uppercase tracking-[0.18em] text-zinc-500">Fokus berikutnya</div>
                                <div class="mt-3 text-base font-semibold text-zinc-950">
                                    {{ $continueLesson?->title ?? 'Semua materi selesai' }}
                                </div>
                                <p class="mt-2 text-sm leading-6 text-zinc-600">
                                    {{ $continueLesson?->section?->title ?? 'Materi mandiri' }}
                                </p>
                            </x-ui.card>
                            <x-ui.card class="p-5">
                                <div class="text-xs uppercase tracking-[0.18em] text-zinc-500">Struktur kelas</div>
                                <div class="mt-3 text-base font-semibold text-zinc-950">
                                    {{ $sections->count() }} section terstruktur
                                </div>
                                <p class="mt-2 text-sm leading-6 text-zinc-600">
                                    Kurikulum dibagi agar materi lebih mudah dipindai dan dilanjutkan dari berbagai device.
                                </p>
                            </x-ui.card>
                        </div>

                        <div class="space-y-4">
                            @foreach ($sections as $section)
                                @php($sectionLessons = $lessonsBySection->get($section->id, collect()))
                                @php($sectionCompleted = $sectionLessons->filter(fn ($lesson) => ($progressByLessonId[$lesson->id] ?? null)?->value === 'completed')->count())

                                @if ($sectionLessons->count() > 0)
                                    <x-ui.card class="overflow-hidden p-0">
                                        <div class="border-b border-zinc-200/70 bg-zinc-50/70 px-6 py-5">
                                            <div class="flex flex-wrap items-start justify-between gap-4">
                                                <div>
                                                    <div class="text-lg font-semibold tracking-tight text-zinc-950">
                                                        {{ $section->title }}
                                                    </div>
                                                    @if (filled($section->description))
                                                        <div class="mt-2 max-w-3xl text-sm leading-6 text-zinc-600">
                                                            {{ $section->description }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="rounded-full border border-zinc-200 bg-white px-4 py-2 text-sm text-zinc-600">
                                                    {{ $sectionCompleted }}/{{ $sectionLessons->count() }} selesai
                                                </div>
                                            </div>
                                        </div>

                                        <div class="divide-y divide-zinc-200/70">
                                            @foreach ($sectionLessons as $lesson)
                                                @php($status = $progressByLessonId[$lesson->id] ?? null)
                                                @php($isCompleted = $status?->value === 'completed')
                                                @php($isInProgress = $status?->value === 'in_progress')

                                                <a
                                                    href="{{ route('my-courses.lessons.show', [$userProduct, $lesson]) }}"
                                                    class="group flex flex-col gap-4 px-6 py-5 transition hover:bg-amber-50/40 md:flex-row md:items-center md:justify-between"
                                                >
                                                    <div class="flex min-w-0 items-start gap-4">
                                                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-[var(--radius-md)] border {{ $isCompleted ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : ($isInProgress ? 'border-amber-200 bg-amber-50 text-amber-700' : 'border-zinc-200 bg-zinc-50 text-zinc-500') }}">
                                                            {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                                        </div>
                                                        <div class="min-w-0">
                                                            <div class="font-semibold text-zinc-950 transition group-hover:text-zinc-900">
                                                                {{ $lesson->title }}
                                                            </div>
                                                            <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-zinc-500">
                                                                <span class="rounded-full bg-zinc-100 px-2.5 py-1">{{ $lesson->lesson_type?->label() ?? ($lesson->lesson_type?->value ?? '-') }}</span>
                                                                @if ($lesson->duration_minutes)
                                                                    <span>{{ $lesson->duration_minutes }} menit</span>
                                                                @endif
                                                                @if (filled($lesson->short_description))
                                                                    <span class="max-w-xl truncate">{{ $lesson->short_description }}</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="flex items-center gap-3">
                                                        @if ($isCompleted)
                                                            <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700">Selesai</span>
                                                        @elseif ($isInProgress)
                                                            <span class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700">Berjalan</span>
                                                        @else
                                                            <span class="rounded-full border border-zinc-200 bg-white px-3 py-1.5 text-xs font-semibold text-zinc-600">Belum mulai</span>
                                                        @endif

                                                        <span class="text-sm font-medium text-zinc-400 transition group-hover:translate-x-0.5 group-hover:text-zinc-700">Buka -></span>
                                                    </div>
                                                </a>
                                            @endforeach
                                        </div>
                                    </x-ui.card>
                                @endif
                            @endforeach

                            @php($noSectionLessons = $lessonsBySection->get(0, collect()))

                            @if ($noSectionLessons->count() > 0)
                                <x-ui.card class="overflow-hidden p-0">
                                    <div class="border-b border-zinc-200/70 bg-zinc-50/70 px-6 py-5">
                                        <div class="flex items-start justify-between gap-4">
                                            <div>
                                                <div class="text-lg font-semibold tracking-tight text-zinc-950">Materi Tambahan</div>
                                                <div class="mt-2 text-sm text-zinc-600">
                                                    Lesson yang tidak berada di section tertentu tetap bisa diakses cepat dari sini.
                                                </div>
                                            </div>
                                            <div class="rounded-full border border-zinc-200 bg-white px-4 py-2 text-sm text-zinc-600">
                                                {{ $noSectionLessons->count() }} lesson
                                            </div>
                                        </div>
                                    </div>

                                    <div class="divide-y divide-zinc-200/70">
                                        @foreach ($noSectionLessons as $lesson)
                                            @php($status = $progressByLessonId[$lesson->id] ?? null)
                                            @php($isCompleted = $status?->value === 'completed')
                                            @php($isInProgress = $status?->value === 'in_progress')

                                            <a
                                                href="{{ route('my-courses.lessons.show', [$userProduct, $lesson]) }}"
                                                class="group flex flex-col gap-4 px-6 py-5 transition hover:bg-amber-50/40 md:flex-row md:items-center md:justify-between"
                                            >
                                                <div class="flex min-w-0 items-start gap-4">
                                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-[var(--radius-md)] border {{ $isCompleted ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : ($isInProgress ? 'border-amber-200 bg-amber-50 text-amber-700' : 'border-zinc-200 bg-zinc-50 text-zinc-500') }}">
                                                        {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                                    </div>
                                                    <div class="min-w-0">
                                                        <div class="font-semibold text-zinc-950">{{ $lesson->title }}</div>
                                                        <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-zinc-500">
                                                            <span class="rounded-full bg-zinc-100 px-2.5 py-1">{{ $lesson->lesson_type?->label() ?? ($lesson->lesson_type?->value ?? '-') }}</span>
                                                            @if ($lesson->duration_minutes)
                                                                <span>{{ $lesson->duration_minutes }} menit</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="flex items-center gap-3">
                                                    @if ($isCompleted)
                                                        <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700">Selesai</span>
                                                    @elseif ($isInProgress)
                                                        <span class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700">Berjalan</span>
                                                    @else
                                                        <span class="rounded-full border border-zinc-200 bg-white px-3 py-1.5 text-xs font-semibold text-zinc-600">Belum mulai</span>
                                                    @endif

                                                    <span class="text-sm font-medium text-zinc-400 transition group-hover:translate-x-0.5 group-hover:text-zinc-700">Buka -></span>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                </x-ui.card>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="space-y-6 xl:sticky xl:top-24 xl:self-start">
                    <x-ui.card class="p-6">
                        <div class="text-sm font-semibold text-zinc-950">Rencana Belajar</div>
                        <div class="mt-3 space-y-3 text-sm text-zinc-600">
                            <div class="rounded-[var(--radius-lg)] bg-zinc-50 p-4">
                                <div class="font-semibold text-zinc-900">Checkpoint berikutnya</div>
                                <div class="mt-1 leading-6">
                                    {{ $continueLesson?->title ?? 'Semua lesson selesai. Saatnya review ulang materi penting.' }}
                                </div>
                            </div>
                            <div class="rounded-[var(--radius-lg)] border border-zinc-200/70 p-4">
                                <div class="font-semibold text-zinc-900">Yang sudah tercapai</div>
                                <div class="mt-1 leading-6">
                                    {{ $completedLessons }} lesson sudah dituntaskan. Sisanya tinggal {{ $remainingLessons }} lesson lagi.
                                </div>
                            </div>
                        </div>

                        @if ($continueLesson)
                            <div class="mt-5">
                                <x-ui.button variant="primary" class="w-full" :href="route('my-courses.lessons.show', [$userProduct, $continueLesson])">
                                    Buka materi berikutnya
                                </x-ui.button>
                            </div>
                        @endif
                    </x-ui.card>

                    <x-ui.card class="p-6">
                        <div class="text-sm font-semibold text-zinc-950">Tips Penggunaan</div>
                        <div class="mt-4 space-y-3 text-sm leading-6 text-zinc-600">
                            <div class="rounded-[var(--radius-lg)] border border-zinc-200/70 p-4">
                                Mulai dari lesson yang berstatus <span class="font-semibold text-amber-700">Berjalan</span> agar momentum belajar tidak putus.
                            </div>
                            <div class="rounded-[var(--radius-lg)] border border-zinc-200/70 p-4">
                                Gunakan halaman lesson untuk akses video, artikel, file, atau link eksternal dalam satu pola yang konsisten.
                            </div>
                            <div class="rounded-[var(--radius-lg)] border border-zinc-200/70 p-4">
                                Progress disimpan per materi, jadi pengguna bisa lanjut lintas device tanpa bingung mencari posisi terakhir.
                            </div>
                        </div>
                    </x-ui.card>
                </div>
            </div>
        </section>

        @include('partials.user-dashboard-footer')
    </div>
@endcomponent
