{{-- Pita hijau di atas halaman admin untuk aksi yang selesai (Tersimpan, Tidak ada perubahan, Dihapus).
     Bahan: $aksi (isi flash 'aksi'). Hilang sendiri setelah 5 detik lewat public/js/admin.js.
     Kegagalan tidak lewat sini: pesan gagal harus tetap ada di baris yang salah sampai diperbaiki. --}}
<div class="notifikasi" data-notifikasi role="status">
  <div class="notifikasi-teks">
    <p class="notifikasi-judul">{{ $aksi['judul'] }}</p>
    @isset($aksi['isi'])
      <p class="notifikasi-isi">{{ $aksi['isi'] }}</p>
    @endisset
  </div>
  <button type="button" class="notifikasi-tutup" data-notifikasi-tutup>Tutup</button>
  <span class="notifikasi-garis" aria-hidden="true"></span>
</div>
