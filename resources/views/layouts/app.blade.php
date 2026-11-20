<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'Laundry Abah')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@400..700&display=swap" />
    <link rel="stylesheet" href="{{ asset('css/base.css') }}?v={{ filemtime(public_path('css/base.css')) }}" />
    @stack('styles')
    {{-- Gerak dimuat terakhir supaya menimpa transisi di CSS halaman. Alasannya ada di motion.css. --}}
    <link rel="stylesheet" href="{{ asset('css/motion.css') }}?v={{ filemtime(public_path('css/motion.css')) }}" />
  </head>

  <body>
    <header class="navbar">
      <a class="merek" href="{{ url('/') }}">
        <img src="{{ asset('img/Logo.png') }}" alt="" width="48" height="48" />
        <span class="teks"><span>Laundry</span> Abah</span>
      </a>
      {{-- Tombol ini baru ditampilkan app.js. Tanpa JavaScript menu tetap tampil sebagai daftar di bawah logo. --}}
      <button type="button" class="tombol-menu" id="tombol-menu" aria-controls="menu-utama" aria-expanded="false" hidden>Menu</button>
      <nav id="menu-utama" aria-label="Menu utama">
        <a href=>Order</a>
        <a href=>Cek pesanan</a>
        <a href=>Bantuan</a>
        <div class="menu-akun">
            <a href="{{ route('masuk') }}" class="{{ request()->routeIs('masuk') ? 'aktif' : '' }}" @if (request()->routeIs('masuk')) aria-current="page" @endif>Masuk</a>
            <a href="{{ route('daftar') }}" class="tombol-nav {{ request()->routeIs('daftar') ? 'aktif' : '' }}" @if (request()->routeIs('daftar')) aria-current="page" @endif>Daftar</a>
        </div>
      </nav>
    </header>

    {{-- Isi halaman di sebelah kanan navbar samping (flex di base.css). --}}
    <div class="halaman">
      @yield('content')
    </div>
    <script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}" defer></script>
    @stack('scripts')
  </body>
</html>
