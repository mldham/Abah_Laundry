<?php $__env->startSection('title', 'Audit aktivitas'); ?>

<?php $__env->startPush('styles'); ?>
  <link rel="stylesheet" href="<?php echo e(asset('css/admin.css')); ?>" />
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<main class="admin-page">
  <header class="admin-heading">
    <h1>Audit aktivitas</h1>
  </header>

  <section class="admin-panel" aria-labelledby="judul-audit">
    <div class="panel-heading">
      <h2 id="judul-audit">Catatan terbaru</h2>
      <p class="keterangan-audit">Catatan hanya bertambah dan tidak bisa diubah dari aplikasi. Waktu dalam WIB.</p>
    </div>

    <div class="tabel-scroll">
      <table class="tabel-admin tabel-audit">
        <thead>
          <tr>
            <th scope="col">Waktu</th>
            <th scope="col">User</th>
            <th scope="col">Aksi</th>
            <th scope="col">Rincian</th>
            <th scope="col">Alamat IP</th>
          </tr>
        </thead>
        <tbody>
          <?php $__empty_1 = true; $__currentLoopData = $log; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entri): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
              <td>
                <time datetime="<?php echo e($entri->created_at->toIso8601String()); ?>">
                  <?php echo e($entri->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i')); ?>

                </time>
              </td>
              <td>
                <?php if($entri->pelaku): ?>
                  <strong><?php echo e($entri->pelaku->name); ?></strong>
                  <span class="rincian"><?php echo e($entri->pelaku->label_peran); ?></span>
                <?php elseif($entri->email_dicoba): ?>
                  <strong><?php echo e($entri->email_dicoba); ?></strong>
                  <span class="rincian">Email yang diketik</span>
                <?php else: ?>
                  <span class="rincian">Pelanggan atau tamu</span>
                <?php endif; ?>
              </td>
              <td>
                <?php echo e($entri->label); ?>

                <?php if($entri->subjek): ?>
                  <span class="rincian"><?php echo e($entri->subjek); ?> #<?php echo e($entri->id_subjek); ?></span>
                <?php endif; ?>
              </td>
              <td class="rincian"><?php echo e($entri->ringkasan); ?></td>
              <td class="rincian"><?php echo e($entri->ip_address); ?></td>
            </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
              <td colspan="5">
                <div class="kosong-admin">
                  <strong>Belum ada catatan</strong>
                  <p>Catatan muncul setelah ada yang masuk, mengubah, atau menghapus pesanan.</p>
                </div>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php if($log->previousPageUrl() || $log->nextPageUrl()): ?>
      <nav class="halaman-audit" aria-label="Halaman catatan">
        <?php if($log->previousPageUrl()): ?>
          <a class="tombol-garis" href="<?php echo e($log->previousPageUrl()); ?>" rel="prev">Lebih baru</a>
        <?php endif; ?>
        <?php if($log->nextPageUrl()): ?>
          <a class="tombol-garis" href="<?php echo e($log->nextPageUrl()); ?>" rel="next">Lebih lama</a>
        <?php endif; ?>
      </nav>
    <?php endif; ?>
  </section>
</main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/mldham/Documents/TUGASBESAR_M3_ILHAM/resources/views/admin/audit.blade.php ENDPATH**/ ?>