@extends('layouts.app')

@section('title', 'Komplain pelanggan')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/admin.css') }}" />
@endpush

@php
  $aksi = session('aksi');
  $statusKomplain = \App\Models\Komplain::STATUS;
  $pilihan = [null => 'Semua'] + $statusKomplain;
@endphp

@section('content')
@if ($aksi && $aksi['jenis'] !== 'galat')
  @include('admin.notifikasi', ['aksi' => $aksi])
@endif

<main class="admin-page">
  <header class="admin-heading">
    <h1>Komplain pelanggan</h1>
    <div class="admin-count" aria-label="Komplain yang belum ditanggapi">
      <span>Belum ditanggapi</span>
      <strong>{{ $hitung['baru'] ?? 0 }}</strong>
    </div>
  </header>

  <section class="admin-panel" aria-labelledby="judul-komplain">
    <div class="panel-heading">
      <h2 id="judul-komplain">Daftar komplain</h2>
    </div>

    {{-- Saringan lewat tautan, bukan tombol: halaman ini sedikit isinya, dan alamatnya bisa disimpan atau dibagikan. --}}
    <nav class="alat-admin" aria-label="Saring komplain">
      <div class="saring">
        @foreach ($pilihan as $kunci => $nama)
          @php $aktif = ($saring ?? '') === (string) $kunci; @endphp
          <a class="saring-tombol" href="{{ $kunci ? route('admin.komplain.index', ['status' => $kunci]) : route('admin.komplain.index') }}"
             @if ($aktif) aria-current="true" @endif>
            {{ $nama }}<span>{{ $kunci ? ($hitung[$kunci] ?? 0) : $semua }}</span>
          </a>
        @endforeach
      </div>
    </nav>

    @if ($daftar->isEmpty())
      <p class="komplain-kosong">
        {{ $saring ? 'Tidak ada komplain dengan status ini.' : 'Belum ada komplain. Kalau pelanggan melapor, tampil di sini.' }}
      </p>
    @else
      <ul class="daftar-komplain">
        @foreach ($daftar as $k)
          @php
            $id = $k->id_komplain;
            $statusField = 'status_'.$id;
            $tanggapanField = 'tanggapan_'.$id;
          @endphp
          <li class="komplain-admin" id="komplain-{{ $id }}">
            <div class="komplain-admin-kepala">
              <div>
                <p class="komplain-admin-nama">{{ $k->pelanggan->nama ?? 'Pelanggan' }}</p>
                <p class="komplain-admin-meta">
                  {{ $k->pesanan->no_telepon ?? '' }} &middot; Pesanan #{{ $k->id_pesanan }}, {{ $k->pesanan->layanan ?? '' }}
                </p>
              </div>
              <span class="label-komplain" data-komplain="{{ $k->status }}">{{ $k->label_status }}</span>
            </div>

            <p class="komplain-admin-jenis">
              {{ $k->label_jenis }}
              <span>Dikirim {{ $k->created_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j F Y, H.i') }}</span>
            </p>
            <p class="komplain-admin-teks">{{ $k->deskripsi }}</p>

            @if ($k->pesanan)
              <p class="komplain-admin-tautan">
                <a class="tautan-struk" href="{{ route('admin.order.struk', $k->pesanan) }}" target="_blank" rel="noopener">Lihat struk<span class="sr-only"> pesanan #{{ $k->id_pesanan }} (buka di tab baru)</span></a>
              </p>
            @endif

            <form class="komplain-admin-form" method="post" action="{{ route('admin.komplain.update', $k) }}" data-kunci>
              @csrf
              @method('PUT')

              <div class="grup-form">
                <label for="tanggapan-{{ $id }}">Tanggapan untuk pelanggan</label>
                <textarea id="tanggapan-{{ $id }}" name="{{ $tanggapanField }}" rows="3" maxlength="1000"
                          placeholder="Contoh: Maaf ya, cucian dicuci ulang dan diantar lagi besok."
                          @if ($errors->has($tanggapanField)) aria-invalid="true" aria-describedby="tanggapan-galat-{{ $id }}" @endif>{{ old($tanggapanField, $k->tanggapan) }}</textarea>
                @error($tanggapanField)
                  <p class="galat" id="tanggapan-galat-{{ $id }}">{{ $message }}</p>
                @enderror
              </div>

              <div class="komplain-admin-aksi">
                <div class="komplain-admin-status">
                  <label for="status-komplain-{{ $id }}">Status</label>
                  <select id="status-komplain-{{ $id }}" name="{{ $statusField }}"
                          @if ($errors->has($statusField)) aria-invalid="true" @endif>
                    @foreach ($statusKomplain as $kunci => $nama)
                      <option value="{{ $kunci }}" @selected(old($statusField, $k->status) === $kunci)>{{ $nama }}</option>
                    @endforeach
                  </select>
                </div>
                <button type="submit" class="tombol-simpan" data-teks-proses="Menyimpan...">Simpan</button>
              </div>
              @error($statusField)
                <p class="galat">{{ $message }}</p>
              @enderror

              @if ($k->penanggap)
                <p class="komplain-admin-jejak">
                  Terakhir diubah oleh {{ $k->penanggap->name }}, {{ $k->updated_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j M Y, H.i') }}.
                </p>
              @endif
            </form>
          </li>
        @endforeach
      </ul>
    @endif
  </section>
</main>
@endsection

@push('scripts')
  {{-- ?v= berubah setiap file diubah, supaya browser tidak memakai salinan lama dari cache. --}}
  <script src="{{ asset('js/admin.js') }}?v={{ filemtime(public_path('js/admin.js')) }}" defer></script>
@endpush
