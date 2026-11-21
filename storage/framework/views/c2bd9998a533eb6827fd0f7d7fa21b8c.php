<?php $__env->startSection('title', 'Bantuan'); ?>

<?php $__env->startPush('styles'); ?>
  <link rel="stylesheet" href="<?php echo e(asset('css/bantuan.css')); ?>" />
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<main class="bantuan">
  <h1>Bantuan</h1>
  <p class="pengantar">Cara memesan, arti status, dan biaya. Semua angka di sini sama dengan yang dipakai saat pesananmu dihitung.</p>

  <section aria-labelledby="judul-cara">
    <h2 id="judul-cara">Cara memesan</h2>
    <ol class="langkah">
      <li>Pilih layanan di halaman <a href="<?php echo e(route('order.create')); ?>">Order</a>.</li>
      <li>Isi tanggal dan alamat jemput. Kalau belum punya akun, isi juga nama dan nomor WhatsApp.</li>
      <li>Kirim pesanan. Kami menjemput cuciannya pada tanggal itu.</li>
      <li>Lihat perkembangannya di <a href="<?php echo e(route('order.index')); ?>">Cek pesanan</a>. Halaman itu butuh akun dengan nomor WhatsApp yang sama dengan pesananmu.</li>
    </ol>
  </section>

  <section aria-labelledby="judul-status">
    <h2 id="judul-status">Arti status pesanan</h2>
    <dl class="daftar-status">
      <?php $__currentLoopData = $status; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <dt><?php echo e($label[$s]); ?></dt>
        <dd><?php echo e($penjelasan[$s]); ?></dd>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </dl>
  </section>

  <section aria-labelledby="judul-biaya">
    <h2 id="judul-biaya">Harga dan ongkir</h2>
    <ul class="daftar-harga">
      <?php $__currentLoopData = $layanan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <li>
          <span><?php echo e($item['nama']); ?></span>
          <strong>Rp<?php echo e(number_format($item['harga'], 0, ',', '.')); ?> / <?php echo e($item['satuan']); ?></strong>
        </li>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>
    <p>Total harga dihitung setelah cucian ditimbang atau dihitung jumlahnya. Ongkir Rp<?php echo e(number_format($ongkir, 0, ',', '.')); ?>, gratis untuk alamat di <?php echo e($areaGratis); ?>.</p>
  </section>

  <section aria-labelledby="judul-batal">
    <h2 id="judul-batal">Membatalkan pesanan</h2>
    <p>Pesanan bisa dibatalkan dari <a href="<?php echo e(route('order.index')); ?>">Cek pesanan</a> selama statusnya masih "<?php echo e($label['pending']); ?>". Setelah dijemput, pesanan tidak bisa dibatalkan sendiri.</p>
  </section>

  <?php if($linkWa): ?>
    <section aria-labelledby="judul-hubungi">
      <h2 id="judul-hubungi">Masih bingung?</h2>
      <p>Tanya langsung ke admin lewat WhatsApp.</p>
      <a class="tombol tombol-bantuan" href="<?php echo e($linkWa); ?>" rel="noopener">Chat admin di WhatsApp</a>
    </section>
  <?php endif; ?>
</main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/mldham/Documents/TUGASBESAR_M3_ILHAM/resources/views/bantuan.blade.php ENDPATH**/ ?>