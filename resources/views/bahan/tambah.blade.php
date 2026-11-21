@extends('layouts.app')

@section('title', 'Tambah bahan pembersih - Laundry Abah')

@section('content')
<main class="kelola">
  <header class="admin-heading">
    <div>
      <h1>Tambah bahan pembersih</h1>
      <p class="subjudul">Masukkan stok bahan pembersih baru ke inventaris Abah Laundry.</p>
    </div>
  </header>

  <section class="panel panel-form admin-panel" aria-labelledby="judul-form">
    <h2 id="judul-form">Detail bahan baru</h2>

    <form method="POST" action="{{ route('bahan.store') }}" data-kunci>
      @csrf

      <div class="grup">
        <label for="nama_bahan">Nama bahan</label>
        <input type="text" id="nama_bahan" name="nama_bahan" value="{{ old('nama_bahan') }}"
               placeholder="Contoh: Deterjen cair / Pewangi melati" required>
      </div>

      <div class="baris-2">
        <div class="grup">
          <label for="kategori">Kategori</label>
          <select id="kategori" name="kategori" required>
            <option value="">Pilih kategori</option>
            @foreach (['Deterjen', 'Pewangi', 'Pelembut'] as $kategori)
              <option value="{{ $kategori }}" @selected(old('kategori') === $kategori)>{{ $kategori }}</option>
            @endforeach
          </select>
        </div>

        <div class="grup">
          <label for="merek">Merek</label>
          <input type="text" id="merek" name="merek" value="{{ old('merek') }}"
                 placeholder="Contoh: Rinso, Downy, Molto" required>
        </div>
      </div>

      <div class="baris-2">
        <div class="grup">
          <label for="stok">Jumlah stok</label>
          <input type="number" id="stok" name="stok" value="{{ old('stok') }}" inputmode="numeric"
                 placeholder="Contoh: 10" min="0" required>
        </div>

        <div class="grup">
          <label for="satuan">Satuan</label>
          <input type="text" id="satuan" name="satuan" value="{{ old('satuan') }}"
                 placeholder="Contoh: Liter, Kg, Botol" required>
        </div>
      </div>

      <div class="grup">
        <label for="id_supplier">Supplier</label>
        <select id="id_supplier" name="id_supplier" required>
          <option value="">Pilih supplier</option>
          @foreach ($supplier as $s)
            <option value="{{ $s->id_supplier }}" @selected((string) old('id_supplier') === (string) $s->id_supplier)>{{ $s->nama_supplier }}</option>
          @endforeach
        </select>
      </div>

      <div class="aksi-form">
        <button type="submit" class="tombol tombol-utama" data-teks-proses="Menyimpan...">Simpan data bahan</button>
        <a href="{{ route('bahan.index') }}" class="tombol-garis">Kembali</a>
      </div>
    </form>
  </section>
</main>
@endsection
