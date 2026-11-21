@extends('layouts.app')

@section('title', 'Edit bahan pembersih - Laundry Abah')

@section('content')
<main class="kelola">
  <header class="admin-heading">
    <div>
      <h1>Edit bahan pembersih</h1>
      <p class="subjudul">Perbarui informasi stok atau supplier untuk {{ $bahan->nama_bahan }}.</p>
    </div>
  </header>

  <section class="panel panel-form admin-panel" aria-labelledby="judul-form">
    <h2 id="judul-form">Form pembaruan data</h2>

    <form method="POST" action="{{ route('bahan.update', $bahan->id_bahan) }}" data-kunci>
      @csrf
      @method('PUT')

      <div class="grup">
        <label for="nama_bahan">Nama barang</label>
        <input type="text" id="nama_bahan" name="nama_bahan" value="{{ old('nama_bahan', $bahan->nama_bahan) }}" required>
      </div>

      <div class="baris-2">
        <div class="grup">
          <label for="kategori">Kategori</label>
          <select id="kategori" name="kategori" required>
            @foreach (['Deterjen', 'Pewangi', 'Pelembut'] as $kategori)
              <option value="{{ $kategori }}" @selected(old('kategori', $bahan->kategori) === $kategori)>{{ $kategori }}</option>
            @endforeach
          </select>
        </div>

        <div class="grup">
          <label for="merek">Merek</label>
          <input type="text" id="merek" name="merek" value="{{ old('merek', $bahan->merek) }}" required>
        </div>
      </div>

      <div class="baris-2">
        <div class="grup">
          <label for="stok">Jumlah stok</label>
          <input type="number" id="stok" name="stok" value="{{ old('stok', $bahan->stok) }}" inputmode="numeric" min="0" required>
        </div>

        <div class="grup">
          <label for="satuan">Satuan</label>
          <input type="text" id="satuan" name="satuan" value="{{ old('satuan', $bahan->satuan) }}" required>
        </div>
      </div>

      <div class="grup">
        <label for="id_supplier">Supplier</label>
        <select id="id_supplier" name="id_supplier" required>
          @foreach ($supplier as $s)
            <option value="{{ $s->id_supplier }}" @selected((string) old('id_supplier', $bahan->id_supplier) === (string) $s->id_supplier)>{{ $s->nama_supplier }}</option>
          @endforeach
        </select>
      </div>

      <div class="aksi-form">
        <button type="submit" class="tombol tombol-utama" data-teks-proses="Menyimpan...">Perbarui data</button>
        <a href="{{ route('bahan.index') }}" class="tombol-garis">Kembali</a>
      </div>
    </form>
  </section>
</main>
@endsection
