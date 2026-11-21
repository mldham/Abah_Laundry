<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('judul', 'Laundry Abah')</title>
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
        <a href="">Cek pesanan</a>
        <a href="">Bantuan</a>

        @if (auth('admin')->check())
          <hr class="pemisah-menu" style="width: 100%; border: 0; border-top: 1px solid rgba(255, 255, 255, 0.16); margin: 0.5rem 0;" />

          <a href="{{ route('pelanggan.index') }}" class="{{ request()->routeIs('pelanggan.*') ? 'aktif' : '' }}">Data pelanggan</a>
          <a href="{{ Route::has('admin.order.index') ? route('admin.order.index') : '#' }}" class="{{ request()->routeIs('admin.order.*') ? 'aktif' : '' }}">Kelola pesanan</a>
          <a href="{{ Route::has('admin.komplain.index') ? route('admin.komplain.index') : '#' }}" class="{{ request()->routeIs('admin.komplain.*') ? 'aktif' : '' }}">Komplain</a>

          @if (auth('admin')->user()->peran === 'admin')
            <a href="{{ Route::has('admin.audit') ? route('admin.audit') : '#' }}" class="{{ request()->routeIs('admin.audit') ? 'aktif' : '' }}">Audit</a>
          @endif

          <div class="menu-akun">
            <span class="info-akun" style="display: block; padding: 0.45rem 0.85rem; font-weight: 600; font-size: 0.92rem; color: #ffffff;">
              {{ ucfirst(auth('admin')->user()->peran) }}: {{ auth('admin')->user()->name }}
            </span>
            <form action="{{ route('admin.keluar') }}" method="POST" style="margin: 0;">
              @csrf
              <button type="submit" class="tombol-keluar" style="background: none; border: 0; color: rgba(255, 255, 255, 0.85); font: inherit; padding: 0.45rem 0.85rem; width: 100%; text-align: left; cursor: pointer;">
                Keluar
              </button>
            </form>
          </div>
        @else
          <div class="menu-akun">
            <a href="{{ route('masuk') }}" class="{{ request()->routeIs('masuk') ? 'aktif' : '' }}">Masuk</a>
            <a href="{{ route('daftar') }}" class="tombol-nav {{ request()->routeIs('daftar') ? 'aktif' : '' }}">Daftar</a>
          </div>
        @endif
      </nav>
    </header>

    {{-- Isi halaman di sebelah kanan navbar samping (flex di base.css). --}}
    <div class="halaman">
      @yield('isi')
      @yield('content')
    </div>
    <script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}" defer></script>
    @stack('scripts')
  </body>
</html>