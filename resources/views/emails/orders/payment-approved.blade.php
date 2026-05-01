@extends('emails.layouts.epic')

@section('content')
<p class="greeting">Pembayaran Berhasil, Akses Produk Aktif! 🎊</p>

<p class="text">
  Selamat <strong>{{ $userName }}</strong>! Pembayaran Anda telah diverifikasi dan akses produk Anda sudah aktif.
</p>

<div class="success-box">
  ✅ <strong>Pembayaran diterima dan diverifikasi oleh tim kami.</strong>
</div>

<div class="info-box">
  <div class="info-row">
    <span class="info-label">Nomor Order</span>
    <span class="info-value">{{ $orderNumber }}</span>
  </div>
  @foreach ($products as $product)
  <div class="info-row">
    <span class="info-label">{{ $loop->first ? 'Produk' : '' }}</span>
    <span class="info-value">{{ $product }}</span>
  </div>
  @endforeach
</div>

<div class="cta-wrap">
  <a href="{{ $myProductsUrl }}" class="cta-btn">Produk Saya →</a>
  @if (!empty($myCoursesUrl))
  <br>
  <a href="{{ $myCoursesUrl }}" class="cta-btn-secondary">Kelas Saya →</a>
  @endif
  @if (!empty($myEventsUrl))
  <br>
  <a href="{{ $myEventsUrl }}" class="cta-btn-secondary" style="margin-top: 8px;">Event Saya →</a>
  @endif
</div>

<p class="text" style="font-size: 14px;">
  Jika ada pertanyaan atau kendala akses, jangan ragu menghubungi kami.
</p>
@endsection
