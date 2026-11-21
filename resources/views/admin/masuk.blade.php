@extends('layouts.app')

@section('judul', 'Masuk admin')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/auth.css') }}" />
@endpush

@section('isi')
<main class="auth">
  <h1>Masuk admin</h1>

  <form class="form-auth" method="post" action="{{ route('admin.masuk.proses') }}" data-kunci>
    @csrf

    <div class="grup">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="username" required
             @error('email') aria-invalid="true" aria-describedby="email-galat" @enderror>
      @error('email')
        <p class="galat" id="email-galat" role="alert">{{ $message }}</p>
      @enderror
    </div>

    <div class="grup">
      <label for="password">Kata sandi</label>
      <input type="password" id="password" name="password" autocomplete="current-password" required
             @error('password') aria-invalid="true" aria-describedby="password-galat" @enderror>
      @error('password')
        <p class="galat" id="password-galat" role="alert">{{ $message }}</p>
      @enderror
    </div>

    <button type="submit" class="tombol tombol-auth" data-teks-proses="Masuk...">Masuk</button>
  </form>

  <p class="alihan">Pelanggan? <a href="{{ route('masuk') }}">Masuk dengan nomor WhatsApp</a>.</p>
</main>
@endsection
