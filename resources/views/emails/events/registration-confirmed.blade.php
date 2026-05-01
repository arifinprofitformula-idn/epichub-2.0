@extends('emails.layouts.epic')

@section('content')
<p class="greeting">Registrasi Event Berhasil</p>

<p class="text">
  Halo <strong>{{ $userName }}</strong>, Anda sudah terdaftar pada event berikut.
</p>

<div class="success-box">
  Tempat Anda sudah dicadangkan. Silakan cek halaman Event Saya untuk detail akses terbaru.
</div>

<div class="info-box">
  <div class="info-row">
    <span class="info-label">Nama Event</span>
    <span class="info-value">{{ $eventName }}</span>
  </div>
  <div class="info-row">
    <span class="info-label">Tanggal & Waktu</span>
    <span class="info-value">{{ $eventSchedule }}</span>
  </div>
  <div class="info-row">
    <span class="info-label">Lokasi</span>
    <span class="info-value">{{ $eventLocation }}</span>
  </div>
</div>

<div class="alert-box">
  <strong>Aturan akses event:</strong><br>
  @foreach ($accessGuidance as $guidance)
    • {{ $guidance }}<br>
  @endforeach
</div>

<div class="cta-wrap">
  <a href="{{ $myEventUrl }}" class="cta-btn">Lihat Event Saya →</a>
</div>

<p class="text" style="font-size: 14px;">
  Jika halaman event sudah diperbarui, Anda juga bisa melihat daftar lengkap event di
  <a href="{{ $myEventsUrl }}" style="color: #f59e0b; text-decoration: none;">Event Saya</a>.
</p>
@endsection
