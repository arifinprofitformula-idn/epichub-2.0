@extends('emails.layouts.epic')

@section('content')
<p class="greeting">Halo, {{ $userName }}</p>

<p class="text">
  Pesanan Anda masih menunggu pembayaran. Silakan lanjutkan pembayaran agar order dapat segera diproses.
</p>

<div class="info-box">
  <div class="info-row">
    <span class="info-label">Nomor Order</span>
    <span class="info-value">{{ $orderNumber }}</span>
  </div>
  <div class="info-row">
    <span class="info-label">Total Pembayaran</span>
    <span class="info-value">Rp {{ number_format((float) $totalAmount, 0, ',', '.') }}</span>
  </div>
</div>

<p class="text"><strong>Produk:</strong></p>
<ul class="text" style="margin: 0 0 16px 18px; padding: 0;">
  @foreach ($products as $product)
    <li>{{ $product }}</li>
  @endforeach
</ul>

<div class="cta-wrap">
  <a href="{{ $paymentUrl }}" class="cta-btn">Lihat Invoice / Upload Bukti →</a>
</div>

<p class="text">
  Pengingat ke-{{ $attemptNumber }} ini dikirim agar pesanan Anda tidak tertinggal.
</p>
@endsection
