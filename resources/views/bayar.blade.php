@extends('layouts.app')

@section('title', 'Bayar pesanan #'.$pesanan->id_pesanan)

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/status.css') }}" />
  <link rel="stylesheet" href="{{ asset('css/bayar.css') }}" />
@endpush

@php
  $rp = fn (int $angka) => 'Rp'.number_format($angka, 0, ',', '.');
@endphp

@section('content')
<main class="bayar">
  <a class="kembali" href="{{ route('order.index') }}">Kembali ke Cek pesanan</a>
  <h1>Bayar pesanan #{{ $pesanan->id_pesanan }}</h1>

  <article class="pesanan">
    @if ($pesanan->status_bayar === 'lunas')
      <p class="kabar-kartu" role="status">
        <strong>Sudah lunas.</strong>
        @if ($pesanan->dibayar_pada)
          Tercatat {{ $pesanan->dibayar_pada->locale('id')->translatedFormat('j F Y, H:i') }}.
        @endif
      </p>
    @elseif ($pesanan->status_bayar === 'menunggu_verifikasi')
      <p class="kabar-kartu" role="status">
        <strong>Pembayaran sedang dicek admin.</strong> Statusnya jadi Lunas setelah cocok dengan mutasi QRIS.
      </p>
    @endif

    <h2 class="judul-kartu">{{ $pesanan->layanan }}</h2>

    <dl class="rincian">
      <dt>{{ $pesanan->label_jumlah }}</dt>
      <dd>{{ $pesanan->total_berat }} {{ $pesanan->satuan }}</dd>

      <dt>Cucian</dt>
      <dd>{{ $rp($pesanan->total_harga) }}</dd>

      <dt>Ongkir</dt>
      <dd>{{ $pesanan->ongkir > 0 ? $rp($pesanan->ongkir) : 'Gratis' }}</dd>

      <dt>Total bayar</dt>
      <dd class="total">{{ $rp($pesanan->total_bayar) }}</dd>
    </dl>

    @if ($galat)
      <div class="kabar kabar-galat bayar-galat" role="alert">
        <p class="kabar-judul">{{ $galat }}</p>
      </div>
    @elseif ($qr)
      <section class="qris" aria-labelledby="judul-qris">
        <h2 id="judul-qris">Scan dengan aplikasi pembayaran</h2>

        <div class="qris-kode" role="img" aria-label="Kode QRIS senilai {{ $rp($pesanan->total_bayar) }}">{!! $qr !!}</div>
        <p class="qris-nominal">{{ $rp($pesanan->total_bayar) }}</p>
        @if ($penerima)
          <p class="qris-penerima">Penerima: {{ $penerima }}</p>
        @endif

        <ol class="langkah">
          <li>Buka e-wallet atau mobile banking yang mendukung QRIS.</li>
          <li>Scan kode di atas. Nominal terisi otomatis, jangan diubah.</li>
          <li>Setelah berhasil, tekan "Saya sudah bayar" di bawah.</li>
        </ol>

        <form method="post" action="{{ route('order.bayar.lapor', $pesanan) }}" data-kunci>
          @csrf
          <button type="submit" class="tombol tombol-utama tombol-blok" data-teks-proses="Mengirim...">Saya sudah bayar</button>
        </form>
      </section>
    @endif
  </article>
</main>
@endsection
