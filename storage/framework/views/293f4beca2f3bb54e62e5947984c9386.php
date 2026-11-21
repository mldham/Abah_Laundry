
<form method="post" action="<?php echo e(route('order.terima', $p)); ?>" class="form-terima" data-kunci>
  <?php echo csrf_field(); ?>
  <button type="submit" class="<?php echo e(($utama ?? false) ? 'tombol tombol-utama' : 'tombol-garis'); ?>" data-teks-proses="Menyimpan...">Cucian sudah diterima</button>
</form>
<?php /**PATH /home/mldham/Documents/TUGASBESAR_M3_ILHAM/resources/views/partials/terima-pesanan.blade.php ENDPATH**/ ?>