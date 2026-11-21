<?php $__env->startSection('title', 'Bayar pesanan #'.$pesanan->id_pesanan); ?>

<?php $__env->startPush('styles'); ?>
  <link rel="stylesheet" href="<?php echo e(asset('css/status.css')); ?>" />
  <link rel="stylesheet" href="<?php echo e(asset('css/bayar.css')); ?>" />
<?php $__env->stopPush(); ?>

<?php
  $rp = fn (int $angka) => 'Rp'.number_format($angka, 0, ',', '.');
?>

<?php $__env->startSection('content'); ?>
<main class="bayar">
  <a class="kembali" href="<?php echo e(route('order.index')); ?>">Kembali ke Cek pesanan</a>
  <h1>Bayar pesanan #<?php echo e($pesanan->id_pesanan); ?></h1>

  <article class="pesanan">
    <?php if($pesanan->status_bayar === 'lunas'): ?>
      <p class="kabar-kartu" role="status">
        <strong>Sudah lunas.</strong>
        <?php if($pesanan->dibayar_pada): ?>
          Tercatat <?php echo e($pesanan->dibayar_pada->locale('id')->translatedFormat('j F Y, H:i')); ?>.
        <?php endif; ?>
      </p>
    <?php elseif($pesanan->status_bayar === 'menunggu_verifikasi'): ?>
      <p class="kabar-kartu" role="status">
        <strong>Pembayaran sedang dicek admin.</strong> Statusnya jadi Lunas setelah cocok dengan mutasi QRIS.
      </p>
    <?php endif; ?>

    <h2 class="judul-kartu"><?php echo e($pesanan->layanan); ?></h2>

    <dl class="rincian">
      <dt><?php echo e($pesanan->label_jumlah); ?></dt>
      <dd><?php echo e($pesanan->total_berat); ?> <?php echo e($pesanan->satuan); ?></dd>

      <dt>Cucian</dt>
      <dd><?php echo e($rp($pesanan->total_harga)); ?></dd>

      <dt>Ongkir</dt>
      <dd><?php echo e($pesanan->ongkir > 0 ? $rp($pesanan->ongkir) : 'Gratis'); ?></dd>

      <dt>Total bayar</dt>
      <dd class="total"><?php echo e($rp($pesanan->total_bayar)); ?></dd>
    </dl>

    <?php if($galat): ?>
      <div class="kabar kabar-galat bayar-galat" role="alert">
        <p class="kabar-judul"><?php echo e($galat); ?></p>
      </div>
    <?php elseif($qr): ?>
      <section class="qris" aria-labelledby="judul-qris">
        <h2 id="judul-qris">Scan dengan aplikasi pembayaran</h2>

        <div class="qris-kode" role="img" aria-label="Kode QRIS senilai <?php echo e($rp($pesanan->total_bayar)); ?>"><?php echo $qr; ?></div>
        <p class="qris-nominal"><?php echo e($rp($pesanan->total_bayar)); ?></p>
        <?php if($penerima): ?>
          <p class="qris-penerima">Penerima: <?php echo e($penerima); ?></p>
        <?php endif; ?>

        <ol class="langkah">
          <li>Buka e-wallet atau mobile banking yang mendukung QRIS.</li>
          <li>Scan kode di atas. Nominal terisi otomatis, jangan diubah.</li>
          <li>Setelah berhasil, tekan "Saya sudah bayar" di bawah.</li>
        </ol>

        <form method="post" action="<?php echo e(route('order.bayar.lapor', $pesanan)); ?>" data-kunci>
          <?php echo csrf_field(); ?>
          <button type="submit" class="tombol tombol-utama tombol-blok" data-teks-proses="Mengirim...">Saya sudah bayar</button>
        </form>
      </section>
    <?php endif; ?>
  </article>
</main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/mldham/Documents/TUGASBESAR_M3_ILHAM/resources/views/bayar.blade.php ENDPATH**/ ?>