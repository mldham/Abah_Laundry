
<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $__env->yieldContent('judul'); ?> - Laundry Abah</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@400..700&display=swap" />
    <link rel="stylesheet" href="<?php echo e(asset('css/base.css')); ?>" />
    <link rel="stylesheet" href="<?php echo e(asset('css/motion.css')); ?>" />
  </head>
  <body>
    <header class="navbar">
      <a class="merek" href="<?php echo e(route('order.create')); ?>">
        <img src="<?php echo e(asset('img/Logo.png')); ?>" alt="" width="48" height="48" />
        <span class="teks"><span>Laundry</span> Abah</span>
      </a>
    </header>

    <div class="halaman">
      <main class="halaman-galat">
        <h1><?php echo $__env->yieldContent('judul'); ?></h1>
        <p><?php echo $__env->yieldContent('penjelasan'); ?></p>
        <?php echo $__env->yieldContent('tindakan'); ?>
      </main>
    </div>
  </body>
</html>
<?php /**PATH /home/mldham/Documents/TUGASBESAR_M3_ILHAM/resources/views/errors/layout.blade.php ENDPATH**/ ?>