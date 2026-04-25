<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        @php
            $authStyle = request()->query('auth_style', 'corporate');
            if (! in_array($authStyle, ['corporate', 'marketing'], true)) {
                $authStyle = 'corporate';
            }
        @endphp

        <style>
            @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@600;700;800&family=Outfit:wght@500;600;700;800&display=swap');

            .epi-auth-shell {
                --body-font: "Plus Jakarta Sans", "Segoe UI", Arial, sans-serif;
                --heading-font: "Sora", "Plus Jakarta Sans", sans-serif;
                --auth-field-width: 27rem;
                --card-pad-x: 2.5rem;
                --card-pad-y: 2.75rem;
                --shell-bg: #e8ebf0;
                --card-bg: rgba(255, 255, 255, 0.92);
                --card-border: #dfe4ea;
                --label-color: #7f8ea3;
                --title-color: #0f2748;
                --body-color: #64748b;
                --link-color: #2563eb;
                --btn-grad-start: #4f8bff;
                --btn-grad-mid: #2563eb;
                --btn-grad-end: #1e4ed8;
                --btn-shadow-main: rgba(37, 99, 235, 0.3);
                --btn-shadow-base: #1d4ed8;
                background:
                    radial-gradient(1000px 500px at 20% -10%, rgba(255, 255, 255, 0.92), transparent 60%),
                    radial-gradient(900px 450px at 100% 120%, rgba(59, 130, 246, 0.08), transparent 60%),
                    var(--shell-bg);
                font-family: var(--body-font);
            }

            .epi-auth-shell.epi-style-marketing {
                --body-font: "Outfit", "Plus Jakarta Sans", Arial, sans-serif;
                --heading-font: "Outfit", "Sora", sans-serif;
                --shell-bg: #fff2e2;
                --card-bg: rgba(255, 255, 255, 0.95);
                --card-border: #ffe0bf;
                --label-color: #9a6d46;
                --title-color: #2f1e0d;
                --body-color: #7b6655;
                --link-color: #e85d04;
                --btn-grad-start: #ff8a3d;
                --btn-grad-mid: #f97316;
                --btn-grad-end: #ea580c;
                --btn-shadow-main: rgba(234, 88, 12, 0.34);
                --btn-shadow-base: #c2410c;
                background:
                    radial-gradient(1050px 520px at 18% -12%, rgba(255, 255, 255, 0.9), transparent 58%),
                    radial-gradient(880px 440px at 104% 112%, rgba(251, 146, 60, 0.2), transparent 62%),
                    var(--shell-bg);
            }

            .epi-auth-wrap {
                width: min(100%, calc(var(--auth-field-width) + (var(--card-pad-x) * 2)));
                margin-inline: auto;
            }

            .epi-auth-shell {
                color: var(--body-color);
            }

            .epi-auth-card {
                border-radius: 2rem;
                border: 1px solid var(--card-border);
                background: var(--card-bg);
                padding: var(--card-pad-y) var(--card-pad-x);
                box-shadow:
                    0 20px 40px rgba(15, 23, 42, 0.08),
                    0 1px 0 rgba(255, 255, 255, 0.85) inset;
            }

            .epi-auth-inner {
                width: 100%;
                max-width: var(--auth-field-width);
                margin-inline: auto;
            }

            .epi-auth-title {
                font-family: var(--heading-font);
                color: var(--title-color);
            }

            .epi-auth-inner label {
                font-size: .78rem;
                font-weight: 700;
                letter-spacing: .08em;
                text-transform: uppercase;
                color: var(--label-color);
            }

            .epi-auth-inner :is(input, select, textarea) {
                font-size: 1.03rem !important;
                color: #334155 !important;
            }

            .epi-auth-inner p {
                font-size: .93rem;
            }

            .epi-auth-card :is(input, select, textarea) {
                border-radius: 1rem !important;
            }

            .epi-auth-btn {
                border: none !important;
                border-radius: 1rem !important;
                color: #fff !important;
                letter-spacing: .05em;
                font-weight: 700 !important;
                transform: translateY(0);
                transition: transform .16s ease, box-shadow .16s ease, filter .16s ease;
                background: linear-gradient(180deg, var(--btn-grad-start) 0%, var(--btn-grad-mid) 62%, var(--btn-grad-end) 100%) !important;
                box-shadow:
                    0 14px 24px var(--btn-shadow-main),
                    0 4px 0 var(--btn-shadow-base),
                    0 1px 0 rgba(255, 255, 255, 0.35) inset !important;
            }

            .epi-auth-btn:hover {
                filter: brightness(1.03);
                transform: translateY(-2px);
                box-shadow:
                    0 18px 26px var(--btn-shadow-main),
                    0 5px 0 var(--btn-shadow-base),
                    0 1px 0 rgba(255, 255, 255, 0.35) inset !important;
            }

            .epi-auth-btn:active {
                transform: translateY(3px);
                box-shadow:
                    0 8px 14px var(--btn-shadow-main),
                    0 1px 0 var(--btn-shadow-base),
                    0 1px 0 rgba(255, 255, 255, 0.22) inset !important;
            }

            .epi-auth-link {
                color: var(--link-color) !important;
            }

            @media (max-width: 768px) {
                .epi-auth-shell {
                    --auth-field-width: 100%;
                    --card-pad-x: 1.35rem;
                    --card-pad-y: 1.75rem;
                }
            }

            @media (min-width: 1024px) {
                .epi-auth-card {
                    border-radius: 2.35rem;
                }
            }
        </style>
    </head>
    <body class="min-h-screen antialiased epi-auth-shell epi-style-{{ $authStyle }}">
        <div class="flex min-h-svh flex-col items-center justify-center gap-6 p-5 md:p-10">
            <div class="epi-auth-wrap flex flex-col gap-2">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium" wire:navigate>
                    <span class="mb-2 flex h-14 w-14 items-center justify-center rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <x-app-logo-icon class="size-10 fill-current text-amber-700" />
                    </span>
                    <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
                </a>
                <div class="mb-1 flex items-center justify-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <a href="{{ request()->fullUrlWithQuery(['auth_style' => 'corporate']) }}" class="rounded-full px-3 py-1 {{ $authStyle === 'corporate' ? 'bg-white/80 text-slate-700 shadow-sm' : 'text-slate-500' }}">Corporate</a>
                    <a href="{{ request()->fullUrlWithQuery(['auth_style' => 'marketing']) }}" class="rounded-full px-3 py-1 {{ $authStyle === 'marketing' ? 'bg-white/80 text-slate-700 shadow-sm' : 'text-slate-500' }}">Marketing</a>
                </div>
                <div class="epi-auth-card flex flex-col gap-6">
                    <div class="epi-auth-inner">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
