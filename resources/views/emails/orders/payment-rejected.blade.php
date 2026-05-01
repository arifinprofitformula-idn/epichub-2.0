@extends('emails.layouts.epic')

@section('content')
<p class="greeting">Pembayaran Perlu Diperiksa Ulang</p>

<p class="text">
  Halo <strong>{{ $userName }}</strong>, kami perlu informasi tambahan terkait pembayaran Anda.
  Tim kami tidak dapat memverifikasi bukti pembayaran yang dikirimkan.
</p>

<div class="danger-box">
  ❌ <strong>Pembayaran tidak dapat diverifikasi.</strong>
  @if (!empty($reason))
  <br><br>Alasan: {{ $reason }}
  @endif
</div>

<div class="info-box">
  <div class="info-row">
    <span class="info-label">Nomor Order</span>
    <span class="info-value">{{ $orderNumber }}</span>
  </div>
  <div class="info-row">
    <span class="info-label">Nomor Pembayaran</span>
    <span class="info-value">{{ $paymentNumber }}</span>
  </div>
</div>

<p class="text">
  Silakan upload ulang bukti pembayaran yang jelas dan sesuai. Pastikan:
</p>
<ul style="font-size:14px; color:#4b5563; margin: 0 0 20px 20px; line-height: 2;">
  <li>Foto/screenshot jelas dan tidak blur</li>
  <li>Menampilkan nominal, tanggal, dan nomor rekening tujuan</li>
  <li>Nominal transfer sesuai dengan jumlah order</li>
</ul>

<div class="cta-wrap">
  <a href="{{ $paymentUrl }}" class="cta-btn">Upload Ulang Bukti Pembayaran →</a>
</div>
@endsection
