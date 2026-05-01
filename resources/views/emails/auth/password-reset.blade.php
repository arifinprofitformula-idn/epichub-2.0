@extends('emails.layouts.epic')

@section('content')
<p class="greeting">Reset Password EPIC HUB</p>

<p class="text">
  Kami menerima permintaan untuk mereset password akun EPIC HUB Anda.
  Klik tombol di bawah ini untuk melanjutkan proses reset password.
</p>

<div class="cta-wrap">
  <a href="{{ $resetUrl }}" class="cta-btn">Reset Password Sekarang →</a>
</div>

<div class="alert-box">
  ⏰ <strong>Link ini hanya berlaku selama {{ $expiryMinutes ?? 60 }} menit</strong> dan hanya bisa digunakan sekali.
  Setelah digunakan atau kedaluwarsa, link tidak dapat dipakai lagi.
</div>

<hr class="divider">

<p class="text" style="font-size: 13px; color: #6b7280;">
  Jika Anda tidak merasa meminta reset password, abaikan email ini.
  Akun Anda tetap aman dan tidak ada perubahan yang terjadi.
</p>

<p class="text" style="font-size: 12px; color: #9ca3af; word-break: break-all;">
  Jika tombol di atas tidak berfungsi, salin dan buka URL berikut di browser Anda:<br>
  {{ $resetUrl }}
</p>
@endsection
