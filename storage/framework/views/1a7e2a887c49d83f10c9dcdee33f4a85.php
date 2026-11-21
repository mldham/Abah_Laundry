<?php $__env->startSection('title', 'Komplain pesanan #'.$pesanan->id_pesanan); ?>

<?php $__env->startPush('styles'); ?>
  <link rel="stylesheet" href="<?php echo e(asset('css/komplain.css')); ?>" />
<?php $__env->stopPush(); ?>

<?php
  $jenis = \App\Models\Komplain::JENIS;
  $keterangan = \App\Models\Komplain::KETERANGAN_JENIS;
?>

<?php $__env->startSection('content'); ?>
<main class="komplain-halaman">
  <a class="kembali" href="<?php echo e(route('order.index')); ?>">Kembali ke Cek pesanan</a>
  <h1>Ada masalah dengan pesanan #<?php echo e($pesanan->id_pesanan); ?>?</h1>
  <p class="keterangan"><?php echo e($pesanan->layanan); ?>. Ceritakan apa yang terjadi, admin akan menanggapi di halaman Cek pesanan.</p>

  <form method="post" action="<?php echo e(route('order.komplain.kirim', $pesanan)); ?>" novalidate data-kunci>
    <?php echo csrf_field(); ?>

    <?php if($errors->any()): ?>
      <div class="kabar kabar-galat" role="alert" tabindex="-1" data-fokus-galat>
        <p class="kabar-judul">Komplain belum terkirim.</p>
        <ul>
          <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pesan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li><?php echo e($pesan); ?></li>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
      </div>
    <?php endif; ?>

    <fieldset class="pilihan-masalah" <?php if($errors->has('jenis')): ?> aria-describedby="jenis-galat" <?php endif; ?>>
      <legend>Masalahnya apa?</legend>

      <?php $__currentLoopData = $jenis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kunci => $nama): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="masalah">
          <input type="radio" name="jenis" id="jenis-<?php echo e($kunci); ?>" value="<?php echo e($kunci); ?>" required
                 <?php if(old('jenis') === $kunci): echo 'checked'; endif; ?>
                 <?php if($errors->has('jenis')): ?> aria-invalid="true" <?php endif; ?> />
          <label for="jenis-<?php echo e($kunci); ?>">
            <span class="masalah-nama"><?php echo e($nama); ?></span>
            <span class="masalah-keterangan"><?php echo e($keterangan[$kunci]); ?></span>
          </label>
        </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

      <?php $__errorArgs = ['jenis'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <p class="galat" id="jenis-galat"><?php echo e($message); ?></p>
      <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </fieldset>

    <div class="grup-form">
      <label for="deskripsi">Ceritakan lebih lengkap</label>
      <textarea id="deskripsi" name="deskripsi" rows="5" required maxlength="1000"
                placeholder="Contoh: Dua kaos putih masih ada noda di bagian kerah."
                aria-describedby="deskripsi-petunjuk <?php $__errorArgs = ['deskripsi'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> deskripsi-galat <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                <?php $__errorArgs = ['deskripsi'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> aria-invalid="true" <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>><?php echo e(old('deskripsi')); ?></textarea>
      <?php $__errorArgs = ['deskripsi'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <p class="galat" id="deskripsi-galat"><?php echo e($message); ?></p>
      <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
      <p class="petunjuk" id="deskripsi-petunjuk">Sebut barangnya dan jumlahnya kalau ada yang hilang atau rusak.</p>
    </div>

    <button type="submit" class="tombol tombol-utama tombol-blok" data-teks-proses="Mengirim...">Kirim komplain</button>
  </form>
</main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/mldham/Documents/TUGASBESAR_M3_ILHAM/resources/views/komplain.blade.php ENDPATH**/ ?>