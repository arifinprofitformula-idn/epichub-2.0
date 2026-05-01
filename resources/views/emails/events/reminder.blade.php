@extends('emails.layouts.epic')

@section('content')
<p class="greeting">Halo, {{ $userName }}</p>

<p class="text">
  Event <strong>{{ $eventName }}</strong> akan dimulai {{ $reminderLabel }}.
</p>

<div class="info-box">
  <div class="info-row">
    <span class="info-label">Jadwal</span>
    <span class="info-value">{{ $eventSchedule }}</span>
  </div>
  <div class="info-row">
    <span class="info-label">Lokasi / Akses</span>
    <span class="info-value">{{ $eventLocation }}</span>
  </div>
</div>

<p class="text">
  Untuk keamanan akses, link Zoom tidak dikirim lewat email jika belum waktunya. Silakan buka halaman Event Saya untuk melihat akses yang tersedia sesuai kebijakan event.
</p>

<div class="cta-wrap">
  <a href="{{ $myEventUrl }}" class="cta-btn">Buka Event Saya →</a>
</div>

<div class="cta-wrap">
  <a href="{{ $myEventsUrl }}" class="cta-btn-secondary">Lihat Semua Event Saya</a>
</div>
@endsection
