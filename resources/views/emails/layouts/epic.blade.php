<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
@php
  $replyToEmail = app(\App\Services\Settings\AppSettingService::class)
    ->getMailketing('mailketing_reply_to_email', config('mail.from.address', 'support@epichub.id'));
@endphp
<title>{{ $subject ?? config('app.name', 'EPIC HUB') }}</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: Arial, Helvetica, sans-serif; background: #eef2f7; color: #172033; line-height: 1.6; }
  .wrapper { max-width: 600px; margin: 0 auto; padding: 24px 16px; }
  .card { background: #ffffff; border-radius: 18px; overflow: hidden; box-shadow: 0 18px 36px rgba(15, 23, 42, .08); border: 1px solid #dbe3ee; }
  .header { background: radial-gradient(circle at top, rgba(245, 158, 11, .16), transparent 34%), linear-gradient(135deg, #0f172a 0%, #111827 52%, #1e293b 100%); padding: 36px 40px; text-align: center; }
  .header-logo-container { margin-bottom: 14px; }
  .header-logo-image { max-width: 88px; height: auto; display: inline-block; filter: drop-shadow(0 8px 20px rgba(245, 158, 11, .18)); }
  .header-logo { color: #f8fafc; font-size: 24px; font-weight: 800; letter-spacing: 2.4px; }
  .header-tagline { color: #fbbf24; font-size: 12px; margin-top: 6px; letter-spacing: 2px; text-transform: uppercase; }
  .body { padding: 40px; }
  .greeting { font-size: 18px; font-weight: 700; color: #1a1a2e; margin-bottom: 16px; }
  .text { font-size: 15px; color: #4b5563; margin-bottom: 16px; }
  .info-box { background: #f8fafc; border-left: 4px solid #f59e0b; border-radius: 0 8px 8px 0; padding: 16px 20px; margin: 20px 0; }
  .info-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #e5e7eb; font-size: 14px; }
  .info-row:last-child { border-bottom: none; }
  .info-label { color: #6b7280; font-weight: 600; }
  .info-value { color: #1a1a2e; font-weight: 500; text-align: right; max-width: 60%; }
  .cta-wrap { text-align: center; margin: 28px 0; }
  .cta-btn { display: inline-block; background: #f59e0b; color: #1a1a2e; font-weight: 700; font-size: 15px; padding: 14px 32px; border-radius: 8px; text-decoration: none; letter-spacing: .5px; }
  .cta-btn-secondary { display: inline-block; background: #1a1a2e; color: #f59e0b; font-weight: 700; font-size: 14px; padding: 12px 28px; border-radius: 8px; text-decoration: none; margin-top: 12px; }
  .divider { border: none; border-top: 1px solid #e5e7eb; margin: 28px 0; }
  .alert-box { background: #fef3c7; border: 1px solid #fbbf24; border-radius: 8px; padding: 14px 18px; margin: 20px 0; font-size: 14px; color: #92400e; }
  .success-box { background: #d1fae5; border: 1px solid #34d399; border-radius: 8px; padding: 14px 18px; margin: 20px 0; font-size: 14px; color: #065f46; }
  .danger-box { background: #fee2e2; border: 1px solid #f87171; border-radius: 8px; padding: 14px 18px; margin: 20px 0; font-size: 14px; color: #991b1b; }
  .footer { background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%); padding: 24px 40px; text-align: center; border-top: 1px solid #e5e7eb; }
  .footer-text { font-size: 12px; color: #64748b; line-height: 1.8; }
  .footer-link { color: #b45309; text-decoration: none; font-weight: 700; }
  @media (max-width: 480px) {
    .body, .footer { padding: 28px 20px; }
    .header { padding: 24px 20px; }
    .info-row { flex-direction: column; gap: 4px; }
    .info-value { text-align: left; max-width: 100%; }
  }
</style>
</head>
<body>
<div class="wrapper">
  <div class="card">

    <div class="header">
      <div class="header-logo-container">
        <img src="{{ asset('icons/icon-192.png') }}" alt="EPIC HUB" class="header-logo-image" />
      </div>
      <div class="header-logo">EPIC HUB</div>
      <div class="header-tagline">Connect Grow Impact</div>
    </div>

    <div class="body">
      @isset($htmlContent)
        {!! $htmlContent !!}
      @else
        @yield('content')
      @endisset
    </div>

    <div class="footer">
      <p class="footer-text">
        Email ini dikirim secara otomatis oleh sistem EPIC HUB.<br>
        Jika ada pertanyaan, hubungi kami di
        <a href="mailto:{{ $replyToEmail }}" class="footer-link">{{ $replyToEmail }}</a><br><br>
        &copy; 2026 EPIC HUB. Hak cipta dilindungi.
      </p>
    </div>

  </div>
</div>
</body>
</html>
