<?php $__env->startSection('title', 'Cek pesanan'); ?>

<?php $__env->startPush('styles'); ?>
  <link rel="stylesheet" href="<?php echo e(asset('css/status.css')); ?>?v=<?php echo e(filemtime(public_path('css/status.css'))); ?>" />
  <link rel="stylesheet" href="<?php echo e(asset('css/struk.css')); ?>?v=<?php echo e(filemtime(public_path('css/struk.css'))); ?>" />
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
  <script src="<?php echo e(asset('js/struk.js')); ?>?v=<?php echo e(filemtime(public_path('js/struk.js'))); ?>" defer></script>
<?php $__env->stopPush(); ?>

<?php
  $label = \App\Models\Pesanan::LABEL_STATUS;
?>

<?php $__env->startSection('content'); ?>
<main class="status">
  <h1>Cek pesanan</h1>

  <?php if(session('akun_baru')): ?>
    <div class="kabar" role="status">
      <p class="kabar-judul">Akun dibuat.</p>
      <p class="kabar-isi">
        <?php echo e($pesanan->isEmpty() ? 'Pesanan berikutnya tampil di halaman ini.' : 'Pesanan dengan nomor ini tampil di bawah.'); ?>

      </p>
    </div>
  <?php endif; ?>

  <?php if(session('batal')): ?>
    <?php $batal = session('batal'); ?>
    <div class="kabar <?php echo e($batal['berhasil'] ? '' : 'kabar-galat'); ?>" role="<?php echo e($batal['berhasil'] ? 'status' : 'alert'); ?>">
      <p class="kabar-judul"><?php echo e($batal['judul']); ?></p>
      <p class="kabar-isi"><?php echo e($batal['isi']); ?></p>
    </div>
  <?php endif; ?>

  <?php if(session('bayar')): ?>
    <?php $bayar = session('bayar'); ?>
    <div class="kabar <?php echo e($bayar['berhasil'] ? '' : 'kabar-galat'); ?>" role="<?php echo e($bayar['berhasil'] ? 'status' : 'alert'); ?>">
      <p class="kabar-judul"><?php echo e($bayar['judul']); ?></p>
      <p class="kabar-isi"><?php echo e($bayar['isi']); ?></p>
    </div>
  <?php endif; ?>

  <?php if(session('terima')): ?>
    <?php $terima = session('terima'); ?>
    <div class="kabar <?php echo e($terima['berhasil'] ? '' : 'kabar-galat'); ?>" role="<?php echo e($terima['berhasil'] ? 'status' : 'alert'); ?>">
      <p class="kabar-judul"><?php echo e($terima['judul']); ?></p>
      <p class="kabar-isi"><?php echo e($terima['isi']); ?></p>
    </div>
  <?php endif; ?>

  <?php if(session('komplain')): ?>
    <?php $komplain = session('komplain'); ?>
    <div class="kabar <?php echo e($komplain['berhasil'] ? '' : 'kabar-galat'); ?>" role="<?php echo e($komplain['berhasil'] ? 'status' : 'alert'); ?>">
      <p class="kabar-judul"><?php echo e($komplain['judul']); ?></p>
      <p class="kabar-isi"><?php echo e($komplain['isi']); ?></p>
    </div>
  <?php endif; ?>

  <?php if($pesanan->isEmpty()): ?>
    <p class="kosong">
      Belum pernah order.
      <a href="<?php echo e(route('order.create')); ?>">Buat pesanan baru</a>.
    </p>
  <?php else: ?>
    <?php $p = $pesanan->first(); ?>

    <h2>Pesanan terbaru</h2>
    <article class="pesanan">
      <?php if((session('pesanan_baru.id') ?? null) == $p->id_pesanan): ?>
        <p class="kabar-kartu" role="status"><strong>Pesanan sudah masuk.</strong></p>
      <?php endif; ?>
      <div class="kepala-pesanan">
        <div>
          <h3><?php echo e($p->layanan); ?></h3>
          <p class="nomor">Pesanan #<?php echo e($p->id_pesanan); ?></p>
        </div>
        
        <?php if($p->status === 'diterima'): ?>
          <a class="tautan-struk" href="<?php echo e(route('order.struk', $p)); ?>" data-struk data-judul="Struk pesanan #<?php echo e($p->id_pesanan); ?>">Lihat struk</a>
        <?php endif; ?>
      </div>

      <ol class="tahap" aria-label="Tahap pesanan">
        <?php $__currentLoopData = \App\Models\Pesanan::STATUS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <li class="<?php echo e($i < $p->tahap ? 'lewat' : ''); ?> <?php echo e($i === $p->tahap ? 'sekarang' : ''); ?>" style="--i: <?php echo e($i); ?>"
              <?php if($i === $p->tahap): ?> aria-current="step" <?php endif; ?>>
            <?php echo e($label[$s]); ?><?php if($i === $p->tahap): ?><span class="sr"> (tahap sekarang)</span><?php endif; ?>
          </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </ol>
      <p class="penjelasan-status"><?php echo e(\App\Models\Pesanan::PENJELASAN_STATUS[$p->status] ?? ''); ?></p>

      <dl class="rincian">
        <dt>Jadwal jemput</dt>
        <dd><?php echo e($p->tanggal_pickup->locale('id')->translatedFormat('l, j F Y')); ?></dd>

        <dt><?php echo e($p->label_jumlah); ?></dt>
        <dd>
          <?php if($p->total_berat > 0): ?>
            <?php echo e($p->total_berat); ?> <?php echo e($p->satuan); ?>

          <?php else: ?>
            <span class="belum"><?php echo e($p->satuan === 'kg' ? 'Ditimbang' : 'Dihitung'); ?> saat dijemput</span>
          <?php endif; ?>
        </dd>

        <dt>Ongkir</dt>
        <dd><?php echo e($p->ongkir > 0 ? 'Rp'.number_format($p->ongkir, 0, ',', '.') : 'Gratis'); ?></dd>

        <dt>Total harga</dt>
        <dd class="total">
          <?php if($p->total_harga > 0): ?>
            Rp<?php echo e(number_format($p->total_harga, 0, ',', '.')); ?>

          <?php else: ?>
            <span class="belum">Dihitung setelah cucian ditimbang</span>
          <?php endif; ?>
        </dd>

        <dt>Pembayaran</dt>
        <dd>
          <?php if($p->total_harga > 0): ?>
            <?php echo e($p->label_bayar); ?>

          <?php else: ?>
            <span class="belum">Setelah total harga ada</span>
          <?php endif; ?>
        </dd>

        <?php if($p->diantar_pada): ?>
          <dt>Diantar</dt>
          <dd><?php echo e($p->diantar_pada->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j F Y, H.i')); ?></dd>
        <?php endif; ?>

        <?php if($p->diterima_pada): ?>
          <dt>Diterima</dt>
          <dd><?php echo e($p->diterima_pada->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j F Y, H.i')); ?></dd>
        <?php endif; ?>
      </dl>

      
      <?php $__currentLoopData = $p->komplain->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php echo $__env->make('partials.komplain-kartu', ['k' => $k], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

      
      <?php $perluBayar = $p->bisaDibayar() && $p->status_bayar === 'belum_bayar'; ?>
      <?php if($p->bisaDiterima() || $p->bisaDikomplain() || $perluBayar): ?>
        <div class="aksi-kartu">
          <?php if($p->bisaDiterima()): ?>
            <?php echo $__env->make('partials.terima-pesanan', ['p' => $p, 'utama' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
          <?php endif; ?>

          <?php if($p->bisaDikomplain()): ?>
            <a class="tombol-garis tombol-masalah" href="<?php echo e(route('order.komplain', $p)); ?>">Ada masalah</a>
          <?php endif; ?>

          <?php if($perluBayar): ?>
            <a class="<?php echo e($p->bisaDiterima() ? 'tombol-garis' : 'tombol tombol-utama'); ?> tombol-bayar" href="<?php echo e(route('order.bayar', $p)); ?>">Bayar dengan QRIS</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <?php if($p->bisaDibatalkan()): ?>
        <?php echo $__env->make('partials.batal-pesanan', ['p' => $p], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
      <?php endif; ?>
    </article>

    <?php if($pesanan->count() > 1): ?>
      <h2>Pesanan lainnya</h2>
      <ul class="riwayat">
        <?php $__currentLoopData = $pesanan->slice(1); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lama): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <li>
            <span>
              <?php echo e($lama->layanan); ?>

              <small>Pesanan #<?php echo e($lama->id_pesanan); ?>, jemput <?php echo e($lama->tanggal_pickup->locale('id')->translatedFormat('j M Y')); ?></small>
              <small class="tautan-baris">
                <?php if($lama->status === 'diterima'): ?>
                  <a class="tautan-struk" href="<?php echo e(route('order.struk', $lama)); ?>" data-struk data-judul="Struk pesanan #<?php echo e($lama->id_pesanan); ?>">Lihat struk</a>
                <?php endif; ?>
                <?php if($lama->bisaDikomplain()): ?>
                  <a class="tautan-struk tautan-masalah" href="<?php echo e(route('order.komplain', $lama)); ?>">Ada masalah</a>
                <?php endif; ?>
              </small>
              <?php if($lama->total_harga > 0): ?>
                <small>Pembayaran: <?php echo e($lama->label_bayar); ?></small>
              <?php endif; ?>
            </span>
            <strong><?php echo e($label[$lama->status] ?? $lama->status); ?></strong>
            <?php if($lama->bisaDibayar() && $lama->status_bayar === 'belum_bayar'): ?>
              <a class="tombol-garis" href="<?php echo e(route('order.bayar', $lama)); ?>">Bayar dengan QRIS</a>
            <?php endif; ?>
            <?php if($lama->bisaDiterima()): ?>
              <?php echo $__env->make('partials.terima-pesanan', ['p' => $lama, 'utama' => false], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endif; ?>
            <?php if($lama->bisaDibatalkan()): ?>
              <?php echo $__env->make('partials.batal-pesanan', ['p' => $lama], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endif; ?>
            <?php if($lama->komplain->first()): ?>
              <?php echo $__env->make('partials.komplain-kartu', ['k' => $lama->komplain->first()], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endif; ?>
          </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </ul>
    <?php endif; ?>
  <?php endif; ?>

  <dialog class="dialog" id="dialog-konfirmasi" aria-labelledby="dialog-konfirmasi-judul">
    <h2 id="dialog-konfirmasi-judul" data-dialog-judul>Batalkan pesanan?</h2>
    <p>Pesanan yang dibatalkan hilang dari daftar dan tidak bisa dikembalikan. Kalau mau laundry lagi, buat pesanan baru.</p>
    <p class="dialog-rincian" data-dialog-rincian></p>
    <div class="dialog-aksi">
      <button type="button" class="tombol-garis" data-dialog-batal autofocus>Kembali</button>
      <button type="button" class="tombol-bahaya-isi" data-dialog-ya data-teks-proses="Membatalkan...">Ya, batalkan pesanan</button>
    </div>
  </dialog>

  
  <dialog class="dialog dialog-struk" id="dialog-struk" aria-labelledby="dialog-struk-judul">
    <h2 class="sr-only" id="dialog-struk-judul" data-struk-judul>Struk pesanan</h2>
    <div class="struk-popup-isi" data-struk-isi aria-live="polite"></div>
    <div class="struk-popup-alat">
      <button type="button" class="tombol tombol-utama" data-struk-cetak hidden>Cetak struk</button>
      <button type="button" class="tombol-garis" data-struk-tutup autofocus>Tutup</button>
    </div>
  </dialog>
</main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/mldham/Documents/TUGASBESAR_M3_ILHAM/resources/views/data-order.blade.php ENDPATH**/ ?>