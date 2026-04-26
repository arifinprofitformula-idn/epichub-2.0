@component('layouts::app', ['title' => $course->title])
    @php
        $displayLessons = $visibleLessons ?? $lessons;
        $lessonsBySection = $displayLessons->groupBy(fn ($lesson) => $lesson->course_section_id ?? 0);
        $visibleSectionIds = $displayLessons->pluck('course_section_id')->filter()->unique();
        $continueLesson = $continueLesson ?? $displayLessons->first(function ($lesson) use ($progressByLessonId, $lessonAccessByLessonId) {
            return ($progressByLessonId[$lesson->id] ?? null)?->value !== 'completed'
                && (bool) ($lessonAccessByLessonId[$lesson->id]['can_access'] ?? false);
        });
        $totalSections = $sections->filter(fn ($section) => $visibleSectionIds->contains($section->id))->count()
            + ($lessonsBySection->get(0, collect())->count() > 0 ? 1 : 0);
        $remainingLessons = max($totalLessons - $completedLessons, 0);
    @endphp

    <style>
        .btn-3d-primary {
            position: relative;
            transform: translateY(0);
            box-shadow: 0 6px 0 0 #b45309, 0 8px 16px rgba(245,158,11,0.3);
            transition: transform 0.1s ease, box-shadow 0.1s ease;
        }
        .btn-3d-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 0 0 #b45309, 0 12px 20px rgba(245,158,11,0.35);
        }
        .btn-3d-primary:active {
            transform: translateY(4px);
            box-shadow: 0 2px 0 0 #b45309;
        }
        .btn-3d-nav {
            position: relative;
            transform: translateY(0);
            box-shadow: 0 4px 0 0 #d4d4d8, 0 6px 12px rgba(0,0,0,0.07);
            transition: transform 0.1s ease, box-shadow 0.1s ease;
        }
        .btn-3d-nav:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 0 0 #d4d4d8, 0 10px 16px rgba(0,0,0,0.1);
            background-color: #f4f4f5;
        }
        .btn-3d-nav:active {
            transform: translateY(3px);
            box-shadow: 0 1px 0 0 #d4d4d8;
        }
        .lesson-row-link {
            transition: background-color 0.15s ease, transform 0.15s ease, box-shadow 0.15s ease;
        }
        .lesson-row-link:hover {
            background-color: #fffbeb;
        }
        .lesson-row-link:hover .lesson-arrow {
            transform: translateX(3px);
        }
        .lesson-arrow {
            transition: transform 0.15s ease;
        }
        .progress-glow {
            box-shadow: 0 0 8px rgba(251,191,36,0.6);
        }
        .tip-icon-badge {
            flex-shrink: 0;
            width: 32px;
            height: 32px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>

    <div class="mx-auto flex min-h-[calc(100vh-1rem)] w-full max-w-[min(1520px,calc(100vw-40px))] flex-col px-0 pb-0 pt-0 md:min-h-screen md:pb-0">
        @include('partials.user-dashboard-header')

        <section class="px-1 py-6 md:px-6 lg:px-8">

            {{-- Top bar --}}
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                <a
                    href="{{ route('my-courses.index') }}"
                    class="btn-3d-nav inline-flex items-center gap-1.5 rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm font-semibold text-zinc-700"
                >
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.612l4.158 3.96a.75.75 0 1 1-1.04 1.08l-5.5-5.25a.75.75 0 0 1 0-1.08l5.5-5.25a.75.75 0 1 1 1.04 1.08L5.612 9.25H16.25A.75.75 0 0 1 17 10Z" clip-rule="evenodd"/>
                    </svg>
                    <span class="hidden sm:inline">Kelas Saya</span>
                </a>
                <span class="inline-flex items-center gap-1.5 rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-[11px] font-semibold text-sky-700">
                    <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path d="M10.75 16.82A7.462 7.462 0 0 1 10 17c-.386 0-.766-.02-1.138-.06l-.136-.021a7.5 7.5 0 1 1 2.55-.079l-.276.04Z"/></svg>
                    Ecourse Workspace
                </span>
            </div>

            @if (session('status'))
                <div class="mb-5">
                    <x-ui.alert :variant="session('status_variant', 'info')" :title="session('status_title', 'Catatan')">
                        {{ session('status') }}
                    </x-ui.alert>
                </div>
            @endif

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1.7fr)_380px]">
                <div class="space-y-5">

                    {{-- Hero card --}}
                    <div class="overflow-hidden rounded-2xl border border-zinc-800 bg-[radial-gradient(circle_at_top_left,rgba(251,191,36,0.22),transparent_32%),linear-gradient(135deg,#111827_0%,#27272a_48%,#18181b_100%)] text-white shadow-[0_8px_32px_rgba(0,0,0,0.2)]">
                        <div class="grid gap-6 p-5 md:p-8 lg:grid-cols-[minmax(0,1.3fr)_260px] lg:items-end">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full border border-white/15 px-3 py-1 text-[11px] font-semibold tracking-[0.14em] text-white/60 uppercase">
                                        Learning Access
                                    </span>
                                    <span class="rounded-full border border-white/15 px-3 py-1 text-[11px] font-semibold tracking-[0.14em] text-white/70">
                                        {{ $userProduct->product?->title ?? 'Produk kelas' }}
                                    </span>
                                    <span class="inline-flex items-center gap-1 rounded-full border border-white/15 px-2.5 py-1 text-[11px] font-semibold text-white/70">
                                        @if ($course->usesSequentialLessons())
                                            <svg class="h-3 w-3 text-amber-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2 4.75A.75.75 0 0 1 2.75 4h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 4.75ZM2 10a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 10Zm0 5.25a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd"/></svg>
                                            Mode Bertahap
                                        @else
                                            <svg class="h-3 w-3 text-emerald-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                                            Mode Bebas
                                        @endif
                                    </span>
                                </div>

                                <h1 class="mt-4 max-w-3xl text-2xl font-bold tracking-tight text-white md:text-3xl">
                                    {{ $course->title }}
                                </h1>

                                @if (filled($course->short_description))
                                    <p class="mt-3 max-w-2xl text-sm leading-7 text-white/70">
                                        {{ $course->short_description }}
                                    </p>
                                @endif

                                <div class="mt-5 flex flex-wrap items-center gap-3">
                                    @if ($continueLesson)
                                        <a
                                            href="{{ route('my-courses.lessons.show', [$userProduct, $continueLesson]) }}"
                                            class="btn-3d-primary inline-flex items-center gap-2 rounded-xl bg-gradient-to-br from-amber-400 to-amber-500 px-5 py-2.5 text-sm font-bold text-white"
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M6.3 2.84A1.5 1.5 0 0 0 4 4.11v11.78a1.5 1.5 0 0 0 2.3 1.27l9.344-5.891a1.5 1.5 0 0 0 0-2.538L6.3 2.84Z"/>
                                            </svg>
                                            {{ $completedLessons > 0 ? 'Lanjutkan Belajar' : 'Mulai Belajar' }}
                                        </a>
                                    @endif

                                    <div class="inline-flex items-center gap-2 rounded-xl border border-white/12 bg-white/8 px-4 py-2.5 text-sm text-white/80">
                                        <svg class="h-4 w-4 text-emerald-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                                        <span class="font-semibold text-white">{{ $completedLessons }}</span> / {{ $totalLessons }} materi
                                    </div>
                                </div>
                            </div>

                            <div class="grid gap-3">
                                {{-- Progress card --}}
                                <div class="rounded-xl border border-white/10 bg-white/8 p-4 backdrop-blur">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="text-[11px] font-semibold uppercase tracking-[0.16em] text-white/50">Progres Kelas</div>
                                        <div class="text-3xl font-bold text-white">{{ $progressPercent }}%</div>
                                    </div>
                                    <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-white/10">
                                        <div
                                            class="progress-glow h-full rounded-full bg-gradient-to-r from-amber-400 to-amber-500 transition-all duration-700"
                                            style="width: {{ $progressPercent }}%"
                                        ></div>
                                    </div>
                                    <div class="mt-2.5 flex items-center justify-between text-[11px] text-white/50">
                                        <span class="flex items-center gap-1">
                                            <svg class="h-3 w-3 text-emerald-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                                            {{ $completedLessons }} selesai
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <svg class="h-3 w-3 text-amber-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-13a.75.75 0 0 0-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 0 0 0-1.5h-3.25V5Z" clip-rule="evenodd"/></svg>
                                            {{ $remainingLessons }} tersisa
                                        </span>
                                    </div>
                                </div>

                                {{-- Stats --}}
                                <div class="grid grid-cols-3 gap-2">
                                    <div class="rounded-xl border border-white/10 bg-black/20 p-3 text-center">
                                        <div class="flex items-center justify-center gap-1 text-[10px] text-white/50">
                                            <svg class="h-3 w-3 text-violet-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2 4.75A.75.75 0 0 1 2.75 4h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 4.75ZM2 10a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 10Zm0 5.25a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd"/></svg>
                                            Bab
                                        </div>
                                        <div class="mt-1.5 text-xl font-bold text-white">{{ $totalSections }}</div>
                                    </div>
                                    <div class="rounded-xl border border-white/10 bg-black/20 p-3 text-center">
                                        <div class="flex items-center justify-center gap-1 text-[10px] text-white/50">
                                            <svg class="h-3 w-3 text-sky-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm-1.5-9.5a1.5 1.5 0 1 1 3 0v4a1.5 1.5 0 0 1-3 0v-4ZM10 6a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
                                            Materi
                                        </div>
                                        <div class="mt-1.5 text-xl font-bold text-white">{{ $totalLessons }}</div>
                                    </div>
                                    <div class="rounded-xl border border-white/10 bg-black/20 p-3 text-center">
                                        <div class="flex items-center justify-center gap-1 text-[10px] text-white/50">
                                            <svg class="h-3 w-3 text-amber-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-13a.75.75 0 0 0-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 0 0 0-1.5h-3.25V5Z" clip-rule="evenodd"/></svg>
                                            Tersisa
                                        </div>
                                        <div class="mt-1.5 text-xl font-bold text-white">{{ $remainingLessons }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if (($displayLessons->count() ?? 0) === 0)
                        <x-ui.empty-state
                            title="Belum ada materi yang bisa ditampilkan"
                            description="Materi kelas ini belum tersedia untuk akun Anda saat ini."
                        />
                    @else
                        {{-- Info stats row --}}
                        <div class="grid gap-3 md:grid-cols-3">
                            <div class="overflow-hidden rounded-2xl border border-zinc-200/70 bg-white p-4 shadow-[0_2px_12px_rgba(0,0,0,0.05)]">
                                <div class="flex items-center gap-2.5">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-violet-100 to-violet-50">
                                        <svg class="h-4.5 w-4.5 text-violet-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm-1.5-9.5a1.5 1.5 0 1 1 3 0v4a1.5 1.5 0 0 1-3 0v-4ZM10 6a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
                                    </div>
                                    <div class="text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Kesiapan</div>
                                </div>
                                <div class="mt-2 text-2xl font-bold text-zinc-950">{{ $progressPercent }}%</div>
                                <p class="mt-1 text-xs leading-5 text-zinc-500">Ritme belajar tercatat walau ada materi bertahap</p>
                            </div>
                            <div class="overflow-hidden rounded-2xl border border-zinc-200/70 bg-white p-4 shadow-[0_2px_12px_rgba(0,0,0,0.05)]">
                                <div class="flex items-center gap-2.5">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-amber-100 to-amber-50">
                                        <svg class="h-4.5 w-4.5 text-amber-600" viewBox="0 0 20 20" fill="currentColor"><path d="M6.3 2.84A1.5 1.5 0 0 0 4 4.11v11.78a1.5 1.5 0 0 0 2.3 1.27l9.344-5.891a1.5 1.5 0 0 0 0-2.538L6.3 2.84Z"/></svg>
                                    </div>
                                    <div class="text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Fokus Berikutnya</div>
                                </div>
                                <div class="mt-2 truncate text-sm font-bold text-zinc-950">
                                    {{ $continueLesson?->title ?? 'Semua terbuka' }}
                                </div>
                                <p class="mt-1 truncate text-xs text-zinc-500">
                                    {{ $continueLesson?->section?->title ?? 'Pantau materi yang dijadwalkan' }}
                                </p>
                            </div>
                            <div class="overflow-hidden rounded-2xl border border-zinc-200/70 bg-white p-4 shadow-[0_2px_12px_rgba(0,0,0,0.05)]">
                                <div class="flex items-center gap-2.5">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-teal-100 to-teal-50">
                                        <svg class="h-4.5 w-4.5 text-teal-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2 4.75A.75.75 0 0 1 2.75 4h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 4.75ZM2 10a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 10Zm0 5.25a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd"/></svg>
                                    </div>
                                    <div class="text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Struktur Kelas</div>
                                </div>
                                <div class="mt-2 text-2xl font-bold text-zinc-950">{{ $sections->count() }}</div>
                                <p class="mt-1 text-xs text-zinc-500">Seksi terstruktur</p>
                            </div>
                        </div>

                        {{-- Lesson sections --}}
                        <div class="space-y-4">
                            @foreach ($sections as $section)
                                @php($sectionLessons = $lessonsBySection->get($section->id, collect()))
                                @php($sectionCompleted = $sectionLessons->filter(fn ($lesson) => ($progressByLessonId[$lesson->id] ?? null)?->value === 'completed')->count())

                                @if ($sectionLessons->count() > 0)
                                    <div class="overflow-hidden rounded-2xl border border-zinc-200/70 bg-white shadow-[0_2px_12px_rgba(0,0,0,0.05)]">
                                        {{-- Section header --}}
                                        <div class="border-b border-zinc-100 bg-gradient-to-r from-zinc-50 to-white px-5 py-4">
                                            <div class="flex flex-wrap items-start justify-between gap-3">
                                                <div class="flex items-start gap-3">
                                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-violet-500 to-violet-600 shadow-[0_3px_8px_rgba(124,58,237,0.3)]">
                                                        <svg class="h-4 w-4 text-white" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2 4.75A.75.75 0 0 1 2.75 4h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 4.75ZM2 10a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 10Zm0 5.25a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd"/></svg>
                                                    </div>
                                                    <div>
                                                        <div class="font-bold tracking-tight text-zinc-950">{{ $section->title }}</div>
                                                        @if (filled($section->description))
                                                            <div class="mt-0.5 max-w-2xl text-xs leading-5 text-zinc-500">{{ $section->description }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-1.5 rounded-full border border-zinc-200 bg-white px-3 py-1.5 text-xs font-semibold text-zinc-600">
                                                    <svg class="h-3.5 w-3.5 text-emerald-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                                                    {{ $sectionCompleted }}/{{ $sectionLessons->count() }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="divide-y divide-zinc-100">
                                            @foreach ($sectionLessons as $lesson)
                                                @php($status = $progressByLessonId[$lesson->id] ?? null)
                                                @php($access = $lessonAccessByLessonId[$lesson->id] ?? ['can_access' => true, 'state' => 'open', 'status_label' => 'Terbuka', 'message' => null])
                                                @php($isCompleted = $status?->value === 'completed')
                                                @php($isClickable = (bool) ($access['can_access'] ?? false))
                                                @php($state = $access['state'] ?? 'open')
                                                @php($lessonType = $lesson->lesson_type?->value ?? null)

                                                @php($typeIconHtml = match ($lessonType) {
                                                    'video_embed'     => '<div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-violet-100"><svg class="h-4 w-4 text-violet-600" viewBox="0 0 20 20" fill="currentColor"><path d="M6.3 2.84A1.5 1.5 0 0 0 4 4.11v11.78a1.5 1.5 0 0 0 2.3 1.27l9.344-5.891a1.5 1.5 0 0 0 0-2.538L6.3 2.84Z"/></svg></div>',
                                                    'article'         => '<div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-sky-100"><svg class="h-4 w-4 text-sky-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 0 1 2-2h4.586A2 2 0 0 1 12 2.586L15.414 6A2 2 0 0 1 16 7.414V16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4Zm2 6a1 1 0 0 1 1-1h6a1 1 0 1 1 0 2H7a1 1 0 0 1-1-1Zm1 3a1 1 0 1 0 0 2h6a1 1 0 1 0 0-2H7Z" clip-rule="evenodd"/></svg></div>',
                                                    'file_attachment' => '<div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-orange-100"><svg class="h-4 w-4 text-orange-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M15.621 4.379a3 3 0 0 0-4.242 0l-7 7a3 3 0 0 0 4.241 4.243h.001l.497-.5a.75.75 0 0 1 1.064 1.057l-.498.501-.002.002a4.5 4.5 0 0 1-6.364-6.364l7-7a4.5 4.5 0 0 1 6.368 6.36l-3.455 3.553A2.625 2.625 0 1 1 9.52 9.52l3.45-3.451a.75.75 0 1 1 1.061 1.06l-3.45 3.451a1.125 1.125 0 0 0 1.587 1.595l3.454-3.553a3 3 0 0 0 0-4.242Z" clip-rule="evenodd"/></svg></div>',
                                                    'external_link'   => '<div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-teal-100"><svg class="h-4 w-4 text-teal-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.25 5.5a.75.75 0 0 0-.75.75v8.5c0 .414.336.75.75.75h8.5a.75.75 0 0 0 .75-.75v-4a.75.75 0 0 1 1.5 0v4A2.25 2.25 0 0 1 12.75 17h-8.5A2.25 2.25 0 0 1 2 14.75v-8.5A2.25 2.25 0 0 1 4.25 4h5a.75.75 0 0 1 0 1.5h-5Z" clip-rule="evenodd"/><path fill-rule="evenodd" d="M6.194 12.753a.75.75 0 0 0 1.06.053L16.5 4.44v2.81a.75.75 0 0 0 1.5 0v-4.5a.75.75 0 0 0-.75-.75h-4.5a.75.75 0 0 0 0 1.5h2.553l-9.056 8.194a.75.75 0 0 0-.053 1.06Z" clip-rule="evenodd"/></svg></div>',
                                                    default           => '<div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-zinc-100"><svg class="h-4 w-4 text-zinc-500" viewBox="0 0 20 20" fill="currentColor"><path d="M10.75 16.82A7.462 7.462 0 0 1 10 17c-.386 0-.766-.02-1.138-.06l-.136-.021a7.5 7.5 0 1 1 2.55-.079l-.276.04Z"/></svg></div>',
                                                })

                                                @php($stateIconHtml = match ($state) {
                                                    'completed' => '<span class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-700"><svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>'.$access['status_label'].'</span>',
                                                    'scheduled' => '<span class="inline-flex items-center gap-1 rounded-full border border-sky-200 bg-sky-50 px-2.5 py-1 text-[10px] font-bold text-sky-700"><svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-13a.75.75 0 0 0-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 0 0 0-1.5h-3.25V5Z" clip-rule="evenodd"/></svg>'.$access['status_label'].'</span>',
                                                    'locked'    => '<span class="inline-flex items-center gap-1 rounded-full border border-rose-200 bg-rose-50 px-2.5 py-1 text-[10px] font-bold text-rose-700"><svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z" clip-rule="evenodd"/></svg>'.$access['status_label'].'</span>',
                                                    default     => '<span class="inline-flex items-center gap-1 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-[10px] font-bold text-amber-700"><svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path d="M6.3 2.84A1.5 1.5 0 0 0 4 4.11v11.78a1.5 1.5 0 0 0 2.3 1.27l9.344-5.891a1.5 1.5 0 0 0 0-2.538L6.3 2.84Z"/></svg>'.$access['status_label'].'</span>',
                                                })

                                                @if ($isClickable)
                                                    <a
                                                        href="{{ route('my-courses.lessons.show', [$userProduct, $lesson]) }}"
                                                        class="lesson-row-link group flex flex-col gap-3 px-5 py-4 md:flex-row md:items-center md:justify-between"
                                                    >
                                                        <div class="flex min-w-0 items-start gap-3">
                                                            {!! $typeIconHtml !!}
                                                            <div class="min-w-0">
                                                                <div class="font-semibold text-zinc-950">{{ $lesson->title }}</div>
                                                                <div class="mt-1.5 flex flex-wrap items-center gap-2 text-xs text-zinc-500">
                                                                    @if ($lesson->duration_minutes)
                                                                        <span class="flex items-center gap-1">
                                                                            <svg class="h-3 w-3 text-zinc-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-13a.75.75 0 0 0-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 0 0 0-1.5h-3.25V5Z" clip-rule="evenodd"/></svg>
                                                                            {{ $lesson->duration_minutes }} menit
                                                                        </span>
                                                                    @endif
                                                                    @if (filled($lesson->short_description))
                                                                        <span class="max-w-xs truncate text-zinc-400">{{ $lesson->short_description }}</span>
                                                                    @endif
                                                                    @if (! $lesson->isRequired())
                                                                        <span class="rounded-full border border-zinc-200 bg-white px-2 py-0.5 text-[10px] font-medium text-zinc-500">Opsional</span>
                                                                    @endif
                                                                </div>
                                                                @if (filled($access['message']) && ! $isCompleted)
                                                                    <div class="mt-1 text-xs text-zinc-500">{{ $access['message'] }}</div>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <div class="flex shrink-0 items-center gap-2.5">
                                                            {!! $stateIconHtml !!}
                                                            <svg class="lesson-arrow h-4 w-4 text-zinc-400 group-hover:text-zinc-700" viewBox="0 0 20 20" fill="currentColor">
                                                                <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/>
                                                            </svg>
                                                        </div>
                                                    </a>
                                                @else
                                                    <div class="flex flex-col gap-3 bg-zinc-50/60 px-5 py-4 md:flex-row md:items-center md:justify-between">
                                                        <div class="flex min-w-0 items-start gap-3 opacity-60">
                                                            {!! $typeIconHtml !!}
                                                            <div class="min-w-0">
                                                                <div class="font-semibold text-zinc-700">{{ $lesson->title }}</div>
                                                                <div class="mt-1.5 flex flex-wrap items-center gap-2 text-xs text-zinc-500">
                                                                    @if ($lesson->duration_minutes)
                                                                        <span class="flex items-center gap-1">
                                                                            <svg class="h-3 w-3 text-zinc-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-13a.75.75 0 0 0-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 0 0 0-1.5h-3.25V5Z" clip-rule="evenodd"/></svg>
                                                                            {{ $lesson->duration_minutes }} menit
                                                                        </span>
                                                                    @endif
                                                                    @if (! $lesson->isRequired())
                                                                        <span class="rounded-full border border-zinc-200 bg-white px-2 py-0.5 text-[10px] font-medium text-zinc-500">Opsional</span>
                                                                    @endif
                                                                </div>
                                                                @if (filled($access['message']))
                                                                    <div class="mt-1 text-xs text-zinc-500">{{ $access['message'] }}</div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="shrink-0">
                                                            {!! $stateIconHtml !!}
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach

                            @php($noSectionLessons = $lessonsBySection->get(0, collect()))

                            @if ($noSectionLessons->count() > 0)
                                <div class="overflow-hidden rounded-2xl border border-zinc-200/70 bg-white shadow-[0_2px_12px_rgba(0,0,0,0.05)]">
                                    <div class="border-b border-zinc-100 bg-gradient-to-r from-zinc-50 to-white px-5 py-4">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="flex items-center gap-3">
                                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-zinc-600 to-zinc-700 shadow-[0_3px_8px_rgba(0,0,0,0.2)]">
                                                    <svg class="h-4 w-4 text-white" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2 4.75A.75.75 0 0 1 2.75 4h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 4.75ZM2 10a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 10Zm0 5.25a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd"/></svg>
                                                </div>
                                                <div>
                                                    <div class="font-bold tracking-tight text-zinc-950">Materi Tambahan</div>
                                                    <div class="mt-0.5 text-xs text-zinc-500">Lesson di luar struktur seksi utama</div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-1.5 rounded-full border border-zinc-200 bg-white px-3 py-1.5 text-xs font-semibold text-zinc-600">
                                                <svg class="h-3.5 w-3.5 text-zinc-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2 4.75A.75.75 0 0 1 2.75 4h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 4.75ZM2 10a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 10Zm0 5.25a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd"/></svg>
                                                {{ $noSectionLessons->count() }} lesson
                                            </div>
                                        </div>
                                    </div>

                                    <div class="divide-y divide-zinc-100">
                                        @foreach ($noSectionLessons as $lesson)
                                            @php($status = $progressByLessonId[$lesson->id] ?? null)
                                            @php($access = $lessonAccessByLessonId[$lesson->id] ?? ['can_access' => true, 'state' => 'open', 'status_label' => 'Terbuka', 'message' => null])
                                            @php($isCompleted = $status?->value === 'completed')
                                            @php($isClickable = (bool) ($access['can_access'] ?? false))
                                            @php($state = $access['state'] ?? 'open')
                                            @php($lessonType = $lesson->lesson_type?->value ?? null)

                                            @php($typeIconHtml = match ($lessonType) {
                                                'video_embed'     => '<div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-violet-100"><svg class="h-4 w-4 text-violet-600" viewBox="0 0 20 20" fill="currentColor"><path d="M6.3 2.84A1.5 1.5 0 0 0 4 4.11v11.78a1.5 1.5 0 0 0 2.3 1.27l9.344-5.891a1.5 1.5 0 0 0 0-2.538L6.3 2.84Z"/></svg></div>',
                                                'article'         => '<div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-sky-100"><svg class="h-4 w-4 text-sky-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 0 1 2-2h4.586A2 2 0 0 1 12 2.586L15.414 6A2 2 0 0 1 16 7.414V16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4Zm2 6a1 1 0 0 1 1-1h6a1 1 0 1 1 0 2H7a1 1 0 0 1-1-1Zm1 3a1 1 0 1 0 0 2h6a1 1 0 1 0 0-2H7Z" clip-rule="evenodd"/></svg></div>',
                                                'file_attachment' => '<div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-orange-100"><svg class="h-4 w-4 text-orange-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M15.621 4.379a3 3 0 0 0-4.242 0l-7 7a3 3 0 0 0 4.241 4.243h.001l.497-.5a.75.75 0 0 1 1.064 1.057l-.498.501-.002.002a4.5 4.5 0 0 1-6.364-6.364l7-7a4.5 4.5 0 0 1 6.368 6.36l-3.455 3.553A2.625 2.625 0 1 1 9.52 9.52l3.45-3.451a.75.75 0 1 1 1.061 1.06l-3.45 3.451a1.125 1.125 0 0 0 1.587 1.595l3.454-3.553a3 3 0 0 0 0-4.242Z" clip-rule="evenodd"/></svg></div>',
                                                'external_link'   => '<div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-teal-100"><svg class="h-4 w-4 text-teal-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.25 5.5a.75.75 0 0 0-.75.75v8.5c0 .414.336.75.75.75h8.5a.75.75 0 0 0 .75-.75v-4a.75.75 0 0 1 1.5 0v4A2.25 2.25 0 0 1 12.75 17h-8.5A2.25 2.25 0 0 1 2 14.75v-8.5A2.25 2.25 0 0 1 4.25 4h5a.75.75 0 0 1 0 1.5h-5Z" clip-rule="evenodd"/><path fill-rule="evenodd" d="M6.194 12.753a.75.75 0 0 0 1.06.053L16.5 4.44v2.81a.75.75 0 0 0 1.5 0v-4.5a.75.75 0 0 0-.75-.75h-4.5a.75.75 0 0 0 0 1.5h2.553l-9.056 8.194a.75.75 0 0 0-.053 1.06Z" clip-rule="evenodd"/></svg></div>',
                                                default           => '<div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-zinc-100"><svg class="h-4 w-4 text-zinc-500" viewBox="0 0 20 20" fill="currentColor"><path d="M10.75 16.82A7.462 7.462 0 0 1 10 17c-.386 0-.766-.02-1.138-.06l-.136-.021a7.5 7.5 0 1 1 2.55-.079l-.276.04Z"/></svg></div>',
                                            })

                                            @php($stateIconHtml = match ($state) {
                                                'completed' => '<span class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-700"><svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>'.$access['status_label'].'</span>',
                                                'scheduled' => '<span class="inline-flex items-center gap-1 rounded-full border border-sky-200 bg-sky-50 px-2.5 py-1 text-[10px] font-bold text-sky-700"><svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-13a.75.75 0 0 0-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 0 0 0-1.5h-3.25V5Z" clip-rule="evenodd"/></svg>'.$access['status_label'].'</span>',
                                                'locked'    => '<span class="inline-flex items-center gap-1 rounded-full border border-rose-200 bg-rose-50 px-2.5 py-1 text-[10px] font-bold text-rose-700"><svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z" clip-rule="evenodd"/></svg>'.$access['status_label'].'</span>',
                                                default     => '<span class="inline-flex items-center gap-1 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-[10px] font-bold text-amber-700"><svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path d="M6.3 2.84A1.5 1.5 0 0 0 4 4.11v11.78a1.5 1.5 0 0 0 2.3 1.27l9.344-5.891a1.5 1.5 0 0 0 0-2.538L6.3 2.84Z"/></svg>'.$access['status_label'].'</span>',
                                            })

                                            @if ($isClickable)
                                                <a
                                                    href="{{ route('my-courses.lessons.show', [$userProduct, $lesson]) }}"
                                                    class="lesson-row-link group flex flex-col gap-3 px-5 py-4 md:flex-row md:items-center md:justify-between"
                                                >
                                                    <div class="flex min-w-0 items-start gap-3">
                                                        {!! $typeIconHtml !!}
                                                        <div class="min-w-0">
                                                            <div class="font-semibold text-zinc-950">{{ $lesson->title }}</div>
                                                            <div class="mt-1.5 flex flex-wrap items-center gap-2 text-xs text-zinc-500">
                                                                @if ($lesson->duration_minutes)
                                                                    <span class="flex items-center gap-1">
                                                                        <svg class="h-3 w-3 text-zinc-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-13a.75.75 0 0 0-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 0 0 0-1.5h-3.25V5Z" clip-rule="evenodd"/></svg>
                                                                        {{ $lesson->duration_minutes }} menit
                                                                    </span>
                                                                @endif
                                                                @if (! $lesson->isRequired())
                                                                    <span class="rounded-full border border-zinc-200 bg-white px-2 py-0.5 text-[10px] font-medium text-zinc-500">Opsional</span>
                                                                @endif
                                                            </div>
                                                            @if (filled($access['message']) && ! $isCompleted)
                                                                <div class="mt-1 text-xs text-zinc-500">{{ $access['message'] }}</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="flex shrink-0 items-center gap-2.5">
                                                        {!! $stateIconHtml !!}
                                                        <svg class="lesson-arrow h-4 w-4 text-zinc-400 group-hover:text-zinc-700" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/>
                                                        </svg>
                                                    </div>
                                                </a>
                                            @else
                                                <div class="flex flex-col gap-3 bg-zinc-50/60 px-5 py-4 md:flex-row md:items-center md:justify-between">
                                                    <div class="flex min-w-0 items-start gap-3 opacity-60">
                                                        {!! $typeIconHtml !!}
                                                        <div class="min-w-0">
                                                            <div class="font-semibold text-zinc-700">{{ $lesson->title }}</div>
                                                            <div class="mt-1.5 flex flex-wrap items-center gap-2 text-xs text-zinc-500">
                                                                @if ($lesson->duration_minutes)
                                                                    <span class="flex items-center gap-1">
                                                                        <svg class="h-3 w-3 text-zinc-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-13a.75.75 0 0 0-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 0 0 0-1.5h-3.25V5Z" clip-rule="evenodd"/></svg>
                                                                        {{ $lesson->duration_minutes }} menit
                                                                    </span>
                                                                @endif
                                                                @if (! $lesson->isRequired())
                                                                    <span class="rounded-full border border-zinc-200 bg-white px-2 py-0.5 text-[10px] font-medium text-zinc-500">Opsional</span>
                                                                @endif
                                                            </div>
                                                            @if (filled($access['message']))
                                                                <div class="mt-1 text-xs text-zinc-500">{{ $access['message'] }}</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="shrink-0">
                                                        {!! $stateIconHtml !!}
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Sidebar --}}
                <div class="space-y-5 xl:sticky xl:top-24 xl:self-start">

                    {{-- Study plan --}}
                    <div class="overflow-hidden rounded-2xl border border-zinc-200/70 bg-white p-5 shadow-[0_4px_20px_rgba(0,0,0,0.06)]">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-gradient-to-br from-amber-400 to-amber-500 shadow-[0_3px_8px_rgba(245,158,11,0.3)]">
                                <svg class="h-4 w-4 text-white" viewBox="0 0 20 20" fill="currentColor"><path d="M6.3 2.84A1.5 1.5 0 0 0 4 4.11v11.78a1.5 1.5 0 0 0 2.3 1.27l9.344-5.891a1.5 1.5 0 0 0 0-2.538L6.3 2.84Z"/></svg>
                            </div>
                            <div class="text-sm font-bold text-zinc-950">Rencana Belajar</div>
                        </div>

                        <div class="mt-4 space-y-2.5">
                            <div class="rounded-xl bg-amber-50 p-3.5">
                                <div class="flex items-center gap-2 text-xs font-bold text-amber-800">
                                    <svg class="h-3.5 w-3.5 text-amber-500" viewBox="0 0 20 20" fill="currentColor"><path d="M6.3 2.84A1.5 1.5 0 0 0 4 4.11v11.78a1.5 1.5 0 0 0 2.3 1.27l9.344-5.891a1.5 1.5 0 0 0 0-2.538L6.3 2.84Z"/></svg>
                                    Checkpoint berikutnya
                                </div>
                                <div class="mt-1.5 text-sm font-semibold leading-5 text-zinc-900">
                                    {{ $continueLesson?->title ?? 'Tidak ada materi terbuka berikutnya.' }}
                                </div>
                                @if ($continueLesson?->section?->title)
                                    <div class="mt-1 text-[11px] text-amber-700/70">{{ $continueLesson->section->title }}</div>
                                @endif
                            </div>

                            <div class="rounded-xl border border-zinc-100 bg-zinc-50 p-3.5">
                                <div class="flex items-center gap-2 text-xs font-bold text-zinc-600">
                                    <svg class="h-3.5 w-3.5 text-emerald-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                                    Yang sudah tercapai
                                </div>
                                <div class="mt-1.5 text-sm leading-5 text-zinc-700">
                                    <span class="font-bold text-zinc-950">{{ $completedLessons }}</span> lesson dituntaskan,
                                    tinggal <span class="font-bold text-zinc-950">{{ $remainingLessons }}</span> lagi.
                                </div>
                            </div>
                        </div>

                        @if ($continueLesson)
                            <div class="mt-4">
                                <a
                                    href="{{ route('my-courses.lessons.show', [$userProduct, $continueLesson]) }}"
                                    class="btn-3d-primary inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-amber-400 to-amber-500 py-2.5 text-sm font-bold text-white"
                                >
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M6.3 2.84A1.5 1.5 0 0 0 4 4.11v11.78a1.5 1.5 0 0 0 2.3 1.27l9.344-5.891a1.5 1.5 0 0 0 0-2.538L6.3 2.84Z"/></svg>
                                    Buka Materi Berikutnya
                                </a>
                            </div>
                        @endif
                    </div>

                    {{-- Tips --}}
                    <div class="overflow-hidden rounded-2xl border border-zinc-200/70 bg-white p-5 shadow-[0_4px_20px_rgba(0,0,0,0.06)]">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-gradient-to-br from-sky-400 to-sky-500 shadow-[0_3px_8px_rgba(56,189,248,0.3)]">
                                <svg class="h-4 w-4 text-white" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd"/></svg>
                            </div>
                            <div class="text-sm font-bold text-zinc-950">Panduan Status</div>
                        </div>

                        <div class="mt-4 space-y-2.5">
                            <div class="flex items-start gap-3 rounded-xl border border-amber-100 bg-amber-50 p-3">
                                <div class="tip-icon-badge bg-amber-100">
                                    <svg class="h-4 w-4 text-amber-600" viewBox="0 0 20 20" fill="currentColor"><path d="M6.3 2.84A1.5 1.5 0 0 0 4 4.11v11.78a1.5 1.5 0 0 0 2.3 1.27l9.344-5.891a1.5 1.5 0 0 0 0-2.538L6.3 2.84Z"/></svg>
                                </div>
                                <div class="text-xs leading-5 text-zinc-700">
                                    <span class="font-bold text-amber-700">Terbuka</span> — materi sudah bisa dipelajari sekarang
                                </div>
                            </div>
                            <div class="flex items-start gap-3 rounded-xl border border-rose-100 bg-rose-50 p-3">
                                <div class="tip-icon-badge bg-rose-100">
                                    <svg class="h-4 w-4 text-rose-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z" clip-rule="evenodd"/></svg>
                                </div>
                                <div class="text-xs leading-5 text-zinc-700">
                                    <span class="font-bold text-rose-700">Terkunci</span> — ada materi wajib sebelumnya yang belum selesai
                                </div>
                            </div>
                            <div class="flex items-start gap-3 rounded-xl border border-sky-100 bg-sky-50 p-3">
                                <div class="tip-icon-badge bg-sky-100">
                                    <svg class="h-4 w-4 text-sky-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.75 2a.75.75 0 0 1 .75.75V4h7V2.75a.75.75 0 0 1 1.5 0V4h.25A2.75 2.75 0 0 1 18 6.75v8.5A2.75 2.75 0 0 1 15.25 18H4.75A2.75 2.75 0 0 1 2 15.25v-8.5A2.75 2.75 0 0 1 4.75 4H5V2.75A.75.75 0 0 1 5.75 2Z" clip-rule="evenodd"/></svg>
                                </div>
                                <div class="text-xs leading-5 text-zinc-700">
                                    <span class="font-bold text-sky-700">Dijadwalkan</span> — akan terbuka sesuai tanggal yang ditentukan
                                </div>
                            </div>
                            <div class="flex items-start gap-3 rounded-xl border border-emerald-100 bg-emerald-50 p-3">
                                <div class="tip-icon-badge bg-emerald-100">
                                    <svg class="h-4 w-4 text-emerald-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                                </div>
                                <div class="text-xs leading-5 text-zinc-700">
                                    <span class="font-bold text-emerald-700">Selesai</span> — sudah berhasil kamu tandai sebagai selesai
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @include('partials.user-dashboard-footer')
    </div>
@endcomponent
