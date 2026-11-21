@extends('layouts.app')

@section('title', 'Bantuan')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/bantuan.css') }}" />
@endpush

@section('content')
<main class="bantuan">
  <h1>Bantuan</h1>
  <p class="pengantar">Cara memesan, arti status, dan biaya. Semua angka di sini sama dengan yang dipakai saat pesananmu dihitung.</p>

  <section aria-labelledby="judul-cara">
    <h2 id="judul-cara">Cara memesan</h2>
    <ol class="langkah">
      <li>Pilih layanan di halaman <a href="{{ route('order.create') }}">Order</a>.</li>
      <li>Isi tanggal dan alamat jemput. Kalau belum punya akun, isi juga nama dan nomor WhatsApp.</li>
      <li>Kirim pesanan. Kami menjemput cuciannya pada tanggal itu.</li>
      <li>Lihat perkembangannya di <a href="{{ route('order.index') }}">Cek pesanan</a>. Halaman itu butuh akun dengan nomor WhatsApp yang sama dengan pesananmu.</li>
    </ol>
  </section>

  <section aria-labelledby="judul-status">
    <h2 id="judul-status">Arti status pesanan</h2>
    <dl class="daftar-status">
      @foreach ($status as $s)
        <dt>{{ $label[$s] }}</dt>
        <dd>{{ $penjelasan[$s] }}</dd>
      @endforeach
    </dl>
  </section>

  <section aria-labelledby="judul-biaya">
    <h2 id="judul-biaya">Harga dan ongkir</h2>
    <ul class="daftar-harga">
      @foreach ($layanan as $item)
        <li>
          <span>{{ $item['nama'] }}</span>
          <strong>Rp{{ number_format($item['harga'], 0, ',', '.') }} / {{ $item['satuan'] }}</strong>
        </li>
      @endforeach
    </ul>
    <p>Total harga dihitung setelah cucian ditimbang atau dihitung jumlahnya. Ongkir Rp{{ number_format($ongkir, 0, ',', '.') }}, gratis untuk alamat di {{ $areaGratis }}.</p>
  </section>

  <section aria-labelledby="judul-batal">
    <h2 id="judul-batal">Membatalkan pesanan</h2>
    <p>Pesanan bisa dibatalkan dari <a href="{{ route('order.index') }}">Cek pesanan</a> selama statusnya masih "{{ $label['pending'] }}". Setelah dijemput, pesanan tidak bisa dibatalkan sendiri.</p>
  </section>

  @if ($linkWa)
    <section aria-labelledby="judul-hubungi">
      <h2 id="judul-hubungi">Masih bingung?</h2>
      <p>Tanya langsung ke admin lewat WhatsApp.</p>
      <a class="tombol tombol-bantuan" href="{{ $linkWa }}" rel="noopener">Chat admin di WhatsApp</a>
    </section>
  @endif
</main>
@endsection
