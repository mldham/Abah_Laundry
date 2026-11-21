@extends('layouts.app')

@section('title', 'Komplain pesanan #'.$pesanan->id_pesanan)

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/komplain.css') }}" />
@endpush

@php
  $jenis = \App\Models\Komplain::JENIS;
  $keterangan = \App\Models\Komplain::KETERANGAN_JENIS;
@endphp

@section('content')
<main class="komplain-halaman">
  <a class="kembali" href="{{ route('order.index') }}">Kembali ke Cek pesanan</a>
  <h1>Ada masalah dengan pesanan #{{ $pesanan->id_pesanan }}?</h1>
  <p class="keterangan">{{ $pesanan->layanan }}. Ceritakan apa yang terjadi, admin akan menanggapi di halaman Cek pesanan.</p>

  <form method="post" action="{{ route('order.komplain.kirim', $pesanan) }}" novalidate data-kunci>
    @csrf

    @if ($errors->any())
      <div class="kabar kabar-galat" role="alert" tabindex="-1" data-fokus-galat>
        <p class="kabar-judul">Komplain belum terkirim.</p>
        <ul>
          @foreach ($errors->all() as $pesan)
            <li>{{ $pesan }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <fieldset class="pilihan-masalah" @if ($errors->has('jenis')) aria-describedby="jenis-galat" @endif>
      <legend>Masalahnya apa?</legend>

      @foreach ($jenis as $kunci => $nama)
        <div class="masalah">
          <input type="radio" name="jenis" id="jenis-{{ $kunci }}" value="{{ $kunci }}" required
                 @checked(old('jenis') === $kunci)
                 @if ($errors->has('jenis')) aria-invalid="true" @endif />
          <label for="jenis-{{ $kunci }}">
            <span class="masalah-nama">{{ $nama }}</span>
            <span class="masalah-keterangan">{{ $keterangan[$kunci] }}</span>
          </label>
        </div>
      @endforeach

      @error('jenis')
        <p class="galat" id="jenis-galat">{{ $message }}</p>
      @enderror
    </fieldset>

    <div class="grup-form">
      <label for="deskripsi">Ceritakan lebih lengkap</label>
      <textarea id="deskripsi" name="deskripsi" rows="5" required maxlength="1000"
                placeholder="Contoh: Dua kaos putih masih ada noda di bagian kerah."
                aria-describedby="deskripsi-petunjuk @error('deskripsi') deskripsi-galat @enderror"
                @error('deskripsi') aria-invalid="true" @enderror>{{ old('deskripsi') }}</textarea>
      @error('deskripsi')
        <p class="galat" id="deskripsi-galat">{{ $message }}</p>
      @enderror
      <p class="petunjuk" id="deskripsi-petunjuk">Sebut barangnya dan jumlahnya kalau ada yang hilang atau rusak.</p>
    </div>

    <button type="submit" class="tombol tombol-utama tombol-blok" data-teks-proses="Mengirim...">Kirim komplain</button>
  </form>
</main>
@endsection
