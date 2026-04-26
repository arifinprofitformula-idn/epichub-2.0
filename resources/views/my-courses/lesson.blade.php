@component('layouts::app', ['title' => $lesson->title])
    <div class="mx-auto flex min-h-[calc(100vh-1rem)] w-full max-w-[min(1520px,calc(100vw-40px))] flex-col px-0 pb-0 pt-0 md:min-h-screen md:pb-0">
        @include('partials.user-dashboard-header')

        <section class="px-1 py-8 md:px-6 lg:px-8">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <x-ui.button variant="ghost" size="sm" :href="route('my-courses.show', $userProduct)">
                    ← Kembali ke kelas
                </x-ui.button>

                <div class="flex items-center gap-2">
                    <x-ui.badge variant="info">{{ $lesson->lesson_type?->label() ?? ($lesson->lesson_type?->value ?? '-') }}</x-ui.badge>
                    <x-ui.badge variant="neutral">{{ $progressPercent }}%</x-ui.badge>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-5">
                <div class="lg:col-span-3">
                    <x-ui.card class="p-6 md:p-8">
                        <div class="text-sm font-semibold text-zinc-900 dark:text-white">
                            {{ $lesson->title }}
                        </div>
                        @if ($lesson->section)
                            <div class="mt-1 text-xs text-zinc-600 dark:text-zinc-300">
                                {{ $lesson->section->title }}
                            </div>
                        @endif

                        <div class="mt-6">
                            @php($type = $lesson->lesson_type?->value ?? null)

                            @if ($type === 'article')
                                <div class="prose prose-zinc max-w-none dark:prose-invert">
                                    {!! $lesson->content !!}
                                </div>
                            @elseif ($type === 'video_embed')
                                @php($url = (string) ($lesson->video_url ?? ''))
                                @php($valid = filter_var($url, FILTER_VALIDATE_URL) && in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true))

                                @if ($valid)
                                    <div class="aspect-video w-full overflow-hidden rounded-[var(--radius-xl)] border border-zinc-200/70 dark:border-zinc-800">
                                        <iframe
                                            src="{{ $url }}"
                                            class="h-full w-full"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                            allowfullscreen
                                        ></iframe>
                                    </div>
                                @else
                                    <x-ui.alert variant="info" title="Catatan">
                                        Video sedang disiapkan atau URL belum valid.
                                    </x-ui.alert>
                                @endif
                            @elseif ($type === 'file_attachment')
                                <x-ui.alert variant="info" title="Attachment">
                                    File attachment tersedia untuk lesson ini.
                                </x-ui.alert>
                                <div class="mt-4">
                                    <x-ui.button variant="secondary" :href="route('my-courses.lessons.download', [$userProduct, $lesson])">
                                        Download attachment
                                    </x-ui.button>
                                </div>
                            @elseif ($type === 'external_link')
                                <x-ui.alert variant="info" title="Link">
                                    Lesson ini menggunakan external link.
                                </x-ui.alert>
                                <div class="mt-4">
                                    <x-ui.button variant="secondary" :href="route('my-courses.lessons.open', [$userProduct, $lesson])">
                                        Buka link
                                    </x-ui.button>
                                </div>
                            @else
                                <x-ui.alert variant="info" title="Catatan">
                                    Tipe lesson belum didukung.
                                </x-ui.alert>
                            @endif
                        </div>
                    </x-ui.card>

                    <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            @if ($prevLessonId)
                                <x-ui.button variant="ghost" size="sm" :href="route('my-courses.lessons.show', [$userProduct, $prevLessonId])">
                                    ← Sebelumnya
                                </x-ui.button>
                            @endif
                            @if ($nextLessonId)
                                <x-ui.button variant="ghost" size="sm" :href="route('my-courses.lessons.show', [$userProduct, $nextLessonId])">
                                    Berikutnya →
                                </x-ui.button>
                            @endif
                        </div>

                        <form method="POST" action="{{ route('my-courses.lessons.complete', [$userProduct, $lesson]) }}">
                            @csrf
                            <x-ui.button variant="primary" type="submit">
                                Tandai selesai
                            </x-ui.button>
                        </form>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <x-ui.card class="p-6 md:p-8">
                        <div class="text-sm font-semibold text-zinc-900 dark:text-white">Progress</div>
                        <div class="mt-3 text-sm text-zinc-600 dark:text-zinc-300">
                            {{ $completedLessons }} dari {{ $totalLessons }} lesson selesai.
                        </div>
                        <div class="mt-4">
                            <div class="h-2 w-full overflow-hidden rounded-full bg-zinc-200/70 dark:bg-zinc-800">
                                <div class="h-2 rounded-full bg-zinc-900 dark:bg-white" style="width: {{ $progressPercent }}%"></div>
                            </div>
                        </div>
                    </x-ui.card>
                </div>
            </div>
        </section>

        @include('partials.user-dashboard-footer')
    </div>
@endcomponent

