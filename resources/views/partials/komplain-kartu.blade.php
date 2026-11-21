{{-- Satu komplain beserta tanggapan adminnya, di kartu pesanan pelanggan. Bahan: $k (Komplain). --}}
<section class="komplain-kartu" aria-label="Komplain: {{ $k->label_jenis }}">
  <div class="komplain-kepala">
    <div>
      <p class="komplain-jenis">{{ $k->label_jenis }}</p>
      <p class="komplain-waktu">Dikirim {{ $k->created_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j F Y, H.i') }}</p>
    </div>
    <span class="label-komplain" data-komplain="{{ $k->status }}">{{ $k->label_status }}</span>
  </div>

  <p class="komplain-teks">{{ $k->deskripsi }}</p>

  @if ($k->tanggapan)
    <p class="komplain-tanggapan"><span>Tanggapan admin</span>{{ $k->tanggapan }}</p>
  @elseif ($k->terbuka())
    <p class="komplain-tunggu">Belum ada tanggapan. Jawaban admin tampil di sini.</p>
  @endif
</section>
