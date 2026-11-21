<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="noindex" />
    <title>Struk pesanan #{{ $pesanan->id_pesanan }} - Laundry Abah</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@400..700&display=swap" />
    <link rel="stylesheet" href="{{ asset('css/base.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/struk.css') }}?v={{ filemtime(public_path('css/struk.css')) }}" />
  </head>

  @php
    $rupiah = fn (int $angka) => 'Rp'.number_format($angka, 0, ',', '.');
    $waktu = fn ($tanggal) => $tanggal->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j F Y, H.i');
    $ada = $pesanan->total_harga > 0;
    $nomorToko = preg_replace('/\D+/', '', (string) config('layanan.kontak_whatsapp'));
    $toko = config('layanan.toko');
  @endphp

  <body class="halaman-struk">
    <main class="struk-halaman">
      <article class="struk" aria-labelledby="struk-judul">
        <header class="struk-kepala">
          <h1 id="struk-judul">Laundry Abah</h1>
          <p>Struk pemesanan</p>
          {{-- Tiga baris pendek, bukan satu kalimat panjang: di kertas 80mm satu kalimat akan patah di tengah nama desa. --}}
          <address class="struk-alamat">
            <span>{{ $toko['jalan'] }}</span>
            <span>Desa {{ $toko['desa'] }}, Kec. {{ $toko['kecamatan'] }}</span>
            <span>Kab. {{ $toko['kabupaten'] }}</span>
          </address>
          @if ($nomorToko !== '')
            <p>WhatsApp {{ config('layanan.kontak_whatsapp') }}</p>
          @endif
        </header>

        <dl class="struk-baris">
          <div><dt>No. pesanan</dt><dd>#{{ $pesanan->id_pesanan }}</dd></div>
          <div><dt>Jadwal jemput</dt><dd>{{ $pesanan->tanggal_pickup->locale('id')->translatedFormat('j F Y') }}</dd></div>
          <div><dt>Status</dt><dd>{{ \App\Models\Pesanan::LABEL_STATUS[$pesanan->status] ?? $pesanan->status }}</dd></div>
        </dl>

        <section class="struk-bagian" aria-label="Pelanggan">
          <p class="struk-nama">{{ $pesanan->pelanggan->nama ?? 'Pelanggan' }}</p>
          <p>{{ $pesanan->no_telepon }}</p>
          <p>{{ $pesanan->alamat }}</p>
          @if ($pesanan->notes)
            <p class="struk-catatan">Catatan: {{ $pesanan->notes }}</p>
          @endif
        </section>

        <section class="struk-bagian" aria-label="Rincian">
          <p class="struk-nama">{{ $pesanan->layanan }}</p>
          <dl class="struk-baris">
            @if ($pesanan->total_berat > 0)
              <div>
                <dt>{{ $pesanan->total_berat }} {{ $pesanan->satuan }} x {{ $rupiah((int) $pesanan->harga) }}</dt>
                <dd>{{ $rupiah((int) $pesanan->total_harga) }}</dd>
              </div>
            @else
              <div>
                <dt>{{ $rupiah((int) $pesanan->harga) }} / {{ $pesanan->satuan }}</dt>
                <dd class="struk-belum">{{ $pesanan->satuan === 'kg' ? 'Belum ditimbang' : 'Belum dihitung' }}</dd>
              </div>
            @endif
            <div>
              <dt>Ongkir</dt>
              <dd>{{ $pesanan->ongkir > 0 ? $rupiah((int) $pesanan->ongkir) : 'Gratis' }}</dd>
            </div>
          </dl>
        </section>

        <dl class="struk-baris struk-total">
          <div>
            <dt>Total</dt>
            <dd>
              @if ($ada)
                {{ $rupiah($pesanan->total_bayar) }}
              @else
                <span class="struk-belum">Dihitung setelah ditimbang</span>
              @endif
            </dd>
          </div>
        </dl>

        <dl class="struk-baris">
          <div><dt>Pembayaran</dt><dd>{{ $ada ? $pesanan->label_bayar : 'Setelah total ada' }}</dd></div>
          @if ($pesanan->status_bayar === 'lunas')
            @if ($pesanan->label_metode)
              <div><dt>Metode</dt><dd>{{ $pesanan->label_metode }}</dd></div>
            @endif
            @if ($pesanan->dibayar_pada)
              <div><dt>Dibayar</dt><dd>{{ $waktu($pesanan->dibayar_pada) }}</dd></div>
            @endif
          @endif
        </dl>

        <footer class="struk-kaki">
          <p>Terima kasih sudah memakai Laundry Abah.</p>
          <p>Simpan struk ini sebagai bukti pesanan.</p>
          <p class="struk-cetak">Dicetak {{ $waktu(now()) }}</p>
        </footer>
      </article>

      {{-- Di bawah struk, bukan di atasnya: yang dibaca dulu adalah isinya, lalu tombolnya. Tidak ikut tercetak. Tombol cetak baru tampil setelah struk.js jalan, karena tanpa JavaScript ia tidak bisa berbuat apa-apa. --}}
      <div class="struk-alat">
        <button type="button" class="tombol tombol-utama" data-cetak hidden>Cetak struk</button>
        <a class="tombol-garis" href="{{ $kembali }}">Kembali<span class="sr-only"> {{ $kembaliTeks }}</span></a>
      </div>
    </main>

    <script src="{{ asset('js/struk.js') }}?v={{ filemtime(public_path('js/struk.js')) }}" defer></script>
  </body>
</html>
