@extends('emails.layouts.epic')

@section('content')
<p class="greeting">Selamat datang, {{ $userName }}! 🎉</p>

<p class="text">
  Akun EPIC HUB Anda berhasil dibuat. Mulai sekarang Anda bisa mengakses semua produk digital, kelas online, dan event eksklusif kami.
</p>

<div class="info-box">
  <div class="info-row">
    <span class="info-label">Email Login</span>
    <span class="info-value">{{ $userEmail }}</span>
  </div>
</div>

<div class="cta-wrap">
  <a href="{{ $dashboardUrl }}" class="cta-btn">Masuk ke Dashboard →</a>
</div>

<hr class="divider">

<p class="text">
  Di dashboard Anda bisa melihat semua produk yang sudah dibeli melalui menu <strong>Produk Saya</strong>.
  Jika Anda baru mendaftar, mulai eksplorasi produk kami sekarang!
</p>

<div class="cta-wrap">
  <a href="{{ $productsUrl }}" class="cta-btn-secondary">Lihat Produk Kami</a>
</div>
@endsection
