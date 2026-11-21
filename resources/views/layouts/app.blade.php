<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'Laundry Abah')</title>

    <!-- Muat font Outfit sesuai token --font di base.css -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('css/base.css') }}?v={{ filemtime(public_path('css/base.css')) }}" />
    <link rel="stylesheet" href="{{ asset('css/bahan.css') }}?v={{ filemtime(public_path('css/bahan.css')) }}" />
    <link rel="stylesheet" href="{{ asset('css/motion.css') }}?v={{ filemtime(public_path('css/motion.css')) }}" />
    @stack('styles')
</head>
<body>
    <header class="navbar">
        <a class="merek" href="{{ route('bahan.index') }}">
            <img src="{{ asset('img/Logo.png') }}" alt="Logo Laundry Abah" width="48" height="48" />
            <span class="teks"><span>Laundry</span> Abah</span>
        </a>

        <button type="button" class="tombol-menu" id="tombol-menu" aria-controls="menu-utama" aria-expanded="false" hidden>Menu</button>

        <nav id="menu-utama" aria-label="Menu utama">
            @if(Route::has('order.index'))
                <a href="{{ route('order.index') }}" class="{{ request()->routeIs('order.*') ? 'aktif' : '' }}">Order</a>
            @endif

            @if(Route::has('order.cek'))
                <a href="{{ route('order.cek') }}" class="{{ request()->routeIs('order.cek') ? 'aktif' : '' }}">Cek pesanan</a>
            @endif

            <a href="{{ Route::has('bantuan') ? route('bantuan') : '#' }}" class="{{ request()->routeIs('bantuan') ? 'aktif' : '' }}">Bantuan</a>

            @auth('admin')
                <div class="menu-admin">
                    @if(Route::has('admin.order.index'))
                        <a href="{{ route('admin.order.index') }}" class="{{ request()->routeIs('admin.order.*') ? 'aktif' : '' }}">Kelola pesanan</a>
                    @endif

                    <a href="{{ route('bahan.index') }}" class="{{ request()->routeIs('bahan.*') ? 'aktif' : '' }}" @if(request()->routeIs('bahan.*')) aria-current="page" @endif>Kelola stock</a>

                    @if(Route::has('admin.komplain.index'))
                        <a href="{{ route('admin.komplain.index') }}" class="{{ request()->routeIs('admin.komplain.*') ? 'aktif' : '' }}">Komplain</a>
                    @endif

                    @if(Route::has('admin.audit') && in_array(auth('admin')->user()->peran ?? auth('admin')->user()->role ?? '', ['admin']))
                        <a href="{{ route('admin.audit') }}" class="{{ request()->routeIs('admin.audit') ? 'aktif' : '' }}">Audit</a>
                    @endif
                </div>
            @endauth

            <div class="menu-akun">
                @auth('admin')
                    @php
                        $userAdmin = auth('admin')->user();
                        $peranLabel = ucfirst($userAdmin->peran ?? $userAdmin->role ?? 'Admin');
                        $namaUser = $userAdmin->nama ?? $userAdmin->name ?? 'User';
                    @endphp
                    <div class="akun-nama">
                        {{ $peranLabel }}: {{ $namaUser }}
                    </div>

                    <form action="{{ route('admin.keluar') }}" method="POST">
                        @csrf
                        <button type="submit" class="tautan-keluar">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('admin.masuk') }}">Masuk Admin</a>
                @endauth
            </div>
        </nav>
    </header>

    <div class="halaman">
        <main>
            @yield('content')
        </main>
    </div>

    <script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}"></script>
    @stack('scripts')
</body>
</html>