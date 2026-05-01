@extends('emails.layouts.epic')

@section('content')
<p class="greeting">Payout Komisi Diproses</p>

<p class="text">
  Tim admin baru saja memproses payout komisi untuk member EPI Channel berikut.
</p>

<div class="info-box">
  <div class="info-row">
    <span class="info-label">Member</span>
    <span class="info-value">{{ $memberName }}</span>
  </div>
  <div class="info-row">
    <span class="info-label">Email</span>
    <span class="info-value">{{ $memberEmail }}</span>
  </div>
  <div class="info-row">
    <span class="info-label">ID EPIC</span>
    <span class="info-value">{{ $epicCode }}</span>
  </div>
  <div class="info-row">
    <span class="info-label">Nomor Payout</span>
    <span class="info-value">{{ $payoutNumber }}</span>
  </div>
  <div class="info-row">
    <span class="info-label">Nominal</span>
    <span class="info-value"><strong>{{ $payoutAmount }}</strong></span>
  </div>
  <div class="info-row">
    <span class="info-label">Diproses Pada</span>
    <span class="info-value">{{ $paidAt }}</span>
  </div>
</div>

<div class="cta-wrap">
  <a href="{{ $adminPayoutUrl }}" class="cta-btn">Lihat Data Payout →</a>
</div>
@endsection
