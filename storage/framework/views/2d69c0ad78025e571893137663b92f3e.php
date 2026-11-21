<?php $__env->startSection('title', 'Masuk'); ?>

<?php $__env->startPush('styles'); ?>
  <link rel="stylesheet" href="<?php echo e(asset('css/auth.css')); ?>" />
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<main class="auth">
  <h1>Masuk</h1>

  <form class="form-auth" method="post" action="<?php echo e(route('masuk.proses')); ?>" data-kunci>
    <?php echo csrf_field(); ?>

    <div class="grup">
      <label for="telepon">Nomor WhatsApp</label>
      <input type="tel" id="telepon" name="telepon" value="<?php echo e(old('telepon')); ?>" inputmode="tel"
             autocomplete="username" placeholder="08xxxxxxxxxx" required
             <?php $__errorArgs = ['telepon'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> aria-invalid="true" aria-describedby="telepon-galat" <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>>
      <?php $__errorArgs = ['telepon'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <p class="galat" id="telepon-galat" role="alert"><?php echo e($message); ?></p>
      <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div class="grup">
      <label for="password">Kata sandi</label>
      <input type="password" id="password" name="password" autocomplete="current-password" required
             <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> aria-invalid="true" aria-describedby="password-galat" <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>>
      <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <p class="galat" id="password-galat" role="alert"><?php echo e($message); ?></p>
      <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <label class="ingat" for="ingat">
      <input type="checkbox" id="ingat" name="ingat" value="1" <?php if(old('ingat', ! $errors->any())): echo 'checked'; endif; ?>>
      Tetap masuk di perangkat ini
    </label>

    <button type="submit" class="tombol tombol-auth" data-teks-proses="Masuk...">Masuk</button>
  </form>

  <p class="alihan">Belum punya akun? <a href="<?php echo e(route('daftar')); ?>">Daftar dulu</a>.</p>
  <p class="alihan">Pengelola laundry? <a href="<?php echo e(route('admin.masuk')); ?>">Masuk sebagai admin</a>.</p>
</main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/mldham/Documents/TUGASBESAR_M3_ILHAM/resources/views/auth/masuk.blade.php ENDPATH**/ ?>