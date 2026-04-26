@component('layouts::app', ['title' => $lesson->title])
    @php
        $type = $lesson->lesson_type?->value ?? null;
        $lessonsBySection = $lessons->groupBy(fn ($row) => $row->course_section_id ?? 0);
        $remainingLessons = max($totalLessons - $completedLessons, 0);
        $isCompleted = $currentProgressStatus?->value === 'completed';
        $isInProgress = $currentProgressStatus?->value === 'in_progress';

        $rawVideoUrl = trim((string) ($lesson->video_url ?? ''));
        $videoEmbedUrl = null;

        if ($rawVideoUrl !== '' && filter_var($rawVideoUrl, FILTER_VALIDATE_URL)) {
            $host = strtolower((string) parse_url($rawVideoUrl, PHP_URL_HOST));
            $path = trim((string) parse_url($rawVideoUrl, PHP_URL_PATH), '/');
            parse_str((string) parse_url($rawVideoUrl, PHP_URL_QUERY), $query);

            if (str_contains($host, 'youtu.be')) {
                $videoId = $path !== '' ? explode('/', $path)[0] : null;
                $videoEmbedUrl = $videoId ? 'https://www.youtube.com/embed/'.$videoId.'?rel=0' : null;
            } elseif (str_contains($host, 'youtube.com') || str_contains($host, 'youtube-nocookie.com')) {
                $segments = $path === '' ? [] : explode('/', $path);
                $videoId = $query['v'] ?? null;

                if (! $videoId && count($segments) >= 2 && in_array($segments[0], ['embed', 'shorts', 'live'], true)) {
                    $videoId = $segments[1];
                }

                $videoEmbedUrl = $videoId ? 'https://www.youtube.com/embed/'.$videoId.'?rel=0' : $rawVideoUrl;
            } elseif (str_contains($host, 'vimeo.com')) {
                $segments = $path === '' ? [] : array_values(array_filter(explode('/', $path)));
                $videoId = null;

                if (count($segments) >= 2 && $segments[0] === 'video') {
                    $videoId = $segments[1];
                } elseif (count($segments) >= 1 && ctype_digit($segments[count($segments) - 1])) {
                    $videoId = $segments[count($segments) - 1];
                }

                $videoEmbedUrl = $videoId ? 'https://player.vimeo.com/video/'.$videoId : $rawVideoUrl;
            } elseif (str_contains($host, 'loom.com')) {
                $segments = $path === '' ? [] : array_values(array_filter(explode('/', $path)));
                $videoId = count($segments) >= 2 && in_array($segments[0], ['share', 'embed'], true) ? $segments[1] : null;
                $videoEmbedUrl = $videoId ? 'https://www.loom.com/embed/'.$videoId : $rawVideoUrl;
            } else {
                $videoEmbedUrl = $rawVideoUrl;
            }
        }
    @endphp

    <div class="mx-auto flex min-h-[calc(100vh-1rem)] w-full max-w-[min(1640px,calc(100vw-24px))] flex-col px-0 pb-0 pt-0 md:min-h-screen md:pb-0">
        @include('partials.user-dashboard-header')

        <section class="px-1 py-5 md:px-5 lg:px-6">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <x-ui.button variant="ghost" size="sm" :href="route('my-courses.show', $userProduct)">
                    <- Kelas
                </x-ui.button>

                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <x-ui.badge variant="info">{{ $lesson->lesson_type?->label() ?? ($lesson->lesson_type?->value ?? '-') }}</x-ui.badge>
                    <x-ui.badge variant="neutral">{{ $currentLessonIndex }}/{{ $totalLessons }}</x-ui.badge>
                </div>
            </div>

            <div class="grid gap-5 xl:grid-cols-[300px_minmax(0,1fr)]">
                <aside class="xl:sticky xl:top-24 xl:self-start">
                    <x-ui.card class="overflow-hidden p-0">
                        <div class="border-b border-zinc-200/70 bg-zinc-950 px-5 py-5 text-white">
                            <div class="text-sm font-semibold">{{ $course->title }}</div>
                            <div class="mt-1 text-xs text-white/60">
                                {{ $completedLessons }} selesai • {{ $remainingLessons }} tersisa
                            </div>
                            <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-white/10">
                                <div class="h-full rounded-full bg-[linear-gradient(90deg,#fbbf24_0%,#f59e0b_100%)]" style="width: {{ $progressPercent }}%"></div>
                            </div>
                        </div>

                        <div class="max-h-[calc(100vh-220px)] space-y-3 overflow-y-auto p-4">
                            @foreach ($sections as $section)
                                @php($sectionLessons = $lessonsBySection->get($section->id, collect()))

                                @if ($sectionLessons->count() > 0)
                                    <div class="space-y-2">
                                        <div class="px-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-zinc-500">
                                            {{ $section->title }}
                                        </div>

                                        @foreach ($sectionLessons as $sectionLesson)
                                            @php($status = $progressByLessonId[$sectionLesson->id] ?? null)
                                            @php($rowCompleted = $status?->value === 'completed')
                                            @php($rowInProgress = $status?->value === 'in_progress')
                                            @php($isCurrentLesson = $sectionLesson->id === $lesson->id)

                                            <a
                                                href="{{ route('my-courses.lessons.show', [$userProduct, $sectionLesson]) }}"
                                                class="block rounded-[var(--radius-md)] border px-3 py-3 transition {{ $isCurrentLesson ? 'border-amber-300 bg-amber-50' : 'border-zinc-200/70 bg-white hover:bg-zinc-50' }}"
                                            >
                                                <div class="flex items-start gap-3">
                                                    <div class="mt-0.5 h-2.5 w-2.5 shrink-0 rounded-full {{ $rowCompleted ? 'bg-emerald-500' : ($rowInProgress ? 'bg-amber-500' : 'bg-zinc-300') }}"></div>
                                                    <div class="min-w-0">
                                                        <div class="truncate text-sm font-medium {{ $isCurrentLesson ? 'text-amber-900' : 'text-zinc-900' }}">
                                                            {{ $sectionLesson->title }}
                                                        </div>
                                                        @if ($sectionLesson->duration_minutes)
                                                            <div class="mt-1 text-[11px] text-zinc-500">{{ $sectionLesson->duration_minutes }} menit</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            @endforeach

                            @php($noSectionLessons = $lessonsBySection->get(0, collect()))

                            @if ($noSectionLessons->count() > 0)
                                <div class="space-y-2">
                                    <div class="px-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-zinc-500">
                                        Materi Tambahan
                                    </div>

                                    @foreach ($noSectionLessons as $sectionLesson)
                                        @php($status = $progressByLessonId[$sectionLesson->id] ?? null)
                                        @php($rowCompleted = $status?->value === 'completed')
                                        @php($rowInProgress = $status?->value === 'in_progress')
                                        @php($isCurrentLesson = $sectionLesson->id === $lesson->id)

                                        <a
                                            href="{{ route('my-courses.lessons.show', [$userProduct, $sectionLesson]) }}"
                                            class="block rounded-[var(--radius-md)] border px-3 py-3 transition {{ $isCurrentLesson ? 'border-amber-300 bg-amber-50' : 'border-zinc-200/70 bg-white hover:bg-zinc-50' }}"
                                        >
                                            <div class="flex items-start gap-3">
                                                <div class="mt-0.5 h-2.5 w-2.5 shrink-0 rounded-full {{ $rowCompleted ? 'bg-emerald-500' : ($rowInProgress ? 'bg-amber-500' : 'bg-zinc-300') }}"></div>
                                                <div class="min-w-0">
                                                    <div class="truncate text-sm font-medium {{ $isCurrentLesson ? 'text-amber-900' : 'text-zinc-900' }}">
                                                        {{ $sectionLesson->title }}
                                                    </div>
                                                    @if ($sectionLesson->duration_minutes)
                                                        <div class="mt-1 text-[11px] text-zinc-500">{{ $sectionLesson->duration_minutes }} menit</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </x-ui.card>
                </aside>

                <main class="min-w-0 space-y-4">
                    <x-ui.card class="overflow-hidden p-0 shadow-[var(--shadow-card)]">
                        <div class="border-b border-zinc-200/70 px-4 py-4 md:px-6">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h1 class="truncate text-xl font-semibold tracking-tight text-zinc-950 md:text-2xl">
                                        {{ $lesson->title }}
                                    </h1>
                                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-zinc-500">
                                        <span>{{ $lesson->section?->title ?? 'Materi mandiri' }}</span>
                                        @if ($lesson->duration_minutes)
                                            <span>•</span>
                                            <span>{{ $lesson->duration_minutes }} menit</span>
                                        @endif
                                        <span>•</span>
                                        <span>{{ $progressPercent }}% progres</span>
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('my-courses.lessons.complete', [$userProduct, $lesson]) }}">
                                    @csrf
                                    <x-ui.button variant="primary" class="min-w-[170px]">
                                        {{ $isCompleted ? 'Sudah selesai' : 'Tandai selesai' }}
                                    </x-ui.button>
                                </form>
                            </div>
                        </div>

                        <div class="bg-zinc-950">
                            @if ($type === 'video_embed')
                                @if ($videoEmbedUrl)
                                    <div class="aspect-video w-full">
                                        <iframe
                                            src="{{ $videoEmbedUrl }}"
                                            class="h-full w-full"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                            allowfullscreen
                                            loading="eager"
                                            referrerpolicy="strict-origin-when-cross-origin"
                                            title="{{ $lesson->title }}"
                                        ></iframe>
                                    </div>
                                @else
                                    <div class="p-6">
                                        <x-ui.alert variant="info" title="Video belum siap">
                                            URL video belum valid atau belum mendukung mode embed.
                                        </x-ui.alert>
                                    </div>
                                @endif
                            @elseif ($type === 'article')
                                <div class="bg-white p-6 md:p-8">
                                    <div class="prose prose-zinc max-w-none prose-headings:tracking-tight prose-p:leading-7">
                                        {!! $lesson->content !!}
                                    </div>
                                </div>
                            @elseif ($type === 'file_attachment')
                                <div class="bg-white p-6 md:p-8">
                                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-[var(--radius-lg)] border border-zinc-200/70 bg-zinc-50 p-4">
                                        <div class="text-sm font-medium text-zinc-900">Attachment materi</div>
                                        <x-ui.button variant="primary" :href="route('my-courses.lessons.download', [$userProduct, $lesson])">
                                            Download
                                        </x-ui.button>
                                    </div>

                                    @if (filled($lesson->content))
                                        <div class="mt-5 prose prose-zinc max-w-none prose-p:leading-7">
                                            {!! $lesson->content !!}
                                        </div>
                                    @endif
                                </div>
                            @elseif ($type === 'external_link')
                                <div class="bg-white p-6 md:p-8">
                                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-[var(--radius-lg)] border border-zinc-200/70 bg-zinc-50 p-4">
                                        <div class="text-sm font-medium text-zinc-900">Resource eksternal</div>
                                        <x-ui.button variant="primary" :href="route('my-courses.lessons.open', [$userProduct, $lesson])">
                                            Buka link
                                        </x-ui.button>
                                    </div>

                                    @if (filled($lesson->content))
                                        <div class="mt-5 prose prose-zinc max-w-none prose-p:leading-7">
                                            {!! $lesson->content !!}
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="p-6">
                                    <x-ui.alert variant="info" title="Catatan">
                                        Tipe lesson belum didukung.
                                    </x-ui.alert>
                                </div>
                            @endif
                        </div>

                        @if ($type === 'video_embed' && filled($lesson->content))
                            <div class="border-t border-zinc-200/70 bg-white px-4 py-5 md:px-6">
                                <div class="prose prose-zinc max-w-none prose-p:leading-7">
                                    {!! $lesson->content !!}
                                </div>
                            </div>
                        @endif
                    </x-ui.card>

                    @if (($downloadableAttachments->count() ?? 0) > 0)
                        <x-ui.card class="p-5 md:p-6">
                            <div class="text-base font-semibold text-zinc-950">Download & Resource Materi</div>
                            <div class="mt-4 space-y-3">
                                @foreach ($downloadableAttachments as $attachment)
                                    <div class="flex flex-col gap-3 rounded-[var(--radius-lg)] border border-zinc-200/70 bg-white p-4 md:flex-row md:items-center md:justify-between">
                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <div class="text-sm font-semibold text-zinc-900">
                                                    {{ $attachment['title'] }}
                                                </div>
                                                <span class="inline-flex items-center rounded-full border border-zinc-200 bg-zinc-50 px-2 py-0.5 text-[11px] font-medium text-zinc-600">
                                                    {{ $attachment['badge_label'] }}
                                                </span>
                                            </div>
                                            <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-zinc-500">
                                                @if (filled($attachment['description']))
                                                    <span>{{ $attachment['description'] }}</span>
                                                @endif
                                                @if (filled($attachment['size_label']))
                                                    <span>•</span>
                                                    <span>{{ $attachment['size_label'] }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <x-ui.button
                                            variant="primary"
                                            size="sm"
                                            :href="$attachment['url']"
                                            :target="$attachment['open_in_new_tab'] ? '_blank' : null"
                                            :rel="$attachment['open_in_new_tab'] ? 'noopener noreferrer' : null"
                                        >
                                            {{ $attachment['button_label'] }}
                                        </x-ui.button>
                                    </div>
                                @endforeach
                            </div>
                        </x-ui.card>
                    @endif

                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex flex-wrap items-center gap-2">
                            @if ($prevLessonId)
                                <x-ui.button variant="ghost" size="sm" :href="route('my-courses.lessons.show', [$userProduct, $prevLessonId])">
                                    <- Sebelumnya
                                </x-ui.button>
                            @endif
                            @if ($nextLessonId)
                                <x-ui.button variant="ghost" size="sm" :href="route('my-courses.lessons.show', [$userProduct, $nextLessonId])">
                                    Berikutnya ->
                                </x-ui.button>
                            @endif
                        </div>

                        <div class="text-xs text-zinc-500">
                            {{ $isCompleted ? 'Lesson selesai' : ($isInProgress ? 'Sedang dipelajari' : 'Belum dimulai') }}
                        </div>
                    </div>
                </main>
            </div>
        </section>

        @include('partials.user-dashboard-footer')
    </div>
@endcomponent
