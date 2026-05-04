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
                --auth-field-width: 26rem;
                --card-pad-x: 2.25rem;
                --card-pad-y: 2.5rem;
                --shell-bg: #dde4f0;
                --card-bg: rgba(255, 255, 255, 0.88);
                --card-border: rgba(255,255,255,0.7);
                --label-color: #334155;
                --title-color: #0f2748;
                --body-color: #64748b;
                --link-color: #2563eb;
                --btn-grad-start: #4f8bff;
                --btn-grad-mid: #2563eb;
                --btn-grad-end: #1e40af;
                --btn-shadow-main: rgba(37, 99, 235, 0.35);
                --btn-shadow-base: #1d4ed8;
                --tab-active-text: #2563eb;
                --tab-border: rgba(99,149,245,0.45);
                font-family: var(--body-font);
                background:
                    radial-gradient(ellipse 80% 60% at 10% 0%, rgba(255,255,255,0.85), transparent 55%),
                    radial-gradient(ellipse 70% 55% at 95% 100%, rgba(99,149,245,0.18), transparent 60%),
                    linear-gradient(160deg, #cdd8ee 0%, #d9e2f0 40%, #c8d4ec 100%);
                min-height: 100vh;
            }

            .epi-auth-shell.epi-style-marketing {
                --body-font: "Outfit", "Plus Jakarta Sans", Arial, sans-serif;
                --heading-font: "Outfit", "Sora", sans-serif;
                --shell-bg: #fff2e2;
                --card-bg: rgba(255, 255, 255, 0.92);
                --card-border: rgba(255,224,191,0.7);
                --label-color: #7c4a1e;
                --title-color: #2f1e0d;
                --body-color: #7b6655;
                --link-color: #e85d04;
                --btn-grad-start: #ff8a3d;
                --btn-grad-mid: #f97316;
                --btn-grad-end: #ea580c;
                --btn-shadow-main: rgba(234, 88, 12, 0.34);
                --btn-shadow-base: #c2410c;
                --tab-active-text: #ea580c;
                --tab-border: rgba(234,88,12,0.35);
                background:
                    radial-gradient(ellipse 80% 60% at 10% 0%, rgba(255,255,255,0.88), transparent 55%),
                    radial-gradient(ellipse 70% 55% at 95% 100%, rgba(251,146,60,0.22), transparent 60%),
                    linear-gradient(160deg, #fde8cc 0%, #fdf0e0 40%, #fce3c2 100%);
            }

            /* Decorative glowing lines */
            .epi-auth-shell::before,
            .epi-auth-shell::after {
                content: '';
                position: fixed;
                pointer-events: none;
                z-index: 0;
            }

            .epi-auth-shell::before {
                top: -10%;
                left: -5%;
                width: 55%;
                height: 75%;
                background: linear-gradient(135deg, rgba(99,149,245,0.13) 0%, transparent 60%);
                border-right: 1.5px solid rgba(130,170,255,0.18);
                border-bottom: 1.5px solid rgba(130,170,255,0.13);
                border-radius: 0 0 60% 0;
                transform: rotate(-8deg);
            }

            .epi-auth-shell::after {
                bottom: -8%;
                right: -4%;
                width: 50%;
                height: 70%;
                background: linear-gradient(315deg, rgba(59,130,246,0.1) 0%, transparent 58%);
                border-left: 1.5px solid rgba(99,149,245,0.18);
                border-top: 1.5px solid rgba(99,149,245,0.12);
                border-radius: 60% 0 0 0;
                transform: rotate(-6deg);
            }

            .epi-auth-wrap {
                width: min(100%, calc(var(--auth-field-width) + (var(--card-pad-x) * 2)));
                margin-inline: auto;
                position: relative;
                z-index: 1;
            }

            .epi-auth-shell {
                color: var(--body-color);
            }

            /* Tab switcher pill container */
            .epi-auth-tabs {
                display: flex;
                align-items: center;
                justify-content: center;
                background: rgba(255,255,255,0.55);
                border: 1.5px solid var(--tab-border);
                border-radius: 9999px;
                padding: 4px;
                gap: 2px;
                backdrop-filter: blur(8px);
                -webkit-backdrop-filter: blur(8px);
                width: fit-content;
                margin-inline: auto;
            }

            .epi-auth-tab {
                border-radius: 9999px;
                padding: 7px 22px;
                font-size: 0.72rem;
                font-weight: 700;
                letter-spacing: 0.1em;
                text-transform: uppercase;
                transition: all .2s ease;
                text-decoration: none;
                color: #94a3b8;
            }

            .epi-auth-tab.active {
                background: white;
                color: var(--tab-active-text);
                box-shadow: 0 2px 8px rgba(15,23,42,0.10);
            }

            .epi-auth-card {
                border-radius: 2.25rem;
                border: 1px solid var(--card-border);
                background: var(--card-bg);
                padding: var(--card-pad-y) var(--card-pad-x);
                box-shadow:
                    0 24px 48px rgba(15, 23, 42, 0.10),
                    0 1px 0 rgba(255, 255, 255, 0.9) inset;
                backdrop-filter: blur(20px);
                -webkit-backdrop-filter: blur(20px);
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
                font-size: .82rem;
                font-weight: 600;
                letter-spacing: .01em;
                color: var(--label-color);
            }

            .epi-auth-inner :is(input, select, textarea) {
                font-size: 1rem !important;
                color: #334155 !important;
            }

            .epi-auth-inner p {
                font-size: .93rem;
            }

            .epi-auth-card :is(input, select, textarea) {
                border-radius: 1rem !important;
                background: rgba(241,245,249,0.7) !important;
                border-color: rgba(203,213,225,0.6) !important;
            }

            .epi-auth-btn {
                border: none !important;
                border-radius: 9999px !important;
                color: #fff !important;
                letter-spacing: .04em;
                font-weight: 700 !important;
                font-size: 1rem !important;
                padding-top: 0.85rem !important;
                padding-bottom: 0.85rem !important;
                transform: translateY(0);
                transition: transform .16s ease, box-shadow .16s ease, filter .16s ease;
                background: linear-gradient(180deg, var(--btn-grad-start) 0%, var(--btn-grad-mid) 62%, var(--btn-grad-end) 100%) !important;
                box-shadow:
                    0 14px 28px var(--btn-shadow-main),
                    0 4px 0 var(--btn-shadow-base),
                    0 1px 0 rgba(255, 255, 255, 0.3) inset !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 0.5rem !important;
            }

            .epi-auth-btn:hover {
                filter: brightness(1.05);
                transform: translateY(-2px);
                box-shadow:
                    0 20px 32px var(--btn-shadow-main),
                    0 5px 0 var(--btn-shadow-base),
                    0 1px 0 rgba(255, 255, 255, 0.3) inset !important;
            }

            .epi-auth-btn:active {
                transform: translateY(3px);
                box-shadow:
                    0 6px 12px var(--btn-shadow-main),
                    0 1px 0 var(--btn-shadow-base),
                    0 1px 0 rgba(255, 255, 255, 0.22) inset !important;
            }

            .epi-auth-link {
                color: var(--link-color) !important;
            }

            /* Dot indicator between card and footer */
            .epi-auth-dot-indicator {
                display: flex;
                justify-content: center;
                gap: 5px;
                padding: 6px 0 2px;
            }

            .epi-auth-dot-indicator span {
                width: 6px;
                height: 6px;
                border-radius: 9999px;
                background: rgba(99,149,245,0.55);
            }

            .epi-auth-dot-indicator span.active {
                width: 18px;
                background: rgba(99,149,245,0.75);
            }

            @media (max-width: 768px) {
                .epi-auth-shell {
                    --auth-field-width: 100%;
                    --card-pad-x: 1.25rem;
                    --card-pad-y: 1.1rem;
                }

                .epi-auth-card {
                    border-radius: 1.75rem;
                    gap: 1rem;
                }

                .epi-auth-inner :is(input, select, textarea) {
                    font-size: 0.875rem !important;
                }

                .epi-auth-inner label {
                    font-size: 0.76rem;
                }

                .epi-auth-btn {
                    padding-top: 0.6rem !important;
                    padding-bottom: 0.6rem !important;
                    font-size: 0.875rem !important;
                }

                .epi-auth-tabs {
                    padding: 3px;
                }

                .epi-auth-tab {
                    padding: 5px 14px;
                    font-size: 0.62rem;
                }

                .epi-password-rule {
                    padding: 0.45rem 0.75rem;
                    font-size: 0.78rem;
                    gap: 0.5rem;
                }

                .epi-password-rule-icon {
                    height: 1.35rem;
                    width: 1.35rem;
                    font-size: 0.72rem;
                }
            }

            @media (min-width: 1024px) {
                .epi-auth-card {
                    border-radius: 2.5rem;
                }
            }
        </style>
    </head>
    <body class="min-h-screen antialiased epi-auth-shell epi-style-{{ $authStyle }}">
        <div class="flex min-h-svh flex-col items-center justify-center gap-2 p-3 sm:gap-4 sm:p-5 md:p-10">
            <div class="epi-auth-wrap flex flex-col gap-2 sm:gap-4">
                <div class="epi-auth-tabs">
                    <a href="{{ request()->fullUrlWithQuery(['auth_style' => 'corporate']) }}" class="epi-auth-tab {{ $authStyle === 'corporate' ? 'active' : '' }}">Corporate</a>
                    <a href="{{ request()->fullUrlWithQuery(['auth_style' => 'marketing']) }}" class="epi-auth-tab {{ $authStyle === 'marketing' ? 'active' : '' }}">Marketing</a>
                </div>
                <div class="epi-auth-card flex flex-col gap-4 sm:gap-6">
                    <div class="epi-auth-inner">
                        {{ $slot }}
                    </div>
                </div>
                <div class="epi-auth-dot-indicator">
                    <span class="active"></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        <x-pwa-install-button />
        @include('partials.pwa-scripts')
        @fluxScripts
    </body>
</html>
