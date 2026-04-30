<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Anda sedang offline - EPIC Hub</title>
        <link rel="manifest" href="/manifest.webmanifest">
        <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
        <meta name="theme-color" content="#2563eb">
        <style>
            :root {
                color-scheme: light;
                font-family: "Segoe UI", Arial, sans-serif;
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                min-height: 100vh;
                background:
                    radial-gradient(circle at top, rgba(79, 139, 255, 0.24), transparent 32%),
                    linear-gradient(180deg, #eef4ff 0%, #ffffff 55%, #e8f0ff 100%);
                color: #1e3a8a;
            }

            .offline-shell {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 24px;
            }

            .offline-card {
                width: min(100%, 420px);
                border: 1px solid rgba(37, 99, 235, 0.16);
                border-radius: 28px;
                background: rgba(255, 255, 255, 0.96);
                box-shadow: 0 24px 60px rgba(30, 64, 175, 0.12);
                padding: 28px;
                text-align: center;
            }

            .offline-icon {
                width: 88px;
                height: 88px;
                border-radius: 24px;
                display: block;
                margin: 0 auto 20px;
            }

            h1 {
                margin: 0;
                font-size: 1.9rem;
                line-height: 1.1;
                color: #1e40af;
            }

            p {
                margin: 14px 0 0;
                font-size: 1rem;
                line-height: 1.6;
                color: #475569;
            }

            .offline-note {
                margin-top: 18px;
                font-size: 0.92rem;
                color: #1e3a8a;
            }

            .offline-actions {
                margin-top: 24px;
                display: flex;
                flex-direction: column;
                gap: 12px;
            }

            .offline-button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                width: 100%;
                min-height: 48px;
                border-radius: 999px;
                border: 1px solid transparent;
                font-size: 0.95rem;
                font-weight: 700;
                text-decoration: none;
                cursor: pointer;
                transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
            }

            .offline-button:hover {
                transform: translateY(-1px);
            }

            .offline-button-primary {
                background: linear-gradient(135deg, #4f8bff, #2563eb 62%, #1e40af);
                color: #ffffff;
                box-shadow: 0 16px 32px rgba(37, 99, 235, 0.2);
            }

            .offline-button-secondary {
                border-color: rgba(37, 99, 235, 0.15);
                background: #eff6ff;
                color: #1e40af;
            }

            @media (min-width: 640px) {
                .offline-actions {
                    flex-direction: row;
                }
            }
        </style>
    </head>
    <body>
        <main class="offline-shell">
            <section class="offline-card">
                <img
                    src="/icons/apple-touch-icon.png"
                    alt="EPIC Hub"
                    class="offline-icon"
                    width="88"
                    height="88"
                />

                <h1>Anda sedang offline</h1>
                <p>Beberapa fitur EPIC Hub membutuhkan koneksi internet. Periksa koneksi Anda lalu coba lagi.</p>
                <p class="offline-note">Semua transaksi, checkout, pembayaran, dan data akun tetap membutuhkan koneksi aktif.</p>

                <div class="offline-actions">
                    <button type="button" class="offline-button offline-button-primary" onclick="window.location.reload()">
                        Coba Lagi
                    </button>
                    <a href="{{ route('home') }}" class="offline-button offline-button-secondary">
                        Kembali ke Beranda
                    </a>
                </div>
            </section>
        </main>
    </body>
</html>
