
<div class="notifikasi" data-notifikasi role="status">
  <div class="notifikasi-teks">
    <p class="notifikasi-judul"><?php echo e($aksi['judul']); ?></p>
    <?php if(isset($aksi['isi'])): ?>
      <p class="notifikasi-isi"><?php echo e($aksi['isi']); ?></p>
    <?php endif; ?>
  </div>
  <button type="button" class="notifikasi-tutup" data-notifikasi-tutup>Tutup</button>
  <span class="notifikasi-garis" aria-hidden="true"></span>
</div>
<?php /**PATH /home/mldham/Documents/TUGASBESAR_M3_ILHAM/resources/views/admin/notifikasi.blade.php ENDPATH**/ ?>