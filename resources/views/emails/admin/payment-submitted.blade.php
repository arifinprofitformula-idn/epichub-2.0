@extends('emails.layouts.epic')

@section('content')
<p class="greeting">🔔 Bukti Pembayaran Baru Perlu Dicek</p>

<p class="text">
  Customer baru saja mengupload bukti pembayaran. Silakan verifikasi segera.
</p>

<div class="info-box">
  <div class="info-row">
    <span class="info-label">Nomor Payment</span>
    <span class="info-value"><strong>{{ $paymentNumber }}</strong></span>
  </div>
  <div class="info-row">
    <span class="info-label">Nomor Order</span>
    <span class="info-value">{{ $orderNumber }}</span>
  </div>
  <div class="info-row">
    <span class="info-label">Customer</span>
    <span class="info-value">{{ $customerName }}</span>
  </div>
  <div class="info-row">
    <span class="info-label">Email</span>
    <span class="info-value">{{ $customerEmail }}</span>
  </div>
  <div class="info-row">
    <span class="info-label">Jumlah</span>
    <span class="info-value"><strong>Rp {{ number_format($amount, 0, ',', '.') }}</strong></span>
  </div>
  <div class="info-row">
    <span class="info-label">Dikirim pada</span>
    <span class="info-value">{{ $submittedAt }}</span>
  </div>
</div>

<div class="alert-box">
  ⚡ Verifikasi segera agar akses produk customer dapat diaktifkan.
</div>

<div class="cta-wrap">
  <a href="{{ $adminPaymentUrl }}" class="cta-btn">Verifikasi Pembayaran →</a>
</div>
@endsection
