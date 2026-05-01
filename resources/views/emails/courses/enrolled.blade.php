@extends('emails.layouts.epic')

@section('content')
<p class="greeting">Anda Terdaftar di Kelas Baru</p>

<p class="text">
  Halo <strong>{{ $userName }}</strong>, akses kelas Anda sudah aktif dan siap dipelajari.
</p>

<div class="info-box">
  <div class="info-row">
    <span class="info-label">Nama Kelas</span>
    <span class="info-value">{{ $courseName }}</span>
  </div>
  @if (filled($courseDescription))
  <div class="info-row">
    <span class="info-label">Ringkasan</span>
    <span class="info-value">{{ $courseDescription }}</span>
  </div>
  @endif
</div>

<div class="alert-box">
  Mulai dari materi pertama, lanjutkan progres secara rutin, dan selesaikan setiap lesson agar hasil belajar Anda lebih maksimal.
</div>

<div class="cta-wrap">
  <a href="{{ $courseUrl }}" class="cta-btn">Masuk Kelas →</a>
  <br>
  <a href="{{ $myCoursesUrl }}" class="cta-btn-secondary">Lihat Semua Kelas →</a>
</div>
@endsection
