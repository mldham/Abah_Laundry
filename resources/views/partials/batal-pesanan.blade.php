{{-- Tombol batalkan pesanan, hanya untuk pesanan yang masih menunggu dijemput. Bahan: $p (Pesanan). Konfirmasi lewat <dialog> di data-order.blade.php (public/js/app.js). --}}
<form method="post" action="{{ route('order.batal', $p) }}" class="form-batal"
      data-konfirmasi
      data-judul="Batalkan pesanan #{{ $p->id_pesanan }}"
      data-rincian="{{ $p->layanan }}, jemput {{ $p->tanggal_pickup->locale('id')->translatedFormat('j F Y') }}">
  @csrf
  @method('DELETE')
  <button type="submit" class="tombol-garis tombol-bahaya">Batalkan pesanan</button>
</form>
