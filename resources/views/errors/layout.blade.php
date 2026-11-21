{{-- Dasar halaman galat. Berdiri sendiri (tanpa layouts/app) karena galat 404 dan 419 bisa terjadi
     sebelum sesi dimulai. Tiap halaman wajib menyebut apa yang terjadi dan satu langkah berikutnya. --}}
<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('judul') - Laundry Abah</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@400..700&display=swap" />
    <link rel="stylesheet" href="{{ asset('css/base.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/motion.css') }}" />
  </head>
  <body>
    <header class="navbar">
      <a class="merek" href="{{ route('order.create') }}">
        <img src="{{ asset('img/Logo.png') }}" alt="" width="48" height="48" />
        <span class="teks"><span>Laundry</span> Abah</span>
      </a>
    </header>

    <div class="halaman">
      <main class="halaman-galat">
        <h1>@yield('judul')</h1>
        <p>@yield('penjelasan')</p>
        @yield('tindakan')
      </main>
    </div>
  </body>
</html>
