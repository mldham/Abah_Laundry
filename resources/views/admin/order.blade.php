@extends('layouts.app')

@section('title', 'Admin Laundry Abah')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}" />
@endpush

@php
  $label = \App\Models\Pesanan::LABEL_STATUS;
  $aksi = session('aksi');
  $tampilNotifikasi = $aksi && $aksi['jenis'] !== 'galat';
  $jumlahStatus = $pesanan->countBy('status');
@endphp

@section('content')
@if ($tampilNotifikasi)
  @include('admin.notifikasi', ['aksi' => $aksi])
@endif

<main class="admin-page">
  <header class="admin-heading">
    <h1>Manajemen pesanan</h1>
    <div class="admin-count" aria-label="Jumlah pesanan saat ini">
      <span>Total pesanan</span>
      <strong>{{ $pesanan->count() }}</strong>
    </div>
  </header>

  <section class="admin-panel" aria-labelledby="judul-daftar-pesanan">
    <div class="panel-heading">
      <h2 id="judul-daftar-pesanan" tabindex="-1">Daftar pesanan</h2>
    </div>

    {{-- Saring dan cari berjalan di browser (public/js/admin.js). Baris dengan pesan gagal tidak pernah disembunyikan. --}}
    @if ($pesanan->isNotEmpty())
      <div class="alat-admin">
        <div class="saring" role="group" aria-label="Saring menurut status">
          <button type="button" class="saring-tombol" data-saring="semua" aria-pressed="true">
            Semua <span>{{ $pesanan->count() }}</span>
          </button>
          @foreach (\App\Models\Pesanan::STATUS as $status)
            <button type="button" class="saring-tombol" data-saring="{{ $status }}" aria-pressed="false">
              {{ $label[$status] }} <span>{{ $jumlahStatus[$status] ?? 0 }}</span>
            </button>
          @endforeach
        </div>
        <div class="cari-pesanan">
          <label class="sr-only" for="cari-pesanan">Cari pesanan</label>
          <input type="search" id="cari-pesanan" placeholder="Cari nama, nomor telepon, atau #pesanan" autocomplete="off" />
        </div>
      </div>
      <div class="hasil-saring" data-hasil-saring role="status" hidden>
        <p data-hasil-saring-teks></p>
        <button type="button" class="tombol-garis" data-atur-ulang>Tampilkan semua</button>
      </div>
    @endif

    <div class="tabel-scroll">
      <table class="tabel-admin">
        <thead>
          <tr>
            <th scope="col">Pesanan</th>
            <th scope="col">Layanan</th>
            <th scope="col">Berat atau jumlah</th>
            <th scope="col">Total harga</th>
            <th scope="col">Status</th>
            <th scope="col">Pembayaran <span class="ket-kolom">dengan ongkir</span></th>
            <th scope="col">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($pesanan as $p)
            @php
              $infoLayanan = collect(config('layanan.daftar'))->firstWhere('nama', $p->layanan);
              $satuan = $infoLayanan['satuan'] ?? 'unit';
              $hargaSatuan = (int) ($p->harga ?: ($infoLayanan['harga'] ?? 0));
              $formId = 'form-pesanan-'.$p->id_pesanan;
              $beratField = 'total_berat_'.$p->id_pesanan;
              $statusField = 'status_'.$p->id_pesanan;
              $beratSekarang = old($beratField, $p->total_berat);
              $barisIni = $aksi && ($aksi['id'] ?? null) == $p->id_pesanan;
              $galatBaris = $barisIni ? collect($errors->get($beratField))->merge($errors->get($statusField))->merge($errors->get('status_bayar_'.$p->id_pesanan)) : collect();
            @endphp

            <tr id="pesanan-{{ $p->id_pesanan }}"
                data-tarif="{{ $hargaSatuan }}"
                data-status="{{ $p->status }}"
                data-cari="{{ mb_strtolower($p->pelanggan->nama.' '.$p->no_telepon.' #'.$p->id_pesanan.' '.$p->layanan) }}"
                @class(['baris-baru' => $barisIni && $galatBaris->isEmpty(), 'baris-galat' => $barisIni && $galatBaris->isNotEmpty()])>
              <td>
                <div class="identitas-pesanan">
                  <strong>{{ $p->pelanggan->nama }}</strong>
                  <span>#{{ $p->id_pesanan }}, {{ $p->no_telepon }}</span>
                  @php $komplainTerbuka = $p->komplain->where('status', '!=', 'selesai'); @endphp
                  @if ($komplainTerbuka->isNotEmpty())
                    <a class="tautan-komplain" href="{{ route('admin.komplain.index') }}#komplain-{{ $komplainTerbuka->first()->id_komplain }}">Komplain {{ $komplainTerbuka->contains('status', 'baru') ? 'baru' : 'ditangani' }}@if ($komplainTerbuka->count() > 1) ({{ $komplainTerbuka->count() }})@endif</a>
                  @endif
                  <a class="tautan-struk" href="{{ route('admin.order.struk', $p) }}" target="_blank" rel="noopener">Cetak struk<span class="sr-only"> pesanan #{{ $p->id_pesanan }} (buka di tab baru)</span></a>
                </div>
              </td>
              <td>
                <div class="info-layanan">
                  <strong>{{ $p->layanan }}</strong>
                  <span>Rp{{ number_format($hargaSatuan, 0, ',', '.') }} / {{ $satuan }}</span>
                </div>
              </td>
              <td>
                <label class="sr-only" for="berat-{{ $p->id_pesanan }}">
                  {{ $satuan === 'kg' ? 'Berat pesanan dalam kilogram' : 'Jumlah pesanan dalam '.$satuan }} #{{ $p->id_pesanan }}
                </label>
                <div class="sel-berat">
                  <div class="input-berat">
                    <input
                      id="berat-{{ $p->id_pesanan }}"
                      type="number"
                      name="{{ $beratField }}"
                      form="{{ $formId }}"
                      value="{{ $beratSekarang }}"
                      min="0"
                      max="100000"
                      step="1"
                      inputmode="numeric"
                      data-berat
                      @if ($errors->has($beratField)) aria-invalid="true" @endif
                    />
                    <span>{{ $satuan }}</span>
                  </div>
                  @if ((int) $p->total_berat === 0)
                    <span class="belum-ditimbang">Belum ditimbang</span>
                  @endif
                </div>
              </td>
              <td>
                <div class="harga-hasil" aria-live="polite">
                  <strong data-total>Rp{{ number_format((int) $beratSekarang * $hargaSatuan, 0, ',', '.') }}</strong>
                </div>
              </td>
              <td>
                <label class="sr-only" for="status-{{ $p->id_pesanan }}">Status pesanan #{{ $p->id_pesanan }}</label>
                <div class="sel-status">
                  <select id="status-{{ $p->id_pesanan }}" name="{{ $statusField }}" form="{{ $formId }}"
                          @if ($errors->has($statusField)) aria-invalid="true" @endif>
                    @foreach ($p->statusUntukAdmin() as $status)
                      <option value="{{ $status }}" @selected(old($statusField, $p->status) === $status)>{{ $label[$status] }}</option>
                    @endforeach
                  </select>
                  {{-- Diterima hanya bisa datang dari pelanggan, jadi admin perlu tahu kapan dan dari siapa. --}}
                  @if ($p->status === 'diterima' && $p->diterima_pada)
                    <span class="ket-status">Dikonfirmasi pelanggan, {{ $p->diterima_pada->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j M, H.i') }}</span>
                  @elseif ($p->status === 'diantar' && $p->diantar_pada)
                    <span class="ket-status">Berangkat {{ $p->diantar_pada->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j M, H.i') }}</span>
                  @endif
                </div>
              </td>
              <td>
                <div class="sel-bayar">
                  @if ($p->total_harga > 0)
                    <div class="info-bayar">
                      <span class="label-bayar" data-bayar="{{ $p->status_bayar }}">{{ $p->label_bayar }}</span>
                      <span class="nominal-bayar">Rp{{ number_format($p->total_bayar, 0, ',', '.') }}</span>
                    </div>
                    <div class="aksi-bayar">
                      @if ($p->status_bayar !== 'lunas')
                        <form action="{{ route('admin.order.bayar', $p) }}" method="POST">
                          @csrf
                          @method('PUT')
                          <input type="hidden" name="status_bayar" value="lunas" />
                          <button type="submit" class="tombol-bayar-admin utama" data-teks-proses="Menyimpan...">Tandai lunas</button>
                        </form>
                      @endif
                      @if ($p->status_bayar !== 'belum_bayar')
                        <form action="{{ route('admin.order.bayar', $p) }}" method="POST">
                          @csrf
                          @method('PUT')
                          <input type="hidden" name="status_bayar" value="belum_bayar" />
                          <button type="submit" @class(['tombol-bayar-admin', 'bahaya' => $p->status_bayar === 'lunas']) data-teks-proses="Menyimpan...">{{ $p->status_bayar === 'lunas' ? 'Batalkan lunas' : 'Belum masuk' }}</button>
                        </form>
                      @endif
                    </div>
                  @else
                    <span class="belum-ditimbang">Belum ada total</span>
                  @endif
                </div>
              </td>
              <td>
                <div class="aksi-pesanan">
                  <form id="{{ $formId }}" action="{{ route('admin.order.update', $p) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="tombol-simpan" data-teks-proses="Menyimpan...">Simpan</button>
                  </form>
                  @can('delete', $p)
                    <form
                      action="{{ route('admin.order.destroy', $p) }}"
                      method="POST"
                      data-hapus
                      data-judul="Hapus pesanan #{{ $p->id_pesanan }}"
                      data-rincian="{{ $p->pelanggan->nama }}, {{ $p->layanan }}"
                    >
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="tombol-hapus">Hapus</button>
                    </form>
                  @endcan
                </div>
              </td>
            </tr>

            @if ($barisIni && $galatBaris->isNotEmpty())
              @include('admin.hasil', ['aksi' => $aksi, 'galat' => $galatBaris])
            @endif
          @empty
            <tr>
              <td colspan="7">
                <div class="kosong-admin">
                  <strong>Belum ada pesanan</strong>
                  <p>Pesanan pelanggan akan muncul di halaman ini setelah mereka melakukan order.</p>
                  <a href="{{ route('order.create') }}">Lihat halaman order</a>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>

  <dialog class="dialog-hapus" id="dialog-hapus" aria-labelledby="dialog-hapus-judul">
    <h2 id="dialog-hapus-judul" data-dialog-judul>Hapus pesanan?</h2>
    <p>Pesanan yang dihapus tidak bisa dikembalikan.</p>
    <p class="dialog-rincian" data-dialog-rincian></p>
    <div class="dialog-aksi">
      <button type="button" class="tombol-batal" data-dialog-batal autofocus>Batal</button>
      <button type="button" class="tombol-hapus tombol-hapus-final" data-dialog-hapus data-teks-proses="Menghapus...">Hapus pesanan</button>
    </div>
  </dialog>
</main>
@endsection

@push('scripts')
  {{-- ?v= berubah setiap file diubah, supaya browser tidak memakai salinan lama dari cache. --}}
  <script src="{{ asset('js/admin.js') }}?v={{ filemtime(public_path('js/admin.js')) }}" defer></script>
@endpush
