@extends('emails.layouts.epic')

@section('content')
<p class="greeting">Bukti Pembayaran Anda Sedang Diverifikasi 🔍</p>

<p class="text">
  Halo <strong>{{ $userName }}</strong>, kami telah menerima bukti pembayaran Anda.
  Tim kami akan memverifikasi pembayaran dalam waktu <strong>1×24 jam kerja</strong>.
</p>

<div class="info-box">
  <div class="info-row">
    <span class="info-label">Nomor Order</span>
    <span class="info-value">{{ $orderNumber }}</span>
  </div>
  <div class="info-row">
    <span class="info-label">Nomor Pembayaran</span>
    <span class="info-value">{{ $paymentNumber }}</span>
  </div>
  <div class="info-row">
    <span class="info-label">Jumlah</span>
    <span class="info-value">Rp {{ number_format($amount, 0, ',', '.') }}</span>
  </div>
</div>

<div class="alert-box">
  ⏳ <strong>Mohon tunggu verifikasi kami.</strong><br>
  Setelah pembayaran diverifikasi, Anda akan mendapat email konfirmasi dan akses produk akan langsung aktif.
</div>

<div class="cta-wrap">
  <a href="{{ $paymentUrl }}" class="cta-btn">Lihat Status Pembayaran →</a>
</div>
@endsection
