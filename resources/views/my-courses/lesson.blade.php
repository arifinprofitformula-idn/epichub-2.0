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

        $typeIcon = match ($type) {
            'video_embed' => '<svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M6.3 2.84A1.5 1.5 0 0 0 4 4.11v11.78a1.5 1.5 0 0 0 2.3 1.27l9.344-5.891a1.5 1.5 0 0 0 0-2.538L6.3 2.84Z"/></svg>',
            'article' => '<svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 0 1 2-2h4.586A2 2 0 0 1 12 2.586L15.414 6A2 2 0 0 1 16 7.414V16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4Zm2 6a1 1 0 0 1 1-1h6a1 1 0 1 1 0 2H7a1 1 0 0 1-1-1Zm1 3a1 1 0 1 0 0 2h6a1 1 0 1 0 0-2H7Z" clip-rule="evenodd"/></svg>',
            'file_attachment' => '<svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M15.621 4.379a3 3 0 0 0-4.242 0l-7 7a3 3 0 0 0 4.241 4.243h.001l.497-.5a.75.75 0 0 1 1.064 1.057l-.498.501-.002.002a4.5 4.5 0 0 1-6.364-6.364l7-7a4.5 4.5 0 0 1 6.368 6.36l-3.455 3.553A2.625 2.625 0 1 1 9.52 9.52l3.45-3.451a.75.75 0 1 1 1.061 1.06l-3.45 3.451a1.125 1.125 0 0 0 1.587 1.595l3.454-3.553a3 3 0 0 0 0-4.242Z" clip-rule="evenodd"/></svg>',
            'external_link' => '<svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.25 5.5a.75.75 0 0 0-.75.75v8.5c0 .414.336.75.75.75h8.5a.75.75 0 0 0 .75-.75v-4a.75.75 0 0 1 1.5 0v4A2.25 2.25 0 0 1 12.75 17h-8.5A2.25 2.25 0 0 1 2 14.75v-8.5A2.25 2.25 0 0 1 4.25 4h5a.75.75 0 0 1 0 1.5h-5Z" clip-rule="evenodd"/><path fill-rule="evenodd" d="M6.194 12.753a.75.75 0 0 0 1.06.053L16.5 4.44v2.81a.75.75 0 0 0 1.5 0v-4.5a.75.75 0 0 0-.75-.75h-4.5a.75.75 0 0 0 0 1.5h2.553l-9.056 8.194a.75.75 0 0 0-.053 1.06Z" clip-rule="evenodd"/></svg>',
            default => '<svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10.75 16.82A7.462 7.462 0 0 1 10 17c-.386 0-.766-.02-1.138-.06l-.136-.021a7.5 7.5 0 1 1 2.55-.079l-.276.04Z"/></svg>',
        };

        $typeBg = match ($type) {
            'video_embed' => 'bg-violet-100 text-violet-600',
            'article' => 'bg-sky-100 text-sky-600',
            'file_attachment' => 'bg-orange-100 text-orange-600',
            'external_link' => 'bg-teal-100 text-teal-600',
            default => 'bg-zinc-100 text-zinc-600',
        };
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
            box-shadow: 0 2px 0 0 #b45309, 0 4px 8px rgba(245,158,11,0.2);
        }
        .btn-3d-primary.is-completed {
            box-shadow: 0 6px 0 0 #059669, 0 8px 16px rgba(16,185,129,0.3);
            background: linear-gradient(135deg, #10b981, #059669);
        }
        .btn-3d-primary.is-completed:hover {
            box-shadow: 0 8px 0 0 #059669, 0 12px 20px rgba(16,185,129,0.35);
        }
        .btn-3d-primary.is-completed:active {
            box-shadow: 0 2px 0 0 #059669, 0 4px 8px rgba(16,185,129,0.2);
        }

        .btn-3d-nav {
            position: relative;
            transform: translateY(0);
            box-shadow: 0 4px 0 0 #d4d4d8, 0 6px 12px rgba(0,0,0,0.08);
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

        .lesson-item-active {
            background: linear-gradient(135deg, #fffbeb, #fef3c7);
            box-shadow: 0 2px 8px rgba(245,158,11,0.15);
        }
        .lesson-icon-badge {
            flex-shrink: 0;
            width: 28px;
            height: 28px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .sidebar-header-gradient {
            background: linear-gradient(135deg, #18181b 0%, #27272a 50%, #1c1917 100%);
        }
        .progress-bar-glow {
            box-shadow: 0 0 8px rgba(251,191,36,0.6);
        }
        .content-type-header {
            background: linear-gradient(135deg, #1c1917 0%, #18181b 100%);
        }
    </style>

    <div class="mx-auto flex min-h-[calc(100vh-1rem)] w-full max-w-[min(1640px,calc(100vw-24px))] flex-col px-0 pb-0 pt-0 md:min-h-screen md:pb-0">
        @include('partials.user-dashboard-header')

        <section class="px-1 py-4 md:px-5 lg:px-6">

            {{-- Top navigation bar --}}
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <a
                    href="{{ route('my-courses.show', $userProduct) }}"
                    class="btn-3d-nav inline-flex items-center gap-1.5 rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm font-semibold text-zinc-700"
                >
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.612l4.158 3.96a.75.75 0 1 1-1.04 1.08l-5.5-5.25a.75.75 0 0 1 0-1.08l5.5-5.25a.75.75 0 1 1 1.04 1.08L5.612 9.25H16.25A.75.75 0 0 1 17 10Z" clip-rule="evenodd"/>
                    </svg>
                    <span class="hidden sm:inline">Kelas</span>
                </a>

                <div class="flex flex-wrap items-center gap-2">
                    {{-- Type badge with icon --}}
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-violet-200/70 bg-violet-50 px-2.5 py-1 text-[11px] font-semibold text-violet-700">
                        <span class="{{ $typeBg }} rounded-full p-0.5">{!! $typeIcon !!}</span>
                        {{ $lesson->lesson_type?->label() ?? ($lesson->lesson_type?->value ?? 'Materi') }}
                    </span>
                    {{-- Progress badge --}}
                    <span class="inline-flex items-center gap-1 rounded-full border border-zinc-200 bg-zinc-50 px-2.5 py-1 text-[11px] font-semibold text-zinc-600">
                        <svg class="h-3 w-3 text-zinc-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-13a.75.75 0 0 0-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 0 0 0-1.5h-3.25V5Z" clip-rule="evenodd"/>
                        </svg>
                        {{ $currentLessonIndex }}/{{ $totalLessons }}
                    </span>
                    @if (! $lesson->isRequired())
                        <span class="inline-flex items-center gap-1 rounded-full border border-zinc-200 bg-zinc-50 px-2.5 py-1 text-[11px] font-medium text-zinc-500">
                            Opsional
                        </span>
                    @endif
                </div>
            </div>

            {{-- Main grid --}}
            <div class="grid gap-5 xl:grid-cols-[300px_minmax(0,1fr)]">

                {{-- Sidebar: Course curriculum --}}
                <aside class="xl:sticky xl:top-24 xl:self-start">
                    <div class="overflow-hidden rounded-2xl border border-zinc-200/70 bg-white shadow-[0_4px_24px_rgba(0,0,0,0.07)]">

                        {{-- Sidebar header --}}
                        <div class="sidebar-header-gradient px-4 py-4 text-white">
                            <div class="flex items-start gap-3">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/10">
                                    <svg class="h-4 w-4 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M10.75 16.82A7.462 7.462 0 0 1 10 17c-.386 0-.766-.02-1.138-.06l-.136-.021a7.5 7.5 0 1 1 2.55-.079l-.276.04Z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="truncate text-sm font-semibold leading-tight">{{ $course->title }}</div>
                                    <div class="mt-0.5 flex items-center gap-2 text-[11px] text-white/60">
                                        <span class="flex items-center gap-1">
                                            <svg class="h-3 w-3 text-emerald-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                                            {{ $completedLessons }} selesai
                                        </span>
                                        <span>·</span>
                                        <span>{{ $remainingLessons }} tersisa</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <div class="mb-1 flex items-center justify-between text-[10px] text-white/50">
                                    <span>Progres</span>
                                    <span class="font-semibold text-amber-400">{{ $progressPercent }}%</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-white/10">
                                    <div
                                        class="progress-bar-glow h-full rounded-full bg-gradient-to-r from-amber-400 to-amber-500 transition-all duration-500"
                                        style="width: {{ $progressPercent }}%"
                                    ></div>
                                </div>
                            </div>
                        </div>

                        {{-- Lesson list --}}
                        <div class="max-h-[calc(100vh-240px)] space-y-2 overflow-y-auto p-3">
                            @foreach ($sections as $section)
                                @php($sectionLessons = $lessonsBySection->get($section->id, collect()))
                                @php($hasCurrentLesson = $sectionLessons->contains(fn ($row) => $row->id === $lesson->id))

                                @if ($sectionLessons->count() > 0)
                                    <div
                                        x-data="{ open: {{ $hasCurrentLesson ? 'true' : 'false' }} }"
                                        class="overflow-hidden rounded-xl border border-zinc-200/70 bg-zinc-50/60"
                                    >
                                        <button
                                            type="button"
                                            @click="open = !open"
                                            class="flex w-full items-center justify-between gap-3 px-3 py-2.5 text-left transition hover:bg-zinc-100/60"
                                            :aria-expanded="open.toString()"
                                        >
                                            <div class="min-w-0">
                                                <div class="truncate text-[11px] font-bold uppercase tracking-[0.14em] text-zinc-500">
                                                    {{ $section->title }}
                                                </div>
                                                <div class="mt-0.5 flex items-center gap-1 text-[11px] text-zinc-400">
                                                    <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2 4.75A.75.75 0 0 1 2.75 4h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 4.75ZM2 10a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 10Zm0 5.25a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd"/></svg>
                                                    {{ $sectionLessons->count() }} materi
                                                </div>
                                            </div>
                                            <svg
                                                class="h-4 w-4 shrink-0 text-zinc-400 transition-transform duration-200"
                                                :class="open ? 'rotate-180' : ''"
                                                viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"
                                            >
                                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.512a.75.75 0 0 1-1.08 0L5.21 8.27a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd"/>
                                            </svg>
                                        </button>

                                        <div
                                            x-show="open"
                                            x-transition.opacity.duration.200ms
                                            class="space-y-1.5 border-t border-zinc-200/70 p-2"
                                        >
                                            @foreach ($sectionLessons as $sectionLesson)
                                                @php($status = $progressByLessonId[$sectionLesson->id] ?? null)
                                                @php($access = $lessonAccessByLessonId[$sectionLesson->id] ?? ['can_access' => true, 'state' => 'open', 'status_label' => 'Terbuka', 'message' => null])
                                                @php($rowCompleted = $status?->value === 'completed')
                                                @php($isCurrentLesson = $sectionLesson->id === $lesson->id)
                                                @php($isClickable = (bool) ($access['can_access'] ?? false))
                                                @php($state = $access['state'] ?? 'open')
                                                @php($iconHtml = match ($state) {
                                                    'completed' => '<div class="lesson-icon-badge bg-emerald-100"><svg class="h-3.5 w-3.5 text-emerald-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg></div>',
                                                    'scheduled' => '<div class="lesson-icon-badge bg-sky-100"><svg class="h-3.5 w-3.5 text-sky-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-13a.75.75 0 0 0-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 0 0 0-1.5h-3.25V5Z" clip-rule="evenodd"/></svg></div>',
                                                    'locked' => '<div class="lesson-icon-badge bg-zinc-100"><svg class="h-3.5 w-3.5 text-zinc-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z" clip-rule="evenodd"/></svg></div>',
                                                    default => '<div class="lesson-icon-badge bg-amber-100"><svg class="h-3.5 w-3.5 text-amber-600" viewBox="0 0 20 20" fill="currentColor"><path d="M6.3 2.84A1.5 1.5 0 0 0 4 4.11v11.78a1.5 1.5 0 0 0 2.3 1.27l9.344-5.891a1.5 1.5 0 0 0 0-2.538L6.3 2.84Z"/></svg></div>',
                                                })

                                                @if ($isClickable)
                                                    <a
                                                        href="{{ route('my-courses.lessons.show', [$userProduct, $sectionLesson]) }}"
                                                        class="flex items-start gap-2.5 rounded-lg border px-2.5 py-2 transition-all duration-150 {{ $isCurrentLesson ? 'lesson-item-active border-amber-300' : 'border-transparent bg-white/60 hover:border-zinc-200 hover:bg-white hover:shadow-sm' }}"
                                                    >
                                                        {!! $iconHtml !!}
                                                        <div class="min-w-0 flex-1">
                                                            <div class="truncate text-xs font-semibold leading-tight {{ $isCurrentLesson ? 'text-amber-900' : 'text-zinc-800' }}">
                                                                {{ $sectionLesson->title }}
                                                            </div>
                                                            <div class="mt-0.5 flex flex-wrap items-center gap-1.5 text-[10px] text-zinc-400">
                                                                @if ($sectionLesson->duration_minutes)
                                                                    <span class="flex items-center gap-0.5">
                                                                        <svg class="h-2.5 w-2.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-13a.75.75 0 0 0-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 0 0 0-1.5h-3.25V5Z" clip-rule="evenodd"/></svg>
                                                                        {{ $sectionLesson->duration_minutes }}m
                                                                    </span>
                                                                @endif
                                                                @if (filled($access['message']) && ! $rowCompleted)
                                                                    <span class="truncate text-zinc-400">{{ $access['message'] }}</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </a>
                                                @else
                                                    <div class="flex items-start gap-2.5 rounded-lg border border-transparent bg-zinc-50/80 px-2.5 py-2 opacity-70">
                                                        {!! $iconHtml !!}
                                                        <div class="min-w-0 flex-1">
                                                            <div class="truncate text-xs font-medium leading-tight text-zinc-600">
                                                                {{ $sectionLesson->title }}
                                                            </div>
                                                            <div class="mt-0.5 flex flex-wrap items-center gap-1.5 text-[10px] text-zinc-400">
                                                                @if ($sectionLesson->duration_minutes)
                                                                    <span class="flex items-center gap-0.5">
                                                                        <svg class="h-2.5 w-2.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-13a.75.75 0 0 0-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 0 0 0-1.5h-3.25V5Z" clip-rule="evenodd"/></svg>
                                                                        {{ $sectionLesson->duration_minutes }}m
                                                                    </span>
                                                                @endif
                                                                @if (filled($access['message']))
                                                                    <span class="truncate">{{ $access['message'] }}</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach

                            @php($noSectionLessons = $lessonsBySection->get(0, collect()))
                            @php($hasCurrentStandaloneLesson = $noSectionLessons->contains(fn ($row) => $row->id === $lesson->id))

                            @if ($noSectionLessons->count() > 0)
                                <div
                                    x-data="{ open: {{ $hasCurrentStandaloneLesson ? 'true' : 'false' }} }"
                                    class="overflow-hidden rounded-xl border border-zinc-200/70 bg-zinc-50/60"
                                >
                                    <button
                                        type="button"
                                        @click="open = !open"
                                        class="flex w-full items-center justify-between gap-3 px-3 py-2.5 text-left transition hover:bg-zinc-100/60"
                                        :aria-expanded="open.toString()"
                                    >
                                        <div class="min-w-0">
                                            <div class="truncate text-[11px] font-bold uppercase tracking-[0.14em] text-zinc-500">
                                                Materi Tambahan
                                            </div>
                                            <div class="mt-0.5 flex items-center gap-1 text-[11px] text-zinc-400">
                                                <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2 4.75A.75.75 0 0 1 2.75 4h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 4.75ZM2 10a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 10Zm0 5.25a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd"/></svg>
                                                {{ $noSectionLessons->count() }} materi
                                            </div>
                                        </div>
                                        <svg
                                            class="h-4 w-4 shrink-0 text-zinc-400 transition-transform duration-200"
                                            :class="open ? 'rotate-180' : ''"
                                            viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"
                                        >
                                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.512a.75.75 0 0 1-1.08 0L5.21 8.27a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>

                                    <div
                                        x-show="open"
                                        x-transition.opacity.duration.200ms
                                        class="space-y-1.5 border-t border-zinc-200/70 p-2"
                                    >
                                        @foreach ($noSectionLessons as $sectionLesson)
                                            @php($status = $progressByLessonId[$sectionLesson->id] ?? null)
                                            @php($access = $lessonAccessByLessonId[$sectionLesson->id] ?? ['can_access' => true, 'state' => 'open', 'status_label' => 'Terbuka', 'message' => null])
                                            @php($rowCompleted = $status?->value === 'completed')
                                            @php($isCurrentLesson = $sectionLesson->id === $lesson->id)
                                            @php($isClickable = (bool) ($access['can_access'] ?? false))
                                            @php($state = $access['state'] ?? 'open')
                                            @php($iconHtml = match ($state) {
                                                'completed' => '<div class="lesson-icon-badge bg-emerald-100"><svg class="h-3.5 w-3.5 text-emerald-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg></div>',
                                                'scheduled' => '<div class="lesson-icon-badge bg-sky-100"><svg class="h-3.5 w-3.5 text-sky-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-13a.75.75 0 0 0-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 0 0 0-1.5h-3.25V5Z" clip-rule="evenodd"/></svg></div>',
                                                'locked' => '<div class="lesson-icon-badge bg-zinc-100"><svg class="h-3.5 w-3.5 text-zinc-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z" clip-rule="evenodd"/></svg></div>',
                                                default => '<div class="lesson-icon-badge bg-amber-100"><svg class="h-3.5 w-3.5 text-amber-600" viewBox="0 0 20 20" fill="currentColor"><path d="M6.3 2.84A1.5 1.5 0 0 0 4 4.11v11.78a1.5 1.5 0 0 0 2.3 1.27l9.344-5.891a1.5 1.5 0 0 0 0-2.538L6.3 2.84Z"/></svg></div>',
                                            })

                                            @if ($isClickable)
                                                <a
                                                    href="{{ route('my-courses.lessons.show', [$userProduct, $sectionLesson]) }}"
                                                    class="flex items-start gap-2.5 rounded-lg border px-2.5 py-2 transition-all duration-150 {{ $isCurrentLesson ? 'lesson-item-active border-amber-300' : 'border-transparent bg-white/60 hover:border-zinc-200 hover:bg-white hover:shadow-sm' }}"
                                                >
                                                    {!! $iconHtml !!}
                                                    <div class="min-w-0 flex-1">
                                                        <div class="truncate text-xs font-semibold leading-tight {{ $isCurrentLesson ? 'text-amber-900' : 'text-zinc-800' }}">
                                                            {{ $sectionLesson->title }}
                                                        </div>
                                                        <div class="mt-0.5 flex flex-wrap items-center gap-1.5 text-[10px] text-zinc-400">
                                                            @if ($sectionLesson->duration_minutes)
                                                                <span class="flex items-center gap-0.5">
                                                                    <svg class="h-2.5 w-2.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-13a.75.75 0 0 0-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 0 0 0-1.5h-3.25V5Z" clip-rule="evenodd"/></svg>
                                                                    {{ $sectionLesson->duration_minutes }}m
                                                                </span>
                                                            @endif
                                                            @if (filled($access['message']) && ! $rowCompleted)
                                                                <span class="truncate text-zinc-400">{{ $access['message'] }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </a>
                                            @else
                                                <div class="flex items-start gap-2.5 rounded-lg border border-transparent bg-zinc-50/80 px-2.5 py-2 opacity-70">
                                                    {!! $iconHtml !!}
                                                    <div class="min-w-0 flex-1">
                                                        <div class="truncate text-xs font-medium leading-tight text-zinc-600">
                                                            {{ $sectionLesson->title }}
                                                        </div>
                                                        <div class="mt-0.5 flex flex-wrap items-center gap-1.5 text-[10px] text-zinc-400">
                                                            @if ($sectionLesson->duration_minutes)
                                                                <span class="flex items-center gap-0.5">
                                                                    <svg class="h-2.5 w-2.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-13a.75.75 0 0 0-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 0 0 0-1.5h-3.25V5Z" clip-rule="evenodd"/></svg>
                                                                    {{ $sectionLesson->duration_minutes }}m
                                                                </span>
                                                            @endif
                                                            @if (filled($access['message']))
                                                                <span class="truncate">{{ $access['message'] }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </aside>

                {{-- Main content --}}
                <main class="min-w-0 space-y-4">

                    {{-- Video Materi (show_video=true, non video_embed) --}}
                    @if ($lesson->show_video && $type !== 'video_embed')
                        @php($videoPlayerEmbedUrl = $lesson->youtube_embed_url)
                        @if ($videoPlayerEmbedUrl)
                            <x-learning.youtube-player
                                :embedUrl="$videoPlayerEmbedUrl"
                                :title="$lesson->video_title"
                                :description="$lesson->video_description"
                            />
                        @else
                            <div class="overflow-hidden rounded-2xl border border-zinc-200/70 bg-white p-5 shadow-[0_4px_24px_rgba(0,0,0,0.07)]">
                                <x-ui.alert variant="warning" title="Video Belum Tersedia">
                                    URL video belum valid atau video ID tidak dapat diekstrak.
                                </x-ui.alert>
                            </div>
                        @endif
                    @endif

                    <div class="overflow-hidden rounded-2xl border border-zinc-200/70 bg-white shadow-[0_4px_24px_rgba(0,0,0,0.07)]">

                        {{-- Lesson header --}}
                        <div class="border-b border-zinc-200/70 px-4 py-4 md:px-6">
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="{{ $typeBg }} flex h-7 w-7 items-center justify-center rounded-lg">
                                            {!! $typeIcon !!}
                                        </span>
                                        <h1 class="text-lg font-bold tracking-tight text-zinc-950 md:text-xl">
                                            {{ $lesson->title }}
                                        </h1>
                                    </div>
                                    <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-zinc-500">
                                        @if ($lesson->section?->title)
                                            <span class="flex items-center gap-1">
                                                <svg class="h-3 w-3 text-zinc-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2 4.75A.75.75 0 0 1 2.75 4h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 4.75ZM2 10a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 10Zm0 5.25a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd"/></svg>
                                                {{ $lesson->section->title }}
                                            </span>
                                        @endif
                                        @if ($lesson->duration_minutes)
                                            <span class="flex items-center gap-1">
                                                <svg class="h-3 w-3 text-zinc-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-13a.75.75 0 0 0-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 0 0 0-1.5h-3.25V5Z" clip-rule="evenodd"/></svg>
                                                {{ $lesson->duration_minutes }} menit
                                            </span>
                                        @endif
                                        <span class="flex items-center gap-1">
                                            <svg class="h-3 w-3 text-zinc-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm-1.5-9.5a1.5 1.5 0 1 1 3 0v4a1.5 1.5 0 0 1-3 0v-4ZM10 6a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
                                            {{ $progressPercent }}% progres
                                        </span>
                                        @if ($lesson->available_from)
                                            <span class="flex items-center gap-1 text-sky-600">
                                                <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.75 2a.75.75 0 0 1 .75.75V4h7V2.75a.75.75 0 0 1 1.5 0V4h.25A2.75 2.75 0 0 1 18 6.75v8.5A2.75 2.75 0 0 1 15.25 18H4.75A2.75 2.75 0 0 1 2 15.25v-8.5A2.75 2.75 0 0 1 4.75 4H5V2.75A.75.75 0 0 1 5.75 2Z" clip-rule="evenodd"/></svg>
                                                {{ $lesson->available_from->timezone('Asia/Jakarta')->format('d M Y H:i') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('my-courses.lessons.complete', [$userProduct, $lesson]) }}">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="btn-3d-primary {{ $isCompleted ? 'is-completed' : '' }} inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold text-white {{ $isCompleted ? 'bg-gradient-to-br from-emerald-500 to-emerald-600' : 'bg-gradient-to-br from-amber-400 to-amber-500' }}"
                                    >
                                        @if ($isCompleted)
                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/>
                                            </svg>
                                            Sudah Selesai
                                        @else
                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/>
                                            </svg>
                                            Tandai Selesai
                                        @endif
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Lesson content --}}
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
                                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-orange-100 bg-gradient-to-r from-orange-50 to-amber-50 p-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-100">
                                                <svg class="h-5 w-5 text-orange-600" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M15.621 4.379a3 3 0 0 0-4.242 0l-7 7a3 3 0 0 0 4.241 4.243h.001l.497-.5a.75.75 0 0 1 1.064 1.057l-.498.501-.002.002a4.5 4.5 0 0 1-6.364-6.364l7-7a4.5 4.5 0 0 1 6.368 6.36l-3.455 3.553A2.625 2.625 0 1 1 9.52 9.52l3.45-3.451a.75.75 0 1 1 1.061 1.06l-3.45 3.451a1.125 1.125 0 0 0 1.587 1.595l3.454-3.553a3 3 0 0 0 0-4.242Z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <div class="text-sm font-bold text-zinc-900">Attachment Materi</div>
                                                <div class="text-xs text-zinc-500">File terlampir untuk materi ini</div>
                                            </div>
                                        </div>
                                        <a
                                            href="{{ route('my-courses.lessons.download', [$userProduct, $lesson]) }}"
                                            class="btn-3d-primary inline-flex items-center gap-2 rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 px-4 py-2.5 text-sm font-bold text-white"
                                            style="box-shadow: 0 6px 0 0 #c2410c, 0 8px 16px rgba(234,88,12,0.3);"
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M10.75 2.75a.75.75 0 0 0-1.5 0v8.614L6.295 8.235a.75.75 0 1 0-1.09 1.03l4.25 4.5a.75.75 0 0 0 1.09 0l4.25-4.5a.75.75 0 0 0-1.09-1.03l-2.955 3.129V2.75Z"/>
                                                <path d="M3.5 12.75a.75.75 0 0 0-1.5 0v2.5A2.75 2.75 0 0 0 4.75 18h10.5A2.75 2.75 0 0 0 18 15.25v-2.5a.75.75 0 0 0-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5Z"/>
                                            </svg>
                                            Unduh File
                                        </a>
                                    </div>

                                    @if (filled($lesson->content))
                                        <div class="mt-6 prose prose-zinc max-w-none prose-p:leading-7">
                                            {!! $lesson->content !!}
                                        </div>
                                    @endif
                                </div>
                            @elseif ($type === 'external_link')
                                <div class="bg-white p-6 md:p-8">
                                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-teal-100 bg-gradient-to-r from-teal-50 to-cyan-50 p-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-100">
                                                <svg class="h-5 w-5 text-teal-600" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M4.25 5.5a.75.75 0 0 0-.75.75v8.5c0 .414.336.75.75.75h8.5a.75.75 0 0 0 .75-.75v-4a.75.75 0 0 1 1.5 0v4A2.25 2.25 0 0 1 12.75 17h-8.5A2.25 2.25 0 0 1 2 14.75v-8.5A2.25 2.25 0 0 1 4.25 4h5a.75.75 0 0 1 0 1.5h-5Z" clip-rule="evenodd"/><path fill-rule="evenodd" d="M6.194 12.753a.75.75 0 0 0 1.06.053L16.5 4.44v2.81a.75.75 0 0 0 1.5 0v-4.5a.75.75 0 0 0-.75-.75h-4.5a.75.75 0 0 0 0 1.5h2.553l-9.056 8.194a.75.75 0 0 0-.053 1.06Z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <div class="text-sm font-bold text-zinc-900">Resource Eksternal</div>
                                                <div class="text-xs text-zinc-500">Buka link di tab baru</div>
                                            </div>
                                        </div>
                                        <a
                                            href="{{ route('my-courses.lessons.open', [$userProduct, $lesson]) }}"
                                            class="btn-3d-primary inline-flex items-center gap-2 rounded-xl bg-gradient-to-br from-teal-500 to-teal-600 px-4 py-2.5 text-sm font-bold text-white"
                                            style="box-shadow: 0 6px 0 0 #0f766e, 0 8px 16px rgba(20,184,166,0.3);"
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M4.25 5.5a.75.75 0 0 0-.75.75v8.5c0 .414.336.75.75.75h8.5a.75.75 0 0 0 .75-.75v-4a.75.75 0 0 1 1.5 0v4A2.25 2.25 0 0 1 12.75 17h-8.5A2.25 2.25 0 0 1 2 14.75v-8.5A2.25 2.25 0 0 1 4.25 4h5a.75.75 0 0 1 0 1.5h-5Z" clip-rule="evenodd"/><path fill-rule="evenodd" d="M6.194 12.753a.75.75 0 0 0 1.06.053L16.5 4.44v2.81a.75.75 0 0 0 1.5 0v-4.5a.75.75 0 0 0-.75-.75h-4.5a.75.75 0 0 0 0 1.5h2.553l-9.056 8.194a.75.75 0 0 0-.053 1.06Z" clip-rule="evenodd"/>
                                            </svg>
                                            Buka Link
                                        </a>
                                    </div>

                                    @if (filled($lesson->content))
                                        <div class="mt-6 prose prose-zinc max-w-none prose-p:leading-7">
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
                    </div>

                    {{-- Attachments --}}
                    @if (($downloadableAttachments->count() ?? 0) > 0)
                        <div class="overflow-hidden rounded-2xl border border-zinc-200/70 bg-white p-5 shadow-[0_4px_24px_rgba(0,0,0,0.07)] md:p-6">
                            <div class="flex items-center gap-2.5">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100">
                                    <svg class="h-4 w-4 text-indigo-600" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M15.621 4.379a3 3 0 0 0-4.242 0l-7 7a3 3 0 0 0 4.241 4.243h.001l.497-.5a.75.75 0 0 1 1.064 1.057l-.498.501-.002.002a4.5 4.5 0 0 1-6.364-6.364l7-7a4.5 4.5 0 0 1 6.368 6.36l-3.455 3.553A2.625 2.625 0 1 1 9.52 9.52l3.45-3.451a.75.75 0 1 1 1.061 1.06l-3.45 3.451a1.125 1.125 0 0 0 1.587 1.595l3.454-3.553a3 3 0 0 0 0-4.242Z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="text-sm font-bold text-zinc-950">Download & Resource Materi</div>
                            </div>
                            <div class="mt-4 space-y-2.5">
                                @foreach ($downloadableAttachments as $attachment)
                                    <div class="flex flex-col gap-3 rounded-xl border border-zinc-200/70 bg-zinc-50/60 p-4 transition hover:bg-white hover:shadow-sm md:flex-row md:items-center md:justify-between">
                                        <div class="flex min-w-0 items-start gap-3">
                                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white shadow-sm">
                                                <svg class="h-4 w-4 text-zinc-500" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M10.75 2.75a.75.75 0 0 0-1.5 0v8.614L6.295 8.235a.75.75 0 1 0-1.09 1.03l4.25 4.5a.75.75 0 0 0 1.09 0l4.25-4.5a.75.75 0 0 0-1.09-1.03l-2.955 3.129V2.75Z"/>
                                                    <path d="M3.5 12.75a.75.75 0 0 0-1.5 0v2.5A2.75 2.75 0 0 0 4.75 18h10.5A2.75 2.75 0 0 0 18 15.25v-2.5a.75.75 0 0 0-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5Z"/>
                                                </svg>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <div class="text-sm font-semibold text-zinc-900">
                                                        {{ $attachment['title'] }}
                                                    </div>
                                                    <span class="inline-flex items-center rounded-full border border-zinc-200 bg-white px-2 py-0.5 text-[10px] font-semibold text-zinc-500">
                                                        {{ $attachment['badge_label'] }}
                                                    </span>
                                                </div>
                                                <div class="mt-0.5 flex flex-wrap items-center gap-2 text-xs text-zinc-400">
                                                    @if (filled($attachment['description']))
                                                        <span>{{ $attachment['description'] }}</span>
                                                    @endif
                                                    @if (filled($attachment['size_label']))
                                                        <span>·</span>
                                                        <span>{{ $attachment['size_label'] }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <a
                                            href="{{ $attachment['url'] }}"
                                            @if($attachment['open_in_new_tab']) target="_blank" rel="noopener noreferrer" @endif
                                            class="btn-3d-nav inline-flex shrink-0 items-center gap-2 rounded-xl border border-zinc-200 bg-white px-3.5 py-2 text-sm font-semibold text-zinc-700"
                                        >
                                            <svg class="h-4 w-4 text-zinc-500" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M10.75 2.75a.75.75 0 0 0-1.5 0v8.614L6.295 8.235a.75.75 0 1 0-1.09 1.03l4.25 4.5a.75.75 0 0 0 1.09 0l4.25-4.5a.75.75 0 0 0-1.09-1.03l-2.955 3.129V2.75Z"/>
                                                <path d="M3.5 12.75a.75.75 0 0 0-1.5 0v2.5A2.75 2.75 0 0 0 4.75 18h10.5A2.75 2.75 0 0 0 18 15.25v-2.5a.75.75 0 0 0-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5Z"/>
                                            </svg>
                                            {{ $attachment['button_label'] }}
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Prev / Next navigation --}}
                    <div class="flex flex-wrap items-center justify-between gap-3 pb-2">
                        <div class="flex flex-wrap items-center gap-2">
                            @if ($prevLessonId)
                                <a
                                    href="{{ route('my-courses.lessons.show', [$userProduct, $prevLessonId]) }}"
                                    class="btn-3d-nav inline-flex items-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm font-semibold text-zinc-700"
                                >
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="hidden sm:inline">Sebelumnya</span>
                                </a>
                            @endif
                            @if ($nextLessonId)
                                <a
                                    href="{{ route('my-courses.lessons.show', [$userProduct, $nextLessonId]) }}"
                                    class="btn-3d-nav inline-flex items-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm font-semibold text-zinc-700"
                                >
                                    <span class="hidden sm:inline">Berikutnya</span>
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/>
                                    </svg>
                                </a>
                            @endif
                        </div>

                        <div class="flex items-center gap-2 text-xs text-zinc-500">
                            @if ($isCompleted)
                                <span class="flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-emerald-700">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                                    Selesai
                                </span>
                            @elseif ($isInProgress)
                                <span class="flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-amber-700">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path d="M6.3 2.84A1.5 1.5 0 0 0 4 4.11v11.78a1.5 1.5 0 0 0 2.3 1.27l9.344-5.891a1.5 1.5 0 0 0 0-2.538L6.3 2.84Z"/></svg>
                                    Sedang Dipelajari
                                </span>
                            @else
                                <span class="flex items-center gap-1.5 rounded-full bg-zinc-100 px-2.5 py-1 text-zinc-600">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm-1.5-9.5a1.5 1.5 0 1 1 3 0v4a1.5 1.5 0 0 1-3 0v-4ZM10 6a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
                                    Terbuka
                                </span>
                            @endif
                        </div>
                    </div>
                </main>
            </div>
        </section>

        @include('partials.user-dashboard-footer')
    </div>
@endcomponent
