@extends('layouts.app')

@section('title', 'Cek pesanan')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/status.css') }}?v={{ filemtime(public_path('css/status.css')) }}" />
  <link rel="stylesheet" href="{{ asset('css/struk.css') }}?v={{ filemtime(public_path('css/struk.css')) }}" />
@endpush

@push('scripts')
  <script src="{{ asset('js/struk.js') }}?v={{ filemtime(public_path('js/struk.js')) }}" defer></script>
@endpush

@php
  $label = \App\Models\Pesanan::LABEL_STATUS;
@endphp

@section('content')
<main class="status">
  <h1>Cek pesanan</h1>

  @if (session('akun_baru'))
    <div class="kabar" role="status">
      <p class="kabar-judul">Akun dibuat.</p>
      <p class="kabar-isi">
        {{ $pesanan->isEmpty() ? 'Pesanan berikutnya tampil di halaman ini.' : 'Pesanan dengan nomor ini tampil di bawah.' }}
      </p>
    </div>
  @endif

  @if (session('batal'))
    @php $batal = session('batal'); @endphp
    <div class="kabar {{ $batal['berhasil'] ? '' : 'kabar-galat' }}" role="{{ $batal['berhasil'] ? 'status' : 'alert' }}">
      <p class="kabar-judul">{{ $batal['judul'] }}</p>
      <p class="kabar-isi">{{ $batal['isi'] }}</p>
    </div>
  @endif

  @if (session('bayar'))
    @php $bayar = session('bayar'); @endphp
    <div class="kabar {{ $bayar['berhasil'] ? '' : 'kabar-galat' }}" role="{{ $bayar['berhasil'] ? 'status' : 'alert' }}">
      <p class="kabar-judul">{{ $bayar['judul'] }}</p>
      <p class="kabar-isi">{{ $bayar['isi'] }}</p>
    </div>
  @endif

  @if (session('terima'))
    @php $terima = session('terima'); @endphp
    <div class="kabar {{ $terima['berhasil'] ? '' : 'kabar-galat' }}" role="{{ $terima['berhasil'] ? 'status' : 'alert' }}">
      <p class="kabar-judul">{{ $terima['judul'] }}</p>
      <p class="kabar-isi">{{ $terima['isi'] }}</p>
    </div>
  @endif

  @if (session('komplain'))
    @php $komplain = session('komplain'); @endphp
    <div class="kabar {{ $komplain['berhasil'] ? '' : 'kabar-galat' }}" role="{{ $komplain['berhasil'] ? 'status' : 'alert' }}">
      <p class="kabar-judul">{{ $komplain['judul'] }}</p>
      <p class="kabar-isi">{{ $komplain['isi'] }}</p>
    </div>
  @endif

  @if ($pesanan->isEmpty())
    <p class="kosong">
      Belum pernah order.
      <a href="{{ route('order.create') }}">Buat pesanan baru</a>.
    </p>
  @else
    @php $p = $pesanan->first(); @endphp

    <h2>Pesanan terbaru</h2>
    <article class="pesanan">
      @if ((session('pesanan_baru.id') ?? null) == $p->id_pesanan)
        <p class="kabar-kartu" role="status"><strong>Pesanan sudah masuk.</strong></p>
      @endif
      <div class="kepala-pesanan">
        <div>
          <h3>{{ $p->layanan }}</h3>
          <p class="nomor">Pesanan #{{ $p->id_pesanan }}</p>
        </div>
        {{-- Struk baru bisa dilihat setelah pelanggan menekan "Cucian sudah diterima". --}}
        @if ($p->status === 'diterima')
          <a class="tautan-struk" href="{{ route('order.struk', $p) }}" data-struk data-judul="Struk pesanan #{{ $p->id_pesanan }}">Lihat struk</a>
        @endif
      </div>

      <ol class="tahap" aria-label="Tahap pesanan">
        @foreach (\App\Models\Pesanan::STATUS as $i => $s)
          <li class="{{ $i < $p->tahap ? 'lewat' : '' }} {{ $i === $p->tahap ? 'sekarang' : '' }}" style="--i: {{ $i }}"
              @if ($i === $p->tahap) aria-current="step" @endif>
            {{ $label[$s] }}@if ($i === $p->tahap)<span class="sr"> (tahap sekarang)</span>@endif
          </li>
        @endforeach
      </ol>
      <p class="penjelasan-status">{{ \App\Models\Pesanan::PENJELASAN_STATUS[$p->status] ?? '' }}</p>

      <dl class="rincian">
        <dt>Jadwal jemput</dt>
        <dd>{{ $p->tanggal_pickup->locale('id')->translatedFormat('l, j F Y') }}</dd>

        <dt>{{ $p->label_jumlah }}</dt>
        <dd>
          @if ($p->total_berat > 0)
            {{ $p->total_berat }} {{ $p->satuan }}
          @else
            <span class="belum">{{ $p->satuan === 'kg' ? 'Ditimbang' : 'Dihitung' }} saat dijemput</span>
          @endif
        </dd>

        <dt>Ongkir</dt>
        <dd>{{ $p->ongkir > 0 ? 'Rp'.number_format($p->ongkir, 0, ',', '.') : 'Gratis' }}</dd>

        <dt>Total harga</dt>
        <dd class="total">
          @if ($p->total_harga > 0)
            Rp{{ number_format($p->total_harga, 0, ',', '.') }}
          @else
            <span class="belum">Dihitung setelah cucian ditimbang</span>
          @endif
        </dd>

        <dt>Pembayaran</dt>
        <dd>
          @if ($p->total_harga > 0)
            {{ $p->label_bayar }}
          @else
            <span class="belum">Setelah total harga ada</span>
          @endif
        </dd>

        @if ($p->diantar_pada)
          <dt>Diantar</dt>
          <dd>{{ $p->diantar_pada->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j F Y, H.i') }}</dd>
        @endif

        @if ($p->diterima_pada)
          <dt>Diterima</dt>
          <dd>{{ $p->diterima_pada->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j F Y, H.i') }}</dd>
        @endif
      </dl>

      {{-- Komplain pesanan ini beserta tanggapan adminnya, tepat di atas tombol-tombol. --}}
      @foreach ($p->komplain->take(3) as $k)
        @include('partials.komplain-kartu', ['k' => $k])
      @endforeach

      {{-- Aksi sebaris, yang paling penting di kiri. Satu layar hanya punya satu tombol utama: konfirmasi
           terima kalau cucian sedang diantar, kalau tidak bayar. Ada masalah dan bayar (setelah diantar) bergaris. --}}
      @php $perluBayar = $p->bisaDibayar() && $p->status_bayar === 'belum_bayar'; @endphp
      @if ($p->bisaDiterima() || $p->bisaDikomplain() || $perluBayar)
        <div class="aksi-kartu">
          @if ($p->bisaDiterima())
            @include('partials.terima-pesanan', ['p' => $p, 'utama' => true])
          @endif

          @if ($p->bisaDikomplain())
            <a class="tombol-garis tombol-masalah" href="{{ route('order.komplain', $p) }}">Ada masalah</a>
          @endif

          @if ($perluBayar)
            <a class="{{ $p->bisaDiterima() ? 'tombol-garis' : 'tombol tombol-utama' }} tombol-bayar" href="{{ route('order.bayar', $p) }}">Bayar dengan QRIS</a>
          @endif
        </div>
      @endif

      @if ($p->bisaDibatalkan())
        @include('partials.batal-pesanan', ['p' => $p])
      @endif
    </article>

    @if ($pesanan->count() > 1)
      <h2>Pesanan lainnya</h2>
      <ul class="riwayat">
        @foreach ($pesanan->slice(1) as $lama)
          <li>
            <span>
              {{ $lama->layanan }}
              <small>Pesanan #{{ $lama->id_pesanan }}, jemput {{ $lama->tanggal_pickup->locale('id')->translatedFormat('j M Y') }}</small>
              <small class="tautan-baris">
                @if ($lama->status === 'diterima')
                  <a class="tautan-struk" href="{{ route('order.struk', $lama) }}" data-struk data-judul="Struk pesanan #{{ $lama->id_pesanan }}">Lihat struk</a>
                @endif
                @if ($lama->bisaDikomplain())
                  <a class="tautan-struk tautan-masalah" href="{{ route('order.komplain', $lama) }}">Ada masalah</a>
                @endif
              </small>
              @if ($lama->total_harga > 0)
                <small>Pembayaran: {{ $lama->label_bayar }}</small>
              @endif
            </span>
            <strong>{{ $label[$lama->status] ?? $lama->status }}</strong>
            @if ($lama->bisaDibayar() && $lama->status_bayar === 'belum_bayar')
              <a class="tombol-garis" href="{{ route('order.bayar', $lama) }}">Bayar dengan QRIS</a>
            @endif
            @if ($lama->bisaDiterima())
              @include('partials.terima-pesanan', ['p' => $lama, 'utama' => false])
            @endif
            @if ($lama->bisaDibatalkan())
              @include('partials.batal-pesanan', ['p' => $lama])
            @endif
            @if ($lama->komplain->first())
              @include('partials.komplain-kartu', ['k' => $lama->komplain->first()])
            @endif
          </li>
        @endforeach
      </ul>
    @endif
  @endif

  <dialog class="dialog" id="dialog-konfirmasi" aria-labelledby="dialog-konfirmasi-judul">
    <h2 id="dialog-konfirmasi-judul" data-dialog-judul>Batalkan pesanan?</h2>
    <p>Pesanan yang dibatalkan hilang dari daftar dan tidak bisa dikembalikan. Kalau mau laundry lagi, buat pesanan baru.</p>
    <p class="dialog-rincian" data-dialog-rincian></p>
    <div class="dialog-aksi">
      <button type="button" class="tombol-garis" data-dialog-batal autofocus>Kembali</button>
      <button type="button" class="tombol-bahaya-isi" data-dialog-ya data-teks-proses="Membatalkan...">Ya, batalkan pesanan</button>
    </div>
  </dialog>

  {{-- Popup struk. Isinya diambil dari halaman struk (route order.struk) oleh public/js/struk.js. Tanpa JavaScript
       tautan "Lihat struk" tetap membuka halaman struk penuh. --}}
  <dialog class="dialog dialog-struk" id="dialog-struk" aria-labelledby="dialog-struk-judul">
    <h2 class="sr-only" id="dialog-struk-judul" data-struk-judul>Struk pesanan</h2>
    <div class="struk-popup-isi" data-struk-isi aria-live="polite"></div>
    <div class="struk-popup-alat">
      <button type="button" class="tombol tombol-utama" data-struk-cetak hidden>Cetak struk</button>
      <button type="button" class="tombol-garis" data-struk-tutup autofocus>Tutup</button>
    </div>
  </dialog>
</main>
@endsection
