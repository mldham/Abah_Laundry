
<form method="post" action="<?php echo e(route('order.batal', $p)); ?>" class="form-batal"
      data-konfirmasi
      data-judul="Batalkan pesanan #<?php echo e($p->id_pesanan); ?>"
      data-rincian="<?php echo e($p->layanan); ?>, jemput <?php echo e($p->tanggal_pickup->locale('id')->translatedFormat('j F Y')); ?>">
  <?php echo csrf_field(); ?>
  <?php echo method_field('DELETE'); ?>
  <button type="submit" class="tombol-garis tombol-bahaya">Batalkan pesanan</button>
</form>
<?php /**PATH /home/mldham/Documents/TUGASBESAR_M3_ILHAM/resources/views/partials/batal-pesanan.blade.php ENDPATH**/ ?>