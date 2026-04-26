@component('layouts::app', ['title' => 'Kelas Saya'])
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
        .btn-3d-dark {
            position: relative;
            transform: translateY(0);
            box-shadow: 0 5px 0 0 #18181b, 0 8px 16px rgba(0,0,0,0.2);
            transition: transform 0.1s ease, box-shadow 0.1s ease;
        }
        .btn-3d-dark:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 0 0 #18181b, 0 12px 20px rgba(0,0,0,0.25);
        }
        .btn-3d-dark:active {
            transform: translateY(4px);
            box-shadow: 0 1px 0 0 #18181b;
        }
        .course-card {
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }
        .course-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 32px rgba(0,0,0,0.1);
        }
        .progress-glow {
            box-shadow: 0 0 8px rgba(251,191,36,0.55);
        }
    </style>

    <div class="mx-auto flex min-h-[calc(100vh-1rem)] w-full max-w-[min(1520px,calc(100vw-40px))] flex-col px-0 pb-0 pt-0 md:min-h-screen md:pb-0">
        @include('partials.user-dashboard-header')

        <section class="px-1 py-8 md:px-6 lg:px-8">

            {{-- Page header --}}
            <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-amber-400 to-amber-500 shadow-[0_4px_12px_rgba(245,158,11,0.35)]">
                        <svg class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10.75 16.82A7.462 7.462 0 0 1 10 17c-.386 0-.766-.02-1.138-.06l-.136-.021a7.5 7.5 0 1 1 2.55-.079l-.276.04Z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold tracking-tight text-zinc-950">Kelas Saya</h1>
                        <p class="text-xs text-zinc-500">Daftar kelas aktif yang kamu miliki</p>
                    </div>
                </div>
                <a
                    href="{{ route('my-products.index') }}"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-zinc-200 bg-white px-3.5 py-2 text-sm font-semibold text-zinc-700 shadow-[0_3px_0_0_#e4e4e7] transition hover:-translate-y-0.5 hover:shadow-[0_5px_0_0_#e4e4e7] active:translate-y-0.5 active:shadow-[0_1px_0_0_#e4e4e7]"
                >
                    <svg class="h-4 w-4 text-zinc-400" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M1 1.75A.75.75 0 0 1 1.75 1h1.628a1.75 1.75 0 0 1 1.734 1.51L5.18 3a65.25 65.25 0 0 1 13.36 1.412.75.75 0 0 1 .58.875 48.645 48.645 0 0 1-1.618 6.2.75.75 0 0 1-.712.513H6a2.503 2.503 0 0 0-2.292 1.5H17.25a.75.75 0 0 1 0 1.5H2.76a.75.75 0 0 1-.748-.807 4.002 4.002 0 0 1 2.716-3.486L3.626 2.716a.25.25 0 0 0-.248-.216H1.75A.75.75 0 0 1 1 1.75Z"/>
                    </svg>
                    Produk Saya
                </a>
            </div>

            @if ($userProducts->count() === 0)
                <div class="mt-6">
                    <x-ui.empty-state
                        title="Belum ada kelas"
                        description="Setelah pembayaran diverifikasi, kelas akan muncul otomatis di sini."
                    >
                        <x-slot:action>
                            <x-ui.button variant="primary" :href="route('catalog.products.index')">
                                Jelajahi produk
                            </x-ui.button>
                        </x-slot:action>
                    </x-ui.empty-state>
                </div>
            @else
                <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($userProducts as $userProduct)
                        @php($course = $userProduct->product?->course)
                        @php($product = $userProduct->product)
                        @php($courseReady = (bool) $course)
                        @php($progress = $progressByUserProductId[$userProduct->id] ?? ['percent' => 0, 'completed' => 0, 'total' => 0])
                        @php($percent = $progress['percent'] ?? 0)
                        @php($completed = $progress['completed'] ?? 0)
                        @php($total = $progress['total'] ?? 0)
                        @php($remaining = max($total - $completed, 0))
                        @php($hasThumbnail = filled($product?->thumbnail) || filled($course?->thumbnail))
                        @php($thumbnailSrc = filled($product?->thumbnail) ? asset('storage/'.$product->thumbnail) : (filled($course?->thumbnail) ? asset('storage/'.$course->thumbnail) : null))

                        @php($progressColor = match(true) {
                            $percent >= 100 => 'from-emerald-400 to-emerald-500',
                            $percent >= 60  => 'from-sky-400 to-blue-500',
                            $percent >= 30  => 'from-amber-400 to-amber-500',
                            default         => 'from-zinc-300 to-zinc-400',
                        })
                        @php($progressGlowColor = match(true) {
                            $percent >= 100 => '0 0 8px rgba(52,211,153,0.6)',
                            $percent >= 60  => '0 0 8px rgba(56,189,248,0.6)',
                            $percent >= 30  => '0 0 8px rgba(251,191,36,0.6)',
                            default         => 'none',
                        })

                        <div class="course-card flex flex-col overflow-hidden rounded-2xl border border-zinc-200/70 bg-white shadow-[0_4px_20px_rgba(0,0,0,0.06)]">

                            {{-- Thumbnail --}}
                            <div class="relative h-40 overflow-hidden bg-gradient-to-br from-zinc-800 to-zinc-950 sm:h-44">
                                @if ($thumbnailSrc)
                                    <img
                                        src="{{ $thumbnailSrc }}"
                                        alt="{{ $course?->title ?? $product?->title }}"
                                        class="h-full w-full object-cover opacity-90"
                                    >
                                @else
                                    <div class="flex h-full w-full items-center justify-center">
                                        <svg class="h-12 w-12 text-white/20" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M10.75 16.82A7.462 7.462 0 0 1 10 17c-.386 0-.766-.02-1.138-.06l-.136-.021a7.5 7.5 0 1 1 2.55-.079l-.276.04Z"/>
                                        </svg>
                                    </div>
                                @endif

                                {{-- Gradient overlay --}}
                                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>

                                {{-- Status pill --}}
                                <div class="absolute right-3 top-3">
                                    @if (! $courseReady)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-500/90 px-2.5 py-1 text-[10px] font-bold text-white backdrop-blur-sm">
                                            <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-13a.75.75 0 0 0-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 0 0 0-1.5h-3.25V5Z" clip-rule="evenodd"/></svg>
                                            Disiapkan
                                        </span>
                                    @elseif ($percent >= 100)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/90 px-2.5 py-1 text-[10px] font-bold text-white backdrop-blur-sm">
                                            <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                                            Selesai
                                        </span>
                                    @elseif ($completed > 0)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-sky-500/90 px-2.5 py-1 text-[10px] font-bold text-white backdrop-blur-sm">
                                            <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path d="M6.3 2.84A1.5 1.5 0 0 0 4 4.11v11.78a1.5 1.5 0 0 0 2.3 1.27l9.344-5.891a1.5 1.5 0 0 0 0-2.538L6.3 2.84Z"/></svg>
                                            Sedang Belajar
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-zinc-700/80 px-2.5 py-1 text-[10px] font-bold text-white backdrop-blur-sm">
                                            <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM6.75 9.25a.75.75 0 0 0 0 1.5h4.59l-2.1 1.95a.75.75 0 0 0 1.02 1.1l3.5-3.25a.75.75 0 0 0 0-1.1l-3.5-3.25a.75.75 0 1 0-1.02 1.1l2.1 1.95H6.75Z" clip-rule="evenodd"/></svg>
                                            Mulai
                                        </span>
                                    @endif
                                </div>

                                {{-- Course title on thumbnail --}}
                                <div class="absolute bottom-0 left-0 right-0 px-4 pb-3">
                                    <div class="truncate text-sm font-bold text-white drop-shadow">
                                        {{ $course?->title ?? ($product?->title ?? 'Kelas') }}
                                    </div>
                                </div>
                            </div>

                            {{-- Card body --}}
                            <div class="flex flex-1 flex-col p-4">

                                {{-- Product label --}}
                                <div class="flex items-center gap-1.5 text-xs text-zinc-500">
                                    <svg class="h-3.5 w-3.5 text-zinc-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M1 1.75A.75.75 0 0 1 1.75 1h1.628a1.75 1.75 0 0 1 1.734 1.51L5.18 3a65.25 65.25 0 0 1 13.36 1.412.75.75 0 0 1 .58.875 48.645 48.645 0 0 1-1.618 6.2.75.75 0 0 1-.712.513H6a2.503 2.503 0 0 0-2.292 1.5H17.25a.75.75 0 0 1 0 1.5H2.76a.75.75 0 0 1-.748-.807 4.002 4.002 0 0 1 2.716-3.486L3.626 2.716a.25.25 0 0 0-.248-.216H1.75A.75.75 0 0 1 1 1.75Z"/>
                                    </svg>
                                    <span class="truncate">{{ $product?->title ?? '-' }}</span>
                                </div>

                                {{-- Stats row --}}
                                <div class="mt-3 flex items-center gap-3">
                                    <div class="flex items-center gap-1.5 rounded-lg bg-zinc-50 px-2.5 py-1.5 text-xs">
                                        <svg class="h-3.5 w-3.5 text-emerald-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                                        <span class="font-semibold text-zinc-800">{{ $completed }}</span>
                                        <span class="text-zinc-500">selesai</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 rounded-lg bg-zinc-50 px-2.5 py-1.5 text-xs">
                                        <svg class="h-3.5 w-3.5 text-zinc-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2 4.75A.75.75 0 0 1 2.75 4h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 4.75ZM2 10a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 10Zm0 5.25a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd"/></svg>
                                        <span class="font-semibold text-zinc-800">{{ $total }}</span>
                                        <span class="text-zinc-500">materi</span>
                                    </div>
                                    @if ($remaining > 0)
                                        <div class="flex items-center gap-1.5 rounded-lg bg-zinc-50 px-2.5 py-1.5 text-xs">
                                            <svg class="h-3.5 w-3.5 text-amber-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-13a.75.75 0 0 0-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 0 0 0-1.5h-3.25V5Z" clip-rule="evenodd"/></svg>
                                            <span class="font-semibold text-zinc-800">{{ $remaining }}</span>
                                            <span class="text-zinc-500">tersisa</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Progress bar --}}
                                <div class="mt-3">
                                    <div class="mb-1.5 flex items-center justify-between text-[11px]">
                                        <span class="font-semibold text-zinc-500">Progres</span>
                                        <span class="font-bold text-zinc-900">{{ $percent }}%</span>
                                    </div>
                                    <div class="h-2 overflow-hidden rounded-full bg-zinc-100">
                                        <div
                                            class="h-full rounded-full bg-gradient-to-r {{ $progressColor }} transition-all duration-500"
                                            style="width: {{ $percent }}%; {{ $percent > 0 ? 'box-shadow: '.$progressGlowColor.';' : '' }}"
                                        ></div>
                                    </div>
                                </div>

                                {{-- CTA --}}
                                <div class="mt-4 flex-1 flex items-end">
                                    @if ($courseReady)
                                        <a
                                            href="{{ route('my-courses.show', $userProduct) }}"
                                            class="btn-3d-{{ $percent >= 100 ? 'dark' : 'primary' }} inline-flex w-full items-center justify-center gap-2 rounded-xl py-2.5 text-sm font-bold text-white {{ $percent >= 100 ? 'bg-gradient-to-br from-zinc-800 to-zinc-900' : 'bg-gradient-to-br from-amber-400 to-amber-500' }}"
                                        >
                                            @if ($percent >= 100)
                                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                                                Lihat Kelas
                                            @elseif ($completed > 0)
                                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M6.3 2.84A1.5 1.5 0 0 0 4 4.11v11.78a1.5 1.5 0 0 0 2.3 1.27l9.344-5.891a1.5 1.5 0 0 0 0-2.538L6.3 2.84Z"/></svg>
                                                Lanjut Belajar
                                            @else
                                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M6.3 2.84A1.5 1.5 0 0 0 4 4.11v11.78a1.5 1.5 0 0 0 2.3 1.27l9.344-5.891a1.5 1.5 0 0 0 0-2.538L6.3 2.84Z"/></svg>
                                                Mulai Belajar
                                            @endif
                                        </a>
                                    @else
                                        <div class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-zinc-200 bg-zinc-50 py-2.5 text-sm font-semibold text-zinc-400">
                                            <svg class="h-4 w-4 animate-spin" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 0 1-9.201 2.466l-.312-.311h2.433a.75.75 0 0 0 0-1.5H3.989a.75.75 0 0 0-.75.75v4.242a.75.75 0 0 0 1.5 0v-2.43l.31.31a7 7 0 0 0 11.712-3.138.75.75 0 0 0-1.449-.39Zm1.23-3.723a.75.75 0 0 0 .219-.53V2.929a.75.75 0 0 0-1.5 0V5.36l-.31-.31A7 7 0 0 0 3.239 8.188a.75.75 0 1 0 1.448.389A5.5 5.5 0 0 1 13.89 6.11l.311.31h-2.432a.75.75 0 0 0 0 1.5h4.243a.75.75 0 0 0 .53-.219Z" clip-rule="evenodd"/></svg>
                                            Sedang Disiapkan
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $userProducts->links() }}
                </div>
            @endif
        </section>

        @include('partials.user-dashboard-footer')
    </div>
@endcomponent
