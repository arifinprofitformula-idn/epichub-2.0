@extends('emails.layouts.epic')

@section('content')
<p class="greeting">Akses Produk Anda Sudah Aktif! 🚀</p>

<p class="text">
  Halo <strong>{{ $userName }}</strong>! Akses Anda ke produk berikut telah diberikan dan siap digunakan.
</p>

<div class="success-box">
  ✅ Akses produk aktif dan dapat digunakan sekarang.
</div>

<div class="info-box">
  <div class="info-row">
    <span class="info-label">Produk</span>
    <span class="info-value"><strong>{{ $productName }}</strong></span>
  </div>
  @if (!empty($productType))
  <div class="info-row">
    <span class="info-label">Jenis</span>
    <span class="info-value">{{ $productType }}</span>
  </div>
  @endif
</div>

<div class="cta-wrap">
  <a href="{{ $accessUrl }}" class="cta-btn">Akses Produk Sekarang →</a>
</div>

<p class="text" style="font-size: 14px;">
  Semua produk Anda juga dapat diakses melalui menu <strong>Produk Saya</strong> di dashboard.
</p>

<div class="cta-wrap">
  <a href="{{ $myProductsUrl }}" class="cta-btn-secondary">Produk Saya →</a>
</div>
@endsection
