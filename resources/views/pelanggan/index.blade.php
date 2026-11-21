@extends('layouts.app')

@section('judul', 'Data Pelanggan')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/pelanggan.css') }}?v={{ filemtime(public_path('css/pelanggan.css')) }}" />
@endpush

@section('isi')
@php($isAdmin = auth('admin')->user()?->peran === 'admin')

<main class="halaman-isi">
  <div class="kepala">
    <div>
      <h1>Data Pelanggan</h1>
      <p class="pengantar">Kelola biodata, nomor telepon, dan catatan penanganan khusus pakaian.</p>
    </div>
    <a class="tombol tombol-utama" href="{{ route('pelanggan.create') }}">Tambah pelanggan</a>
  </div>

  @if (session('sukses'))
    <div class="kabar" role="status">
      <p class="kabar-judul">{{ session('sukses') }}</p>
    </div>
  @endif

  @if ($isAdmin && $konfirmasi)
    <div class="kabar kabar-galat kabar-hapus" role="alert">
      <form method="post" action="{{ route('pelanggan.destroy', $konfirmasi) }}" data-kunci>
        @csrf
        @method('DELETE')
        <p class="kabar-judul">Hapus pelanggan {{ $konfirmasi->nama }}?</p>
        <p class="kabar-isi">Data tidak bisa dikembalikan.</p>
        <div class="dialog-aksi">
          <button type="submit" class="tombol-bahaya-isi" data-teks-proses="Menghapus...">Ya, hapus</button>
          <a class="tombol-garis" href="{{ route('pelanggan.index') }}">Batal</a>
        </div>
      </form>
    </div>
  @endif

  <form method="get" action="{{ route('pelanggan.index') }}" class="cari" role="search">
    <label for="cari" class="sr">Cari pelanggan</label>
    <input type="search" id="cari" name="cari" value="{{ $cari }}" placeholder="Cari nama atau no. telepon">
    <button type="submit" class="tombol">Cari</button>
    @if ($cari != '')
      <a class="tombol-garis" href="{{ route('pelanggan.index') }}">Reset</a>
    @endif
  </form>

  <div class="tabel-bungkus">
    <table class="tabel-pelanggan">
      <thead>
        <tr>
          <th scope="col">Kode</th>
          <th scope="col">Nama</th>
          <th scope="col">No. telepon</th>
          <th scope="col">Alamat</th>
          <th scope="col">Catatan penanganan</th>
          <th scope="col">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($daftar as $p)
          <tr>
            <td class="kode">PLG{{ str_pad($p->id_pelanggan, 4, '0', STR_PAD_LEFT) }}</td>
            <td class="nama">{{ $p->nama }}</td>
            <td class="telepon">{{ $p->no_telepon }}</td>
            <td>{{ $p->alamat }}</td>
            <td>{{ $p->catatan != '' ? $p->catatan : '-' }}</td>
            <td>
              <div class="aksi">
                <a class="tombol-garis tombol-kecil" href="{{ route('pelanggan.edit', $p) }}">Ubah</a>
                @if ($isAdmin)
                  <a class="tombol-garis tombol-bahaya tombol-kecil" href="{{ route('pelanggan.index', ['hapus' => $p->id_pelanggan]) }}">Hapus</a>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="kosong">Belum ada data pelanggan.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</main>
@endsection