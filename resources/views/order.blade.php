@extends('layouts.app')

@section('title', 'Order Laundry')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/order.css') }}" />
@endpush

@php
  $alamatIsi = old('alamat', $alamatTerakhir ?? '');
  $alamatDariTerakhir = old('alamat') === null && filled($alamatTerakhir ?? null);
  $petunjukAlamat = 'alamat-petunjuk'.($errors->has('alamat') ? ' alamat-galat' : '');
@endphp

@section('content')
<main class="isi">

  <div class="order-box">
    <form id="form_order_laundry" action="{{ route('order.store') }}" method="post" data-kunci>
      @csrf
      <section class="form-order">
        @if ($errors->any())
          <div class="kabar kabar-galat" role="alert" tabindex="-1" data-fokus-galat>
            <p class="kabar-judul">Pesanan belum terkirim. Perbaiki yang berikut:</p>
            <ul>
              @foreach ($errors->messages() as $kolom => $daftarPesan)
                <li><a href="#{{ $kolom === 'layanan' ? $layanan[0]['id'] : $kolom }}">{{ $daftarPesan[0] }}</a></li>
              @endforeach
            </ul>
          </div>
        @endif

        <h1>Pilih layanan</h1>
        <p class="keterangan">Mau laundry kayak gimana bos.</p>
        <div class="bagian-layanan" role="radiogroup" aria-label="Pilih layanan"
             @if ($errors->has('layanan')) aria-describedby="layanan-galat" @endif>
          @foreach ($layanan as $item)
            <div class="jenis-layanan">
              <input
                type="radio"
                name="layanan"
                id="{{ $item['id'] }}"
                value="{{ $item['nama'] }}"
                data-harga="{{ $item['harga'] }}"
                required
                @checked(old('layanan') === $item['nama'])
                @if ($errors->has('layanan')) aria-invalid="true" @endif
              />
              <label for="{{ $item['id'] }}">
                <img class="ikon-layanan" src="{{ asset('img/'.$item['gambar']) }}" alt="" width="80" height="80" />
                <span class="info-layanan">
                  <span class="nama">{{ $item['nama'] }}</span>
                  <span class="deskripsi">{{ $item['deskripsi'] }}</span>
                  <span class="harga">Rp{{ number_format($item['harga'], 0, ',', '.') }} / {{ $item['satuan'] }}</span>
                </span>
              </label>
            </div>
          @endforeach
        </div>
        @error('layanan')
          <p class="galat" id="layanan-galat">{{ $message }}</p>
        @enderror

        <div class="bagian-form">
          <h2>Data pelanggan</h2>
          @auth('pelanggan')
            <p class="atas-nama">
              Atas nama <strong>{{ auth('pelanggan')->user()->nama }}</strong>,
              WhatsApp {{ auth('pelanggan')->user()->no_telepon }}.
            </p>
          @else
            <div class="grup-form">
              <label for="nama">Nama lengkap</label>
              <input type="text" id="nama" name="nama" value="{{ old('nama') }}" required autocomplete="name"
                     placeholder="Masukkan nama lengkap"
                     @error('nama') aria-invalid="true" aria-describedby="nama-galat" @enderror />
              @error('nama')
                <p class="galat" id="nama-galat">{{ $message }}</p>
              @enderror
            </div>
          @endauth
          <div class="baris-form">
            @guest('pelanggan')
              <div class="grup-form">
                <label for="telepon">Nomor WhatsApp</label>
                <input type="tel" id="telepon" name="telepon" value="{{ old('telepon') }}" required
                       inputmode="tel" autocomplete="tel" placeholder="08xxxxxxxxxx"
                       @error('telepon') aria-invalid="true" aria-describedby="telepon-galat" @enderror />
                @error('telepon')
                  <p class="galat" id="telepon-galat">{{ $message }}</p>
                @enderror
              </div>
            @endguest
            <div class="grup-form">
              <label for="tanggal">Tanggal jemput</label>
              <input type="date" id="tanggal" name="tanggal" value="{{ old('tanggal') }}" min="{{ now()->toDateString() }}" required
                     @error('tanggal') aria-invalid="true" aria-describedby="tanggal-galat" @enderror />
              @error('tanggal')
                <p class="galat" id="tanggal-galat">{{ $message }}</p>
              @enderror
            </div>
          </div>
          <div class="grup-form">
            <label for="alamat">Alamat jemput</label>
            <textarea id="alamat" name="alamat" rows="4" required autocomplete="street-address"
                      placeholder="Masukkan alamat lengkap"
                      aria-describedby="{{ $petunjukAlamat }}"
                      @error('alamat') aria-invalid="true" @enderror>{{ $alamatIsi }}</textarea>
            @error('alamat')
              <p class="galat" id="alamat-galat">{{ $message }}</p>
            @enderror
            <p class="petunjuk" id="alamat-petunjuk">
              @if ($alamatDariTerakhir)
                Diisi dari pesanan terakhir. Ubah kalau jemputnya di tempat lain.
              @endif
              Ongkir Rp{{ number_format($ongkir, 0, ',', '.') }}, gratis untuk alamat di {{ $areaGratis }}.
            </p>
          </div>
          <div class="grup-form">
            <label for="catatan">Catatan <span class="opsional">(boleh dikosongkan)</span></label>
            <textarea id="catatan" name="catatan" rows="3" placeholder="Contoh: Jangan dijemur langsung matahari yah.">{{ old('catatan') }}</textarea>
          </div>
        </div>

        <button type="submit" class="tombol tombol-utama tombol-blok" data-teks-proses="Mengirim pesanan...">Kirim pesanan</button>
      </section>
    </form>
  </div>
</main>
@endsection
