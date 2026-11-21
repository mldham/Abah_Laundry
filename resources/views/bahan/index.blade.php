@extends('layouts.app')

@section('title', 'Kelola Stock - Laundry Abah')

@section('content')
<div class="kelola">
    <div class="admin-heading">
        <div>
            <h1>Manajemen stok bahan</h1>
            <p class="subjudul">Pantau dan kelola inventaris deterjen, pewangi, dan pelembut Abah Laundry.</p>
        </div>

        <div class="ringkasan">
            <div class="ringkasan-label">Total jenis bahan</div>
            <div class="ringkasan-nilai">{{ $bahan->count() }}</div>
        </div>
    </div>

    <div class="panel">
        <h2>Daftar bahan pembersih</h2>

        <div class="toolbar">
            <div class="filter" role="group" aria-label="Filter kategori bahan">
                <button type="button" class="pil" data-kategori="semua" aria-pressed="true">
                    Semua <span class="jumlah">{{ $bahan->count() }}</span>
                </button>
                <button type="button" class="pil" data-kategori="Deterjen" aria-pressed="false">
                    Deterjen <span class="jumlah">{{ $bahan->where('kategori', 'Deterjen')->count() }}</span>
                </button>
                <button type="button" class="pil" data-kategori="Pewangi" aria-pressed="false">
                    Pewangi <span class="jumlah">{{ $bahan->where('kategori', 'Pewangi')->count() }}</span>
                </button>
                <button type="button" class="pil" data-kategori="Pelembut" aria-pressed="false">
                    Pelembut <span class="jumlah">{{ $bahan->where('kategori', 'Pelembut')->count() }}</span>
                </button>
            </div>

            <div class="toolbar-kanan">
                <input type="search" id="cari-bahan" class="cari" placeholder="Cari nama bahan, merek, atau supplier." aria-label="Cari bahan pembersih" />
                <a href="{{ route('bahan.create') }}" class="tombol tombol-utama">+ Tambah bahan</a>
            </div>
        </div>

        <div class="tabel-bungkus">
            <table id="tabel-bahan">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama barang</th>
                        <th>Kategori</th>
                        <th>Merek</th>
                        <th>Stok</th>
                        <th>Supplier</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bahan as $index => $item)
                        <tr data-kategori="{{ $item->kategori }}" data-cari="{{ strtolower($item->nama_bahan . ' ' . $item->merek . ' ' . ($item->supplier->nama_supplier ?? '')) }}">
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="nama-barang">{{ $item->nama_bahan }}</div>
                                <div class="kode">#{{ $item->id_bahan }}</div>
                            </td>
                            <td>
                                <span class="label-kategori">{{ $item->kategori }}</span>
                            </td>
                            <td>{{ $item->merek ?? '-' }}</td>
                            <td>
                                <span class="stok {{ $item->stok <= 5 ? 'stok-menipis' : '' }}">
                                    {{ $item->stok }} {{ $item->satuan }}
                                </span>
                                @if($item->stok <= 5)
                                    <span class="label-menipis">Menipis</span>
                                @endif
                            </td>
                            <td>{{ $item->supplier->nama_supplier ?? '-' }}</td>
                            <td class="aksi">
                                <a href="{{ route('bahan.edit', $item->id_bahan) }}" class="tombol-garis">Edit</a>

                                @if((auth('admin')->user()->peran ?? auth('admin')->user()->role ?? '') === 'admin')
                                    <form action="{{ route('bahan.destroy', $item->id_bahan) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus bahan {{ $item->nama_bahan }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="tombol-bahaya">Hapus</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="kosong">Belum ada data bahan pembersih. Klik "+ Tambah bahan" untuk menambahkan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tombolFilter = document.querySelectorAll('.filter .pil');
    const inputCari = document.getElementById('cari-bahan');
    const barisTabel = document.querySelectorAll('#tabel-bahan tbody tr[data-kategori]');

    let kategoriAktif = 'semua';

    function terapkanFilter() {
        const kataKunci = (inputCari ? inputCari.value : '').trim().toLowerCase();

        barisTabel.forEach(function (baris) {
            const kategoriBaris = baris.getAttribute('data-kategori');
            const teksCari = baris.getAttribute('data-cari') || '';

            const cocokKategori = kategoriAktif === 'semua' || kategoriBaris === kategoriAktif;
            const cocokCari = kataKunci === '' || teksCari.includes(kataKunci);

            baris.style.display = (cocokKategori && cocokCari) ? '' : 'none';
        });
    }

    tombolFilter.forEach(function (tombol) {
        tombol.addEventListener('click', function () {
            tombolFilter.forEach(t => t.setAttribute('aria-pressed', 'false'));
            this.setAttribute('aria-pressed', 'true');
            kategoriAktif = this.getAttribute('data-kategori');
            terapkanFilter();
        });
    });

    if (inputCari) {
        inputCari.addEventListener('input', terapkanFilter);
    }
});
</script>
@endpush