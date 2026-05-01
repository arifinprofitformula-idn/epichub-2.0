@extends('emails.layouts.epic')

@section('content')
<p class="greeting">Komisi Affiliate Baru Masuk</p>

<p class="text">
  Halo <strong>{{ $userName }}</strong>, ada komisi baru yang sudah masuk ke dashboard Anda.
</p>

<div class="info-box">
  <div class="info-row">
    <span class="info-label">Produk</span>
    <span class="info-value">{{ $productName }}</span>
  </div>
  <div class="info-row">
    <span class="info-label">Nominal Komisi</span>
    <span class="info-value"><strong>{{ $commissionAmount }}</strong></span>
  </div>
  <div class="info-row">
    <span class="info-label">Status</span>
    <span class="info-value">{{ $commissionStatus }}</span>
  </div>
</div>

<div class="cta-wrap">
  <a href="{{ $commissionUrl }}" class="cta-btn">Lihat Dashboard Komisi →</a>
</div>
@endsection
