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
        <a href="{{ url('/') }}">Order</a>
        <a href="#">Cek pesanan</a>
        <a href="#">Bantuan</a>

        @auth('admin')
          <hr style="border: none; border-top: 1px solid rgba(255,255,255,0.2); margin: 10px 0;">
          <a href="{{ route('admin.order.index') }}" class="{{ request()->routeIs('admin.order.*') ? 'aktif' : '' }}" @if (request()->routeIs('admin.order.*')) aria-current="page" @endif>Kelola pesanan</a>
          <a href="{{ route('admin.mesin.index') }}" class="{{ request()->routeIs('admin.mesin.*') ? 'aktif' : '' }}" @if (request()->routeIs('admin.mesin.*')) aria-current="page" @endif>Mesin &amp; Peralatan</a>
          <a href="{{ route('admin.audit') }}" class="{{ request()->routeIs('admin.audit') ? 'aktif' : '' }}" @if (request()->routeIs('admin.audit')) aria-current="page" @endif>Audit</a>

          <div class="menu-akun">
            <span style="display: block; padding: 6px 12px; font-weight: 600; font-size: 14px;">
              Admin: {{ Auth::guard('admin')->user()->name ?? Auth::guard('admin')->user()->nama ?? 'Admin' }}
            </span>
            <form action="{{ route('admin.keluar') }}" method="POST">
              @csrf
              <button type="submit" style="background: none; border: none; color: inherit; font: inherit; cursor: pointer; padding: 6px 12px; text-align: left; width: 100%;">
                Keluar
              </button>
            </form>
          </div>
        @else
          <div class="menu-akun">
            <a href="{{ route('masuk') }}" class="{{ request()->routeIs('masuk') ? 'aktif' : '' }}" @if (request()->routeIs('masuk')) aria-current="page" @endif>Masuk</a>
            <a href="{{ route('daftar') }}" class="tombol-nav {{ request()->routeIs('daftar') ? 'aktif' : '' }}" @if (request()->routeIs('daftar')) aria-current="page" @endif>Daftar</a>
          </div>
        @endauth
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
