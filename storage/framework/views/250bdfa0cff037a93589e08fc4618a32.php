<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="noindex" />
    <title>Struk pesanan #<?php echo e($pesanan->id_pesanan); ?> - Laundry Abah</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@400..700&display=swap" />
    <link rel="stylesheet" href="<?php echo e(asset('css/base.css')); ?>" />
    <link rel="stylesheet" href="<?php echo e(asset('css/struk.css')); ?>?v=<?php echo e(filemtime(public_path('css/struk.css'))); ?>" />
  </head>

  <?php
    $rupiah = fn (int $angka) => 'Rp'.number_format($angka, 0, ',', '.');
    $waktu = fn ($tanggal) => $tanggal->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j F Y, H.i');
    $ada = $pesanan->total_harga > 0;
    $nomorToko = preg_replace('/\D+/', '', (string) config('layanan.kontak_whatsapp'));
    $toko = config('layanan.toko');
  ?>

  <body class="halaman-struk">
    <main class="struk-halaman">
      <article class="struk" aria-labelledby="struk-judul">
        <header class="struk-kepala">
          <h1 id="struk-judul">Laundry Abah</h1>
          <p>Struk pemesanan</p>
          
          <address class="struk-alamat">
            <span><?php echo e($toko['jalan']); ?></span>
            <span>Desa <?php echo e($toko['desa']); ?>, Kec. <?php echo e($toko['kecamatan']); ?></span>
            <span>Kab. <?php echo e($toko['kabupaten']); ?></span>
          </address>
          <?php if($nomorToko !== ''): ?>
            <p>WhatsApp <?php echo e(config('layanan.kontak_whatsapp')); ?></p>
          <?php endif; ?>
        </header>

        <dl class="struk-baris">
          <div><dt>No. pesanan</dt><dd>#<?php echo e($pesanan->id_pesanan); ?></dd></div>
          <div><dt>Jadwal jemput</dt><dd><?php echo e($pesanan->tanggal_pickup->locale('id')->translatedFormat('j F Y')); ?></dd></div>
          <div><dt>Status</dt><dd><?php echo e(\App\Models\Pesanan::LABEL_STATUS[$pesanan->status] ?? $pesanan->status); ?></dd></div>
        </dl>

        <section class="struk-bagian" aria-label="Pelanggan">
          <p class="struk-nama"><?php echo e($pesanan->pelanggan->nama ?? 'Pelanggan'); ?></p>
          <p><?php echo e($pesanan->no_telepon); ?></p>
          <p><?php echo e($pesanan->alamat); ?></p>
          <?php if($pesanan->notes): ?>
            <p class="struk-catatan">Catatan: <?php echo e($pesanan->notes); ?></p>
          <?php endif; ?>
        </section>

        <section class="struk-bagian" aria-label="Rincian">
          <p class="struk-nama"><?php echo e($pesanan->layanan); ?></p>
          <dl class="struk-baris">
            <?php if($pesanan->total_berat > 0): ?>
              <div>
                <dt><?php echo e($pesanan->total_berat); ?> <?php echo e($pesanan->satuan); ?> x <?php echo e($rupiah((int) $pesanan->harga)); ?></dt>
                <dd><?php echo e($rupiah((int) $pesanan->total_harga)); ?></dd>
              </div>
            <?php else: ?>
              <div>
                <dt><?php echo e($rupiah((int) $pesanan->harga)); ?> / <?php echo e($pesanan->satuan); ?></dt>
                <dd class="struk-belum"><?php echo e($pesanan->satuan === 'kg' ? 'Belum ditimbang' : 'Belum dihitung'); ?></dd>
              </div>
            <?php endif; ?>
            <div>
              <dt>Ongkir</dt>
              <dd><?php echo e($pesanan->ongkir > 0 ? $rupiah((int) $pesanan->ongkir) : 'Gratis'); ?></dd>
            </div>
          </dl>
        </section>

        <dl class="struk-baris struk-total">
          <div>
            <dt>Total</dt>
            <dd>
              <?php if($ada): ?>
                <?php echo e($rupiah($pesanan->total_bayar)); ?>

              <?php else: ?>
                <span class="struk-belum">Dihitung setelah ditimbang</span>
              <?php endif; ?>
            </dd>
          </div>
        </dl>

        <dl class="struk-baris">
          <div><dt>Pembayaran</dt><dd><?php echo e($ada ? $pesanan->label_bayar : 'Setelah total ada'); ?></dd></div>
          <?php if($pesanan->status_bayar === 'lunas'): ?>
            <?php if($pesanan->label_metode): ?>
              <div><dt>Metode</dt><dd><?php echo e($pesanan->label_metode); ?></dd></div>
            <?php endif; ?>
            <?php if($pesanan->dibayar_pada): ?>
              <div><dt>Dibayar</dt><dd><?php echo e($waktu($pesanan->dibayar_pada)); ?></dd></div>
            <?php endif; ?>
          <?php endif; ?>
        </dl>

        <footer class="struk-kaki">
          <p>Terima kasih sudah memakai Laundry Abah.</p>
          <p>Simpan struk ini sebagai bukti pesanan.</p>
          <p class="struk-cetak">Dicetak <?php echo e($waktu(now())); ?></p>
        </footer>
      </article>

      
      <div class="struk-alat">
        <button type="button" class="tombol tombol-utama" data-cetak hidden>Cetak struk</button>
        <a class="tombol-garis" href="<?php echo e($kembali); ?>">Kembali<span class="sr-only"> <?php echo e($kembaliTeks); ?></span></a>
      </div>
    </main>

    <script src="<?php echo e(asset('js/struk.js')); ?>?v=<?php echo e(filemtime(public_path('js/struk.js'))); ?>" defer></script>
  </body>
</html>
<?php /**PATH /home/mldham/Documents/TUGASBESAR_M3_ILHAM/resources/views/struk.blade.php ENDPATH**/ ?>