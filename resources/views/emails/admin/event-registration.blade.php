@extends('emails.layouts.epic')

@section('content')
<p class="greeting">Pendaftaran Event Baru</p>

<p class="text">
  Admin alert: ada registrasi event baru yang perlu diketahui tim.
</p>

<div class="info-box">
  <div class="info-row">
    <span class="info-label">Event</span>
    <span class="info-value">{{ $eventName }}</span>
  </div>
  <div class="info-row">
    <span class="info-label">Peserta</span>
    <span class="info-value">{{ $participantName }}</span>
  </div>
  <div class="info-row">
    <span class="info-label">Email</span>
    <span class="info-value">{{ $participantEmail }}</span>
  </div>
  <div class="info-row">
    <span class="info-label">Waktu Registrasi</span>
    <span class="info-value">{{ $registeredAt }}</span>
  </div>
</div>

<div class="cta-wrap">
  <a href="{{ $adminEventRegistrationUrl }}" class="cta-btn">Buka Admin Panel →</a>
</div>
@endsection
