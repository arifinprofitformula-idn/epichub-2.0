<x-layouts::public :title="$course->title">
    <section class="mx-auto max-w-[var(--container-5xl)] px-4 py-10">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <x-ui.button variant="ghost" size="sm" :href="route('my-courses.index')">
                ← Kembali
            </x-ui.button>
        </div>

        <div class="grid gap-6 lg:grid-cols-5">
            <div class="lg:col-span-3">
                <x-ui.card class="p-6 md:p-8">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-zinc-900 dark:text-white">
                                {{ $course->title }}
                            </div>
                            <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                                {{ $userProduct->product?->title ?? '-' }}
                            </div>
                        </div>
                        <x-ui.badge variant="info">
                            {{ $progressPercent }}%
                        </x-ui.badge>
                    </div>

                    <div class="mt-5">
                        <div class="h-2 w-full overflow-hidden rounded-full bg-zinc-200/70 dark:bg-zinc-800">
                            <div class="h-2 rounded-full bg-zinc-900 dark:bg-white" style="width: {{ $progressPercent }}%"></div>
                        </div>
                        <div class="mt-2 text-xs text-zinc-600 dark:text-zinc-300">
                            {{ $completedLessons }} dari {{ $totalLessons }} lesson selesai
                        </div>
                    </div>

                    @if (filled($course->short_description))
                        <div class="mt-6 text-sm text-zinc-600 dark:text-zinc-300">
                            {{ $course->short_description }}
                        </div>
                    @endif
                </x-ui.card>

                <div class="mt-6 grid gap-4">
                    @php($lessonsBySection = $lessons->groupBy(fn ($l) => $l->course_section_id ?? 0))

                    @if (($lessons->count() ?? 0) === 0)
                        <x-ui.empty-state
                            title="Belum ada lesson"
                            description="Materi kelas ini belum ditambahkan."
                        />
                    @else
                        @foreach ($sections as $section)
                            @php($sectionLessons = $lessonsBySection->get($section->id, collect()))

                            @if ($sectionLessons->count() > 0)
                                <x-ui.card class="p-6">
                                    <div class="text-sm font-semibold text-zinc-900 dark:text-white">
                                        {{ $section->title }}
                                    </div>
                                    @if (filled($section->description))
                                        <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                                            {{ $section->description }}
                                        </div>
                                    @endif

                                    <div class="mt-4 grid gap-2">
                                        @foreach ($sectionLessons as $lesson)
                                            @php($status = $progressByLessonId[$lesson->id] ?? null)
                                            @php($isCompleted = $status?->value === 'completed')

                                            <a
                                                href="{{ route('my-courses.lessons.show', [$userProduct, $lesson]) }}"
                                                class="flex items-center justify-between gap-4 rounded-[var(--radius-xl)] border border-zinc-200/70 p-4 text-sm hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-900/50"
                                            >
                                                <div class="min-w-0">
                                                    <div class="font-semibold text-zinc-900 dark:text-white">
                                                        {{ $lesson->title }}
                                                    </div>
                                                    <div class="mt-1 text-xs text-zinc-600 dark:text-zinc-300">
                                                        {{ $lesson->lesson_type?->label() ?? ($lesson->lesson_type?->value ?? '-') }}
                                                    </div>
                                                </div>

                                                @if ($isCompleted)
                                                    <x-ui.badge variant="success">Selesai</x-ui.badge>
                                                @else
                                                    <x-ui.badge variant="neutral">Belum</x-ui.badge>
                                                @endif
                                            </a>
                                        @endforeach
                                    </div>
                                </x-ui.card>
                            @endif
                        @endforeach

                        @php($noSectionLessons = $lessonsBySection->get(0, collect()))

                        @if ($noSectionLessons->count() > 0)
                            <x-ui.card class="p-6">
                                <div class="text-sm font-semibold text-zinc-900 dark:text-white">
                                    Pelajaran
                                </div>
                                <div class="mt-4 grid gap-2">
                                    @foreach ($noSectionLessons as $lesson)
                                        @php($status = $progressByLessonId[$lesson->id] ?? null)
                                        @php($isCompleted = $status?->value === 'completed')

                                        <a
                                            href="{{ route('my-courses.lessons.show', [$userProduct, $lesson]) }}"
                                            class="flex items-center justify-between gap-4 rounded-[var(--radius-xl)] border border-zinc-200/70 p-4 text-sm hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-900/50"
                                        >
                                            <div class="min-w-0">
                                                <div class="font-semibold text-zinc-900 dark:text-white">
                                                    {{ $lesson->title }}
                                                </div>
                                                <div class="mt-1 text-xs text-zinc-600 dark:text-zinc-300">
                                                    {{ $lesson->lesson_type?->label() ?? ($lesson->lesson_type?->value ?? '-') }}
                                                </div>
                                            </div>

                                            @if ($isCompleted)
                                                <x-ui.badge variant="success">Selesai</x-ui.badge>
                                            @else
                                                <x-ui.badge variant="neutral">Belum</x-ui.badge>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            </x-ui.card>
                        @endif
                    @endif
                </div>
            </div>

            <div class="lg:col-span-2">
                <x-ui.card class="p-6 md:p-8">
                    <div class="text-sm font-semibold text-zinc-900 dark:text-white">Aksi</div>
                    <div class="mt-3 text-sm text-zinc-600 dark:text-zinc-300">
                        Mulai dari lesson pertama atau lanjutkan dari yang terakhir kamu buka.
                    </div>

                    @php($firstLesson = $lessons->sortBy('sort_order')->first())

                    @if ($firstLesson)
                        <div class="mt-6">
                            <x-ui.button variant="primary" :href="route('my-courses.lessons.show', [$userProduct, $firstLesson])">
                                Mulai belajar
                            </x-ui.button>
                        </div>
                    @endif
                </x-ui.card>
            </div>
        </div>
    </section>
</x-layouts::public>

