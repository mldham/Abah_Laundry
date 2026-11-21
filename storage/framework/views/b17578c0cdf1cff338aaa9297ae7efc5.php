
<section class="komplain-kartu" aria-label="Komplain: <?php echo e($k->label_jenis); ?>">
  <div class="komplain-kepala">
    <div>
      <p class="komplain-jenis"><?php echo e($k->label_jenis); ?></p>
      <p class="komplain-waktu">Dikirim <?php echo e($k->created_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j F Y, H.i')); ?></p>
    </div>
    <span class="label-komplain" data-komplain="<?php echo e($k->status); ?>"><?php echo e($k->label_status); ?></span>
  </div>

  <p class="komplain-teks"><?php echo e($k->deskripsi); ?></p>

  <?php if($k->tanggapan): ?>
    <p class="komplain-tanggapan"><span>Tanggapan admin</span><?php echo e($k->tanggapan); ?></p>
  <?php elseif($k->terbuka()): ?>
    <p class="komplain-tunggu">Belum ada tanggapan. Jawaban admin tampil di sini.</p>
  <?php endif; ?>
</section>
<?php /**PATH /home/mldham/Documents/TUGASBESAR_M3_ILHAM/resources/views/partials/komplain-kartu.blade.php ENDPATH**/ ?>