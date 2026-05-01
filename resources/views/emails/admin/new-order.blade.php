@extends('emails.layouts.epic')

@section('content')
<p class="greeting">📦 Order Baru Masuk</p>

<p class="text">
  Ada order baru yang perlu diproses. Berikut detail order:
</p>

<div class="info-box">
  <div class="info-row">
    <span class="info-label">Nomor Order</span>
    <span class="info-value"><strong>{{ $orderNumber }}</strong></span>
  </div>
  <div class="info-row">
    <span class="info-label">Customer</span>
    <span class="info-value">{{ $customerName }}</span>
  </div>
  <div class="info-row">
    <span class="info-label">Email</span>
    <span class="info-value">{{ $customerEmail }}</span>
  </div>
  @foreach ($products as $product)
  <div class="info-row">
    <span class="info-label">{{ $loop->first ? 'Produk' : '' }}</span>
    <span class="info-value">{{ $product }}</span>
  </div>
  @endforeach
  <div class="info-row">
    <span class="info-label">Total</span>
    <span class="info-value"><strong>Rp {{ number_format($totalAmount, 0, ',', '.') }}</strong></span>
  </div>
  <div class="info-row">
    <span class="info-label">Metode Bayar</span>
    <span class="info-value">{{ $paymentMethod }}</span>
  </div>
  <div class="info-row">
    <span class="info-label">Waktu Order</span>
    <span class="info-value">{{ $createdAt }}</span>
  </div>
</div>

<div class="cta-wrap">
  <a href="{{ $adminOrderUrl }}" class="cta-btn">Lihat di Admin Panel →</a>
</div>
@endsection
