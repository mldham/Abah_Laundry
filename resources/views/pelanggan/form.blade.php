@extends('layouts.app')
@php($ubah = $pelanggan->exists)
@section('judul', $ubah ? 'Ubah Pelanggan' : 'Tambah Pelanggan')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/pelanggan.css') }}?v={{ filemtime(public_path('css/pelanggan.css')) }}" />
@endpush

@section('isi')
<main class="halaman-isi sempit">
  <div class="kepala">
    <h1>{{ $ubah ? 'Ubah Pelanggan' : 'Tambah Pelanggan' }}</h1>
  </div>

  <form class="form-pelanggan" method="post" data-kunci
        action="{{ $ubah ? route('pelanggan.update', $pelanggan) : route('pelanggan.store') }}">
    @csrf
    @if ($ubah)
      @method('PUT')
    @endif

    <div class="grup-form">
      <label for="nama">Nama lengkap</label>
      <input type="text" id="nama" name="nama" value="{{ old('nama', $pelanggan->nama) }}" autocomplete="off" required
             @error('nama') aria-invalid="true" aria-describedby="nama-galat" @enderror>
      @error('nama')
        <p class="galat" id="nama-galat" role="alert">{{ $message }}</p>
      @enderror
    </div>

    <div class="grup-form">
      <label for="no_telepon">No. telepon</label>
      <input type="tel" id="no_telepon" name="no_telepon" value="{{ old('no_telepon', $pelanggan->no_telepon) }}"
             inputmode="tel" placeholder="08xxxxxxxxxx" required
             @error('no_telepon') aria-invalid="true" aria-describedby="no_telepon-galat" @enderror>
      @error('no_telepon')
        <p class="galat" id="no_telepon-galat" role="alert">{{ $message }}</p>
      @enderror
    </div>

    <div class="grup-form">
      <label for="alamat">Alamat</label>
      <textarea id="alamat" name="alamat" rows="3" required
                @error('alamat') aria-invalid="true" aria-describedby="alamat-galat" @enderror>{{ old('alamat', $pelanggan->alamat) }}</textarea>
      @error('alamat')
        <p class="galat" id="alamat-galat" role="alert">{{ $message }}</p>
      @enderror
    </div>

    <div class="grup-form">
      <label for="catatan">Catatan penanganan khusus <span class="opsional">(opsional)</span></label>
      <textarea id="catatan" name="catatan" rows="3" maxlength="500"
                placeholder="Contoh: pakaian sutra jangan diperas"
                @error('catatan') aria-invalid="true" aria-describedby="catatan-galat" @enderror>{{ old('catatan', $pelanggan->catatan) }}</textarea>
      @error('catatan')
        <p class="galat" id="catatan-galat" role="alert">{{ $message }}</p>
      @enderror
    </div>

    <div class="aksi-form">
      <button type="submit" class="tombol tombol-utama" data-teks-proses="Menyimpan...">Simpan</button>
      <a class="tombol-garis" href="{{ route('pelanggan.index') }}">Batal</a>
    </div>
  </form>
</main>
@endsection
