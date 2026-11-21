<?php $__env->startSection('judul', 'Halaman tidak ditemukan.'); ?>
<?php $__env->startSection('penjelasan', 'Alamat yang dibuka tidak ada atau sudah dipindah. Cek lagi tulisannya, atau mulai dari halaman order.'); ?>
<?php $__env->startSection('tindakan'); ?>
  <a class="tombol" href="<?php echo e(route('order.create')); ?>">Ke halaman order</a>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('errors.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/mldham/Documents/TUGASBESAR_M3_ILHAM/resources/views/errors/404.blade.php ENDPATH**/ ?>