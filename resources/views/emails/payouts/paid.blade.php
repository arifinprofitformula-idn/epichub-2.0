@extends('emails.layouts.epic')

@section('content')
<p class="greeting">Payout Komisi Telah Diproses</p>

<p class="text">
  Halo <strong>{{ $userName }}</strong>, payout komisi Anda sudah diproses oleh tim EPIC HUB.
</p>

<div class="success-box">
  Dana payout telah ditandai selesai diproses. Silakan cek detail berikut.
</div>

<div class="info-box">
  <div class="info-row">
    <span class="info-label">Nomor Payout</span>
    <span class="info-value">{{ $payoutNumber }}</span>
  </div>
  <div class="info-row">
    <span class="info-label">Nominal</span>
    <span class="info-value"><strong>{{ $payoutAmount }}</strong></span>
  </div>
  <div class="info-row">
    <span class="info-label">Tanggal Pembayaran</span>
    <span class="info-value">{{ $paidAt }}</span>
  </div>
  <div class="info-row">
    <span class="info-label">Tujuan Rekening</span>
    <span class="info-value">{{ $paymentDestination }}</span>
  </div>
</div>

<div class="cta-wrap">
  <a href="{{ $payoutUrl }}" class="cta-btn">Lihat Riwayat Payout →</a>
</div>
@endsection
