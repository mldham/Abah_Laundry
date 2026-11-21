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
        <a href="{{ route('order.create') }}" class="{{ request()->routeIs('order.create') ? 'aktif' : '' }}" @if (request()->routeIs('order.create')) aria-current="page" @endif>Order</a>
        <a href="{{ route('order.index') }}" class="{{ request()->routeIs('order.index') ? 'aktif' : '' }}" @if (request()->routeIs('order.index')) aria-current="page" @endif>Cek pesanan</a>
        <a href="{{ route('bantuan') }}" class="{{ request()->routeIs('bantuan') ? 'aktif' : '' }}" @if (request()->routeIs('bantuan')) aria-current="page" @endif>Bantuan</a>
        @auth('admin')
          @php
            $admin = auth('admin')->user();
            $komplainBaru = \App\Models\Komplain::where('status', 'baru')->count();
          @endphp
          <div class="menu-admin">
            <a href="{{ route('admin.order.index') }}" class="{{ request()->routeIs('admin.order.*') ? 'aktif' : '' }}" @if (request()->routeIs('admin.order.*')) aria-current="page" @endif>Kelola pesanan</a>
            <a href="{{ route('admin.komplain.index') }}" class="{{ request()->routeIs('admin.komplain.*') ? 'aktif' : '' }}" @if (request()->routeIs('admin.komplain.*')) aria-current="page" @endif>
              Komplain
              @if ($komplainBaru > 0)
                <span class="lencana">{{ $komplainBaru }}<span class="sr-only"> komplain baru</span></span>
              @endif
            </a>
            @if ($admin->adalahAdmin())
              <a href="{{ route('admin.audit') }}" class="{{ request()->routeIs('admin.audit') ? 'aktif' : '' }}" @if (request()->routeIs('admin.audit')) aria-current="page" @endif>Audit</a>
            @endif
          </div>
          <div class="menu-akun">
            <span class="akun-nama" title="{{ $admin->name }} ({{ $admin->label_peran }})">{{ $admin->label_peran }}: {{ \Illuminate\Support\Str::words($admin->name, 1, '') }}</span>
            <form method="post" action="{{ route('admin.keluar') }}">
              @csrf
              <button type="submit" class="tautan-keluar">Keluar</button>
            </form>
          </div>
        @elseauth('pelanggan')
          <div class="menu-akun">
            <span class="akun-nama">Halo, {{ \Illuminate\Support\Str::words(auth('pelanggan')->user()->nama, 1, '') }}</span>
            <form method="post" action="{{ route('keluar') }}">
              @csrf
              <button type="submit" class="tautan-keluar">Keluar</button>
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
