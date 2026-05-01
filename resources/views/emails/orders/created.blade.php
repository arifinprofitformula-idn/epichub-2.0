@extends('emails.layouts.epic')

@section('content')
<p class="greeting">Order Anda Berhasil Dibuat ✅</p>

<p class="text">
  Terima kasih, <strong>{{ $userName }}</strong>! Order Anda telah kami terima.
  Silakan lakukan pembayaran sesuai instruksi di bawah ini agar pesanan segera diproses.
</p>

<div class="info-box">
  <div class="info-row">
    <span class="info-label">Nomor Order</span>
    <span class="info-value"><strong>{{ $orderNumber }}</strong></span>
  </div>
  @foreach ($products as $product)
  <div class="info-row">
    <span class="info-label">{{ $loop->first ? 'Produk' : '' }}</span>
    <span class="info-value">{{ $product }}</span>
  </div>
  @endforeach
  <div class="info-row">
    <span class="info-label">Total Pembayaran</span>
    <span class="info-value"><strong>Rp {{ number_format($totalAmount, 0, ',', '.') }}</strong></span>
  </div>
</div>

@if ($paymentMethod === 'manual_bank_transfer')
<div class="alert-box">
  🏦 <strong>Instruksi Pembayaran</strong><br>
  Transfer sejumlah <strong>Rp {{ number_format($totalAmount, 0, ',', '.') }}</strong> ke rekening yang tertera di halaman pembayaran,
  kemudian upload bukti transfer Anda.
</div>
@endif

<div class="cta-wrap">
  <a href="{{ $paymentUrl }}" class="cta-btn">Lihat Invoice & Upload Bukti →</a>
</div>
@endsection
