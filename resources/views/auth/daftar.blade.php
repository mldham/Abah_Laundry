@extends('layouts.app')

@section('title', 'Daftar')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/auth.css') }}" />
@endpush

@section('content')
<main class="auth">
  <h1>Daftar</h1>

  @if (session('pesanan_baru'))
    <div class="kabar" role="status">
      <p class="kabar-judul">Pesanan #{{ session('pesanan_baru.id') }} sudah masuk.</p>
      <p class="kabar-isi">
        Dijemput {{ session('pesanan_baru.jemput') }}. Buat akun dengan nomor ini supaya status cuciannya bisa dicek.
      </p>
    </div>
  @endif

  <form class="form-auth" method="post" action="{{ route('daftar.proses') }}" data-kunci>
    @csrf

    <div class="grup">
      <label for="nama">Nama lengkap</label>
      <input type="text" id="nama" name="nama" value="{{ old('nama') }}" autocomplete="name" required
             @error('nama') aria-invalid="true" aria-describedby="nama-galat" @enderror>
      @error('nama')
        <p class="galat" id="nama-galat" role="alert">{{ $message }}</p>
      @enderror
    </div>

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
      <input type="password" id="password" name="password" autocomplete="new-password" minlength="6" required
             aria-describedby="{{ $errors->has('password') ? 'password-galat ' : '' }}password-petunjuk"
             @error('password') aria-invalid="true" @enderror>
      @error('password')
        <p class="galat" id="password-galat" role="alert">{{ $message }}</p>
      @enderror
      <p class="petunjuk" id="password-petunjuk">Minimal 6 karakter.</p>
    </div>

    <div class="grup">
      <label for="password_confirmation">Ulangi kata sandi</label>
      <input type="password" id="password_confirmation" name="password_confirmation"
             autocomplete="new-password" minlength="6" required
             @error('password') aria-invalid="true" @enderror>
    </div>

    <button type="submit" class="tombol tombol-auth" data-teks-proses="Membuat akun...">Buat akun</button>
  </form>

  <p class="alihan">Sudah punya akun? <a href="{{ route('masuk') }}">Masuk</a>.</p>
</main>
@endsection
