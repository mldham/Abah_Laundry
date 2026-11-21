{{-- Tombol konfirmasi cucian sudah sampai, hanya untuk pesanan berstatus diantar. Bahan: $p (Pesanan), $utama (true di kartu pesanan terbaru, false di daftar pesanan lainnya). --}}
<form method="post" action="{{ route('order.terima', $p) }}" class="form-terima" data-kunci>
  @csrf
  <button type="submit" class="{{ ($utama ?? false) ? 'tombol tombol-utama' : 'tombol-garis' }}" data-teks-proses="Menyimpan...">Cucian sudah diterima</button>
</form>
