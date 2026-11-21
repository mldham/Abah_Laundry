<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $__env->yieldContent('title', 'Laundry Abah'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@400..700&display=swap" />
    <link rel="stylesheet" href="<?php echo e(asset('css/base.css')); ?>?v=<?php echo e(filemtime(public_path('css/base.css'))); ?>" />
    <?php echo $__env->yieldPushContent('styles'); ?>
    
    <link rel="stylesheet" href="<?php echo e(asset('css/motion.css')); ?>?v=<?php echo e(filemtime(public_path('css/motion.css'))); ?>" />
  </head>

  <body>
    <header class="navbar">
      <a class="merek" href="<?php echo e(url('/')); ?>">
        <img src="<?php echo e(asset('img/Logo.png')); ?>" alt="" width="48" height="48" />
        <span class="teks"><span>Laundry</span> Abah</span>
      </a>
      
      <button type="button" class="tombol-menu" id="tombol-menu" aria-controls="menu-utama" aria-expanded="false" hidden>Menu</button>
      <nav id="menu-utama" aria-label="Menu utama">
        <a href="<?php echo e(route('order.create')); ?>" class="<?php echo e(request()->routeIs('order.create') ? 'aktif' : ''); ?>" <?php if(request()->routeIs('order.create')): ?> aria-current="page" <?php endif; ?>>Order</a>
        <a href="<?php echo e(route('order.index')); ?>" class="<?php echo e(request()->routeIs('order.index') ? 'aktif' : ''); ?>" <?php if(request()->routeIs('order.index')): ?> aria-current="page" <?php endif; ?>>Cek pesanan</a>
        <a href="<?php echo e(route('bantuan')); ?>" class="<?php echo e(request()->routeIs('bantuan') ? 'aktif' : ''); ?>" <?php if(request()->routeIs('bantuan')): ?> aria-current="page" <?php endif; ?>>Bantuan</a>
        <?php if(auth()->guard('admin')->check()): ?>
          <?php
            $admin = auth('admin')->user();
            $komplainBaru = \App\Models\Komplain::where('status', 'baru')->count();
          ?>
          <div class="menu-admin">
            <a href="<?php echo e(route('admin.order.index')); ?>" class="<?php echo e(request()->routeIs('admin.order.*') ? 'aktif' : ''); ?>" <?php if(request()->routeIs('admin.order.*')): ?> aria-current="page" <?php endif; ?>>Kelola pesanan</a>
            <a href="<?php echo e(route('admin.komplain.index')); ?>" class="<?php echo e(request()->routeIs('admin.komplain.*') ? 'aktif' : ''); ?>" <?php if(request()->routeIs('admin.komplain.*')): ?> aria-current="page" <?php endif; ?>>
              Komplain
              <?php if($komplainBaru > 0): ?>
                <span class="lencana"><?php echo e($komplainBaru); ?><span class="sr-only"> komplain baru</span></span>
              <?php endif; ?>
            </a>
            <?php if($admin->adalahAdmin()): ?>
              <a href="<?php echo e(route('admin.audit')); ?>" class="<?php echo e(request()->routeIs('admin.audit') ? 'aktif' : ''); ?>" <?php if(request()->routeIs('admin.audit')): ?> aria-current="page" <?php endif; ?>>Audit</a>
            <?php endif; ?>
          </div>
          <div class="menu-akun">
            <span class="akun-nama" title="<?php echo e($admin->name); ?> (<?php echo e($admin->label_peran); ?>)"><?php echo e($admin->label_peran); ?>: <?php echo e(\Illuminate\Support\Str::words($admin->name, 1, '')); ?></span>
            <form method="post" action="<?php echo e(route('admin.keluar')); ?>">
              <?php echo csrf_field(); ?>
              <button type="submit" class="tautan-keluar">Keluar</button>
            </form>
          </div>
        <?php elseif(auth()->guard('pelanggan')->check()): ?>
          <div class="menu-akun">
            <span class="akun-nama">Halo, <?php echo e(\Illuminate\Support\Str::words(auth('pelanggan')->user()->nama, 1, '')); ?></span>
            <form method="post" action="<?php echo e(route('keluar')); ?>">
              <?php echo csrf_field(); ?>
              <button type="submit" class="tautan-keluar">Keluar</button>
            </form>
          </div>
        <?php else: ?>
          <div class="menu-akun">
            <a href="<?php echo e(route('masuk')); ?>" class="<?php echo e(request()->routeIs('masuk') ? 'aktif' : ''); ?>" <?php if(request()->routeIs('masuk')): ?> aria-current="page" <?php endif; ?>>Masuk</a>
            <a href="<?php echo e(route('daftar')); ?>" class="tombol-nav <?php echo e(request()->routeIs('daftar') ? 'aktif' : ''); ?>" <?php if(request()->routeIs('daftar')): ?> aria-current="page" <?php endif; ?>>Daftar</a>
          </div>
        <?php endif; ?>
      </nav>
    </header>

    
    <div class="halaman">
      <?php echo $__env->yieldContent('content'); ?>
    </div>
    <script src="<?php echo e(asset('js/app.js')); ?>?v=<?php echo e(filemtime(public_path('js/app.js'))); ?>" defer></script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
  </body>
</html>
<?php /**PATH /home/mldham/Documents/TUGASBESAR_M3_ILHAM/resources/views/layouts/app.blade.php ENDPATH**/ ?>