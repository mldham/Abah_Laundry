@extends('layouts.app')

@section('title', 'Masuk')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/auth.css') }}" />
@endpush

@section('content')
<main class="auth">
  <h1>Masuk</h1>

  <form class="form-auth" method="post" action="{{ route('masuk.proses') }}" data-kunci>
    @csrf

    <div class="grup">
      <label for="telepon">Nomor WhatsApp</label>
      <input type="tel" id="telepon" name="telepon" value="{{ old('telepon') }}" inputmode="tel"
             autocomplete="username" placeholder="08xxxxxxxxxx" required
             @error('telepon') aria-invalid="true" aria-describedby="telepon-galat" @enderror>
      @error('telepon')
        <p class="galat" id="telepon-galat" role="alert">{{ $message }}</p>
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

    <label class="ingat" for="ingat">
      <input type="checkbox" id="ingat" name="ingat" value="1" @checked(old('ingat', ! $errors->any()))>
      Tetap masuk di perangkat ini
    </label>

    <button type="submit" class="tombol tombol-auth" data-teks-proses="Masuk...">Masuk</button>
  </form>

  <p class="alihan">Belum punya akun? <a href="{{ route('daftar') }}">Daftar dulu</a>.</p>
  <p class="alihan">Pengelola laundry? <a href="{{ route('admin.masuk') }}">Masuk sebagai admin</a>.</p>
</main>
@endsection
